<?php

namespace App\Observers;

use App\Models\CourseSession;
use App\Services\ThirdParties\Zoom\ZoomService;

class CourseSessionObserver
{
    public function __construct(
        private readonly ZoomService $zoom,
    ) {}

    /**
     * When an online session is created, provision a Zoom meeting for it.
     */
    public function created(CourseSession $session): void
    {
        $this->ensureZoomMeeting($session);
    }

    /**
     * If a session becomes online later (or still lacks a link), try again.
     */
    public function updated(CourseSession $session): void
    {
        $this->ensureZoomMeeting($session);
    }

    private function ensureZoomMeeting(CourseSession $session): void
    {
        if (! $session->isOnline() || filled($session->zoom_join_url) || ! $this->zoom->isConfigured()) {
            return;
        }

        $topic = $session->course?->localized('title') ?? 'Session';

        $meeting = $this->zoom->createMeeting(
            topic: $topic,
            startAt: $session->starts_at,
            durationMinutes: $session->duration_minutes ?? 60,
        );

        if ($meeting) {
            // saveQuietly to avoid re-triggering the observer.
            $session->forceFill([
                'zoom_meeting_id' => $meeting['id'],
                'zoom_join_url' => $meeting['join_url'],
                'zoom_start_url' => $meeting['start_url'],
            ])->saveQuietly();
        }
    }
}
