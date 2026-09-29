<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteProfileRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\AlumniProfile;
use App\Models\SiswaProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $isComplete = !empty($data['sekolah']) && !empty($data['jurusan']);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => Role::from($data['role']),
                'is_profile_complete' => $isComplete,
            ]);

            $profile = [
                'user_id' => $user->id,
                'sekolah' => $data['sekolah'] ?? null,
                'jurusan' => $data['jurusan'] ?? null,
            ];

            if ($data['role'] === 'siswa') {
                SiswaProfile::create($profile);
            } else {
                AlumniProfile::create($profile);
            }

            return $user;
        });

        return response()->json([
            'token' => $user->createToken($data['device_name'] ?? 'mobile')->plainTextToken,
            'user' => $this->userPayload($user),
        ], 201);
    }

    /** Login aplikasi mobile: siswa & alumni. */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->attempt($request, ['siswa', 'alumni']);
    }

    /** Login dashboard web: admin & super_admin. */
    public function adminLogin(LoginRequest $request): JsonResponse
    {
        return $this->attempt($request, ['admin', 'super_admin']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    /** Untuk akun yang melengkapi data dirinya. */
    public function completeProfile(CompleteProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $profile = $user->role === Role::Siswa
            ? $user->siswaProfile()->firstOrCreate([])
            : $user->alumniProfile()->firstOrCreate([]);

        $profileData = array_filter([
            'sekolah' => $data['sekolah'] ?? null,
            'jurusan' => $data['jurusan'] ?? null,
            'nis' => $data['nis'] ?? null,
            'kelas' => $data['kelas'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'bio' => $data['bio'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'angkatan' => $data['angkatan'] ?? null,
            'status_karier' => $data['status_karier'] ?? null,
        ], fn($v) => !is_null($v));

        if (!empty($profileData)) {
            $profile->update($profileData);
        }

        $user->update([
            'name' => $data['name'] ?? $user->name,
            'is_profile_complete' => true,
        ]);

        return response()->json(['user' => $this->userPayload($user->fresh())]);
    }

    private function attempt(LoginRequest $request, array $allowedRoles): JsonResponse
    {
        $data = $request->validated();
        $loginInput = $data['email'];

        $user = User::where('email', $loginInput)
            ->orWhere('name', $loginInput)
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Email / Nama atau kata sandi salah.']]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Akun kamu dinonaktifkan. Hubungi admin.'], 403);
        }

        if (! in_array($user->role->value, $allowedRoles, true)) {
            return response()->json(['message' => 'Akun ini tidak bisa masuk lewat aplikasi ini.'], 403);
        }

        return response()->json([
            'token' => $user->createToken($data['device_name'] ?? 'mobile')->plainTextToken,
            'user' => $this->userPayload($user),
        ]);
    }

    private function userPayload(User $user): array
    {
        $profile = match ($user->role) {
            Role::Siswa => $user->siswaProfile,
            Role::Alumni => $user->alumniProfile,
            default => null,
        };

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'is_profile_complete' => $user->is_profile_complete,
            'token_balance' => $user->token_balance,
            'profile' => $profile,
        ];
    }
}
