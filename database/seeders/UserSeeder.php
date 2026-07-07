<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure the roles exist (super_admin is normally created by Shield).
        Role::findOrCreate('super_admin', 'web');
        $teacherRole = Role::findOrCreate('teacher', 'web');
        Role::findOrCreate('supervisor', 'web');

        // The teacher panel only exposes the Course resource behind a policy,
        // so grant the teacher role every "course" permission.
        $prefixes = config('filament-shield.permission_prefixes.resource', [
            'view', 'view_any', 'create', 'update', 'restore', 'restore_any',
            'replicate', 'reorder', 'delete', 'delete_any', 'force_delete', 'force_delete_any',
        ]);

        $coursePermissions = collect($prefixes)
            ->map(fn (string $prefix): string => "{$prefix}_course")
            ->map(fn (string $name) => Permission::findOrCreate($name, 'web'));

        $teacherRole->syncPermissions($coursePermissions->all());

        // super_admin bypasses all checks via the Shield gate (define_via_gate),
        // so it needs no explicit permissions.

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

        $teacherRecord = Teacher::query()->whereNull('user_id')->orderBy('id')->first();
        $teacherRecord?->update(['user_id' => $teacher->id]);

        // Attendance supervisor account -> /supervisor panel.
        $supervisor = User::updateOrCreate(
            ['email' => 'supervisor@ibneljezery.test'],
            [
                'name' => 'مشرف الحضور',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        $supervisor->syncRoles(['supervisor']);

        // Assign a teacher + supervisor to any course still missing them, so the
        // teacher and supervisor panels have data to work with out of the box.
        Course::query()->whereNull('teacher_id')->update(['teacher_id' => $teacherRecord?->id]);
        Course::query()->whereNull('supervisor_id')->update(['supervisor_id' => $supervisor->id]);
    }
}
