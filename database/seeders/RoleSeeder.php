<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // super_admin is created by Shield; ensure the teacher role exists too.
        Role::findOrCreate('teacher', 'web');
    }
}
