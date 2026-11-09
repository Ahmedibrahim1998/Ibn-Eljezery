<?php

namespace Tests\Feature\Api\V1;

use App\Enum\Booking\BookingStatusEnum;
use App\Enum\Course\CourseTypeEnum;
use App\Models\Booking;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademyApiTest extends TestCase
{
    use RefreshDatabase;

    private Teacher $teacher;

    private User $teacherUser;

    private Course $online;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeaders(['Accept-Language' => 'ar', 'Accept' => 'application/json']);
        Role::findOrCreate('student', 'web');
        Role::findOrCreate('teacher', 'web');

        $this->teacherUser = User::create(['name' => 'الشيخ', 'email' => 't@example.com', 'password' => 'secret123']);
        $this->teacherUser->assignRole('teacher');
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'name_ar' => 'الشيخ محمد',
            'whatsapp_phone' => '+20 100 000 0001',
        ]);

        $category = CourseCategory::create(['type' => CourseTypeEnum::ONLINE, 'title_ar' => 'تحفيظ أونلاين']);
        $this->online = Course::create(['course_category_id' => $category->id, 'teacher_id' => $this->teacher->id, 'title_ar' => 'مجموعة المساء']);
        // The session already has its Zoom links, so the observer never calls Zoom.
        $this->online->sessions()->create([
            'starts_at' => now()->addDay(),
            'zoom_join_url' => 'https://zoom.us/j/123',
            'zoom_start_url' => 'https://zoom.us/s/123',
        ]);
        $this->online->sessions()->create(['starts_at' => now()->subWeek(), 'zoom_join_url' => 'https://zoom.us/j/old']);

        Course::create(['type' => CourseTypeEnum::OFFLINE, 'teacher_id' => $this->teacher->id, 'title_ar' => 'حلقة المسجد']);
        Course::create(['title_ar' => 'مقفولة', 'enrollment_deadline' => now()->subDay()]);
        Course::create(['title_ar' => 'متوقفة', 'is_active' => false]);
    }

    private function studentToken(string $phone = '01012345678'): string
    {
        return $this->postJson('/api/v1/auth/register', ['name' => 'طالب', 'phone' => $phone, 'password' => 'secret123'])->json('token');
    }

    private function bearer(string $token): array
    {
        // Within one test the guard caches the last user; forget it when switching accounts.
        $this->app['auth']->forgetGuards();

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_catalogue_lists_open_groups_only_and_filters_by_type(): void
    {
        $this->getJson('/api/v1/academy/courses')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.teacher.name', 'الشيخ محمد');

        $this->getJson('/api/v1/academy/courses?type=online')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category.title', 'تحفيظ أونلاين');

        $this->getJson('/api/v1/academy/courses?type=nope')->assertStatus(422);
    }

    public function test_public_course_page_shows_upcoming_sessions_without_zoom_links(): void
    {
        $response = $this->getJson("/api/v1/academy/courses/{$this->online->id}")->assertOk();

        $response->assertJsonCount(1, 'data.sessions')->assertJsonPath('data.is_enrolled', false);
        $this->assertArrayNotHasKey('join_url', $response->json('data.sessions.0'));
        $this->assertStringNotContainsString('zoom.us', $response->getContent());
        // The teacher's WhatsApp is public so students can ask before booking.
        $response->assertJsonPath('data.teacher.whatsapp_url', 'https://wa.me/201000000001');
    }

    public function test_teachers_list_and_teacher_page_with_open_groups(): void
    {
        $this->getJson('/api/v1/academy/teachers')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'الشيخ محمد')
            ->assertJsonPath('data.0.open_courses_count', 2);

        $this->getJson("/api/v1/academy/teachers/{$this->teacher->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data.courses')
            ->assertJsonPath('data.whatsapp_url', 'https://wa.me/201000000001');

        $this->getJson('/api/v1/academy/teachers/999')->assertNotFound();
    }

    public function test_teacher_without_number_falls_back_to_academy_whatsapp(): void
    {
        \App\Models\Setting::create(['key' => 'whatsapp_phone', 'value' => '+201067818406', 'group' => 'contact']);
        $this->teacher->update(['whatsapp_phone' => null]);

        $this->getJson("/api/v1/academy/teachers/{$this->teacher->id}")
            ->assertJsonPath('data.whatsapp_url', 'https://wa.me/201067818406');
    }

    public function test_student_books_once_and_sees_pending_enrollment_without_links(): void
    {
        $auth = $this->bearer($this->studentToken());

        $this->postJson("/api/v1/academy/courses/{$this->online->id}/book", ['notes' => 'حافظ جزء عم'], $auth)
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');
        $this->postJson("/api/v1/academy/courses/{$this->online->id}/book", [], $auth)
            ->assertStatus(422)
            ->assertJsonPath('message', trans('api.booking.already', [], 'ar'));

        $this->getJson("/api/v1/academy/courses/{$this->online->id}", $auth)->assertJsonPath('data.is_enrolled', true);

        $mine = $this->getJson('/api/v1/academy/my/bookings', $auth)->assertOk()->assertJsonCount(1, 'data');
        $this->assertNull($mine->json('data.0.teacher_whatsapp_url'));
        $this->assertStringNotContainsString('zoom.us', $mine->getContent());
    }

    public function test_confirmed_student_gets_zoom_link_and_teacher_whatsapp(): void
    {
        $auth = $this->bearer($this->studentToken());
        $this->postJson("/api/v1/academy/courses/{$this->online->id}/book", [], $auth);
        Booking::query()->update(['status' => BookingStatusEnum::CONFIRMED]);

        $this->getJson('/api/v1/academy/my/bookings', $auth)
            ->assertOk()
            ->assertJsonPath('data.0.sessions.0.join_url', 'https://zoom.us/j/123')
            ->assertJsonPath('data.0.teacher_whatsapp_url', 'https://wa.me/201000000001')
            ->assertJsonMissingPath('data.0.sessions.0.start_url');
    }

    public function test_closed_group_cannot_be_booked_and_guests_must_log_in(): void
    {
        $closed = Course::where('title_ar', 'مقفولة')->first();

        $this->postJson("/api/v1/academy/courses/{$this->online->id}/book")->assertUnauthorized();
        $this->postJson("/api/v1/academy/courses/{$closed->id}/book", [], $this->bearer($this->studentToken()))->assertNotFound();
    }

    public function test_teacher_sees_own_groups_with_host_link_and_students(): void
    {
        $this->postJson("/api/v1/academy/courses/{$this->online->id}/book", [], $this->bearer($this->studentToken()));
        $auth = $this->bearer($this->teacherUser->createToken('test')->plainTextToken);

        $this->getJson('/api/v1/academy/teacher/courses', $auth)
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.pending_count', 1)
            ->assertJsonPath('data.0.sessions.0.start_url', 'https://zoom.us/s/123');

        $this->getJson("/api/v1/academy/teacher/courses/{$this->online->id}/students", $auth)
            ->assertOk()
            ->assertJsonPath('data.0.name', 'طالب')
            ->assertJsonPath('data.0.whatsapp_url', 'https://wa.me/201012345678');
    }

    public function test_teacher_cannot_see_another_teachers_students_and_students_cannot_use_teacher_routes(): void
    {
        $other = User::create(['name' => 'آخر', 'email' => 'o@example.com', 'password' => 'secret123']);
        $other->assignRole('teacher');
        Teacher::create(['user_id' => $other->id, 'name_ar' => 'معلم آخر']);
        $auth = $this->bearer($other->createToken('test')->plainTextToken);

        $this->getJson("/api/v1/academy/teacher/courses/{$this->online->id}/students", $auth)->assertNotFound();
        $this->getJson('/api/v1/academy/teacher/courses', $this->bearer($this->studentToken()))->assertForbidden();
    }
}
