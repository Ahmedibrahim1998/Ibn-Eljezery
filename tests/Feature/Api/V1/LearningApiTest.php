<?php

namespace Tests\Feature\Api\V1;

use App\Enum\Learning\ContentStatusEnum;
use App\Enum\Learning\MadhhabEnum;
use App\Models\LearningSection;
use App\Models\LearningSubject;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningApiTest extends TestCase
{
    use RefreshDatabase;

    private LearningSection $fiqh;

    private LearningSubject $maliki;

    private LearningSubject $shafii;

    protected function setUp(): void
    {
        parent::setUp();

        // The app always sends its language. (Symfony test requests default to "en-us" otherwise.)
        $this->withHeader('Accept-Language', 'ar');

        $this->fiqh = LearningSection::create(['slug' => 'fiqh', 'title_ar' => 'الفقه', 'title_en' => 'Fiqh']);
        LearningSection::create(['slug' => 'hidden', 'title_ar' => 'مخفي', 'is_active' => false]);

        $this->maliki = $this->fiqh->subjects()->create(['title_ar' => 'فقه الطهارة', 'title_en' => 'Purification', 'madhhab' => MadhhabEnum::MALIKI]);
        $this->shafii = $this->fiqh->subjects()->create(['title_ar' => 'فقه الصلاة', 'madhhab' => MadhhabEnum::SHAFII]);
    }

    private function lesson(LearningSubject $subject, ContentStatusEnum $status, string $title = 'درس'): Lesson
    {
        return $subject->lessons()->create(['title_ar' => $title, 'content_ar' => '<p>نص الدرس</p>', 'status' => $status]);
    }

    public function test_lists_only_active_sections(): void
    {
        $this->getJson('/api/v1/learning/sections')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'fiqh')
            ->assertJsonPath('data.0.title', 'الفقه');
    }

    public function test_locale_comes_from_accept_language_header(): void
    {
        $this->getJson('/api/v1/learning/sections', ['Accept-Language' => 'en'])
            ->assertJsonPath('data.0.title', 'Fiqh');
    }

    public function test_subjects_can_be_filtered_by_madhhab_and_count_published_lessons_only(): void
    {
        $this->lesson($this->maliki, ContentStatusEnum::PUBLISHED);
        $this->lesson($this->maliki, ContentStatusEnum::DRAFT);

        $this->getJson('/api/v1/learning/sections/fiqh/subjects?madhhab=maliki')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.madhhab', 'maliki')
            ->assertJsonPath('data.0.lessons_count', 1);

        $this->getJson('/api/v1/learning/sections/fiqh/subjects')->assertJsonCount(2, 'data');
        $this->getJson('/api/v1/learning/sections/fiqh/subjects?madhhab=unknown')->assertStatus(422);
        $this->getJson('/api/v1/learning/sections/hidden/subjects')->assertNotFound();
    }

    public function test_only_published_lessons_reach_the_app(): void
    {
        $published = $this->lesson($this->maliki, ContentStatusEnum::PUBLISHED, 'منشور');
        foreach ([ContentStatusEnum::DRAFT, ContentStatusEnum::IN_REVIEW, ContentStatusEnum::APPROVED, ContentStatusEnum::REJECTED] as $status) {
            $hidden = $this->lesson($this->maliki, $status);
            $this->getJson("/api/v1/learning/lessons/{$hidden->id}")->assertNotFound();
        }

        $this->getJson("/api/v1/learning/subjects/{$this->maliki->id}/lessons")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'منشور')
            ->assertJsonMissingPath('data.0.content');

        $this->getJson("/api/v1/learning/lessons/{$published->id}")
            ->assertOk()
            ->assertJsonPath('data.content', '<p>نص الدرس</p>');
    }

    public function test_lessons_of_an_inactive_subject_are_hidden(): void
    {
        $lesson = $this->lesson($this->shafii, ContentStatusEnum::PUBLISHED);
        $this->shafii->update(['is_active' => false]);

        $this->getJson("/api/v1/learning/subjects/{$this->shafii->id}/lessons")->assertNotFound();
        $this->getJson("/api/v1/learning/lessons/{$lesson->id}")->assertNotFound();
    }
}
