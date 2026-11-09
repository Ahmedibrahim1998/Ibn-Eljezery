<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // super_admin is created by Shield; ensure the extra roles exist too.
        Role::findOrCreate('teacher', 'web');
        Role::findOrCreate('supervisor', 'web');
        // Approves / rejects / publishes app lessons (see LessonResource::REVIEWER_ROLES).
        Role::findOrCreate('sharia_reviewer', 'web');
        // Mobile app accounts (no panel access).
        Role::findOrCreate('student', 'web');
    }
}
