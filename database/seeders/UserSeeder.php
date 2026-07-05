<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure both roles exist (super_admin is normally created by Shield).
        Role::findOrCreate('super_admin', 'web');
        Role::findOrCreate('teacher', 'web');

        // Admin account -> /admin panel
        $admin = User::updateOrCreate(
            ['email' => 'admin@ibneljezery.test'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles(['super_admin']);

        // Teacher account -> /teacher panel, linked to the first teacher record.
        $teacher = User::updateOrCreate(
            ['email' => 'teacher@ibneljezery.test'],
            [
                'name' => 'حساب المعلم',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        $teacher->syncRoles(['teacher']);

        Teacher::query()->whereNull('user_id')->orderBy('id')->first()
            ?->update(['user_id' => $teacher->id]);
    }
}
