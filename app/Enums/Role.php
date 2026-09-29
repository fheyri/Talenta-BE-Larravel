<?php

namespace App\Enums;

enum Role: string
{
    case Siswa = 'siswa';
    case Alumni = 'alumni';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
}
