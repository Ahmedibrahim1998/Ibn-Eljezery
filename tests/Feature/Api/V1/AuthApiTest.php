<?php

namespace Tests\Feature\Api\V1;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeaders(['Accept-Language' => 'ar', 'Accept' => 'application/json']);

        foreach (['student', 'teacher', 'supervisor', 'super_admin'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    public function test_student_registers_with_phone_and_gets_a_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'أحمد',
            'phone' => '010 1234 5678',
            'password' => 'secret123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', 'student')
            ->assertJsonPath('user.phone', '201012345678');
        $this->assertNotEmpty($response->json('token'));
        $this->assertTrue(User::where('phone', '201012345678')->first()->hasRole('student'));
    }

    public function test_same_phone_in_another_format_cannot_register_twice(): void
    {
        $this->postJson('/api/v1/auth/register', ['name' => 'أ', 'phone' => '01012345678', 'password' => 'secret123'])->assertCreated();

        $this->postJson('/api/v1/auth/register', ['name' => 'ب', 'phone' => '+20 101 234 5678', 'password' => 'secret123'])
            ->assertStatus(422)
            ->assertJsonPath('errors.phone.0', trans('api.auth.phone_taken', [], 'ar'));
    }

    public function test_validation_messages_are_arabic(): void
    {
        $this->postJson('/api/v1/auth/register', ['name' => '', 'phone' => '0101', 'password' => '123'])
            ->assertStatus(422)
            ->assertJsonPath('errors.name.0', 'اكتب اسمك.')
            ->assertJsonPath('errors.password.0', trans('api.auth.password_min', [], 'ar'));
    }

    public function test_student_logs_in_with_phone_and_teacher_with_email(): void
    {
        $this->postJson('/api/v1/auth/register', ['name' => 'طالب', 'phone' => '01012345678', 'password' => 'secret123']);
        $this->postJson('/api/v1/auth/login', ['login' => '+201012345678', 'password' => 'secret123'])
            ->assertOk()->assertJsonPath('user.role', 'student');

        $user = User::create(['name' => 'الشيخ', 'email' => 't@example.com', 'password' => 'secret123']);
        $user->assignRole('teacher');
        Teacher::create(['user_id' => $user->id, 'name_ar' => 'الشيخ محمد']);

        $this->postJson('/api/v1/auth/login', ['login' => 't@example.com', 'password' => 'secret123'])
            ->assertOk()
            ->assertJsonPath('user.role', 'teacher')
            ->assertJsonPath('user.teacher.name', 'الشيخ محمد');
    }

    public function test_wrong_password_and_panel_only_accounts_are_rejected(): void
    {
        $this->postJson('/api/v1/auth/register', ['name' => 'طالب', 'phone' => '01012345678', 'password' => 'secret123']);
        $this->postJson('/api/v1/auth/login', ['login' => '01012345678', 'password' => 'wrong-pass'])
            ->assertStatus(422)->assertJsonValidationErrors('login');

        $admin = User::create(['name' => 'Admin', 'email' => 'a@example.com', 'password' => 'secret123']);
        $admin->assignRole('super_admin');
        $this->postJson('/api/v1/auth/login', ['login' => 'a@example.com', 'password' => 'secret123'])
            ->assertStatus(422)
            ->assertJsonPath('errors.login.0', trans('api.auth.panel_only', [], 'ar'));
    }

    public function test_me_logout_and_delete_account(): void
    {
        $token = $this->postJson('/api/v1/auth/register', ['name' => 'طالب', 'phone' => '01012345678', 'password' => 'secret123'])->json('token');
        $auth = ['Authorization' => "Bearer {$token}"];

        $this->getJson('/api/v1/auth/me', $auth)->assertOk()->assertJsonPath('data.name', 'طالب');

        $this->deleteJson('/api/v1/auth/me', [], $auth)->assertNoContent();
        $this->assertDatabaseMissing('users', ['phone' => '201012345678']);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_teacher_cannot_delete_account_from_the_app(): void
    {
        $user = User::create(['name' => 'الشيخ', 'email' => 't@example.com', 'password' => 'secret123']);
        $user->assignRole('teacher');
        $token = $user->createToken('test')->plainTextToken;

        $this->deleteJson('/api/v1/auth/me', [], ['Authorization' => "Bearer {$token}"])->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
