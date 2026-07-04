<?php

namespace App\Services\ThirdParties\Zoom;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Minimal Zoom Server-to-Server OAuth client for creating meetings.
 * Degrades gracefully: when credentials are absent, methods return null
 * so the app keeps working and the admin can add a link manually.
 */
class ZoomService
{
    private const TOKEN_URL = 'https://zoom.us/oauth/token';

    private const API_BASE = 'https://api.zoom.us/v2';

    public function isConfigured(): bool
    {
        return filled(config('zoom.account_id'))
            && filled(config('zoom.client_id'))
            && filled(config('zoom.client_secret'));
    }

    /**
     * Create a scheduled Zoom meeting.
     *
     * @return array{id: string, join_url: string, start_url: string}|null
     */
    public function createMeeting(string $topic, Carbon $startAt, int $durationMinutes = 60): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $token = $this->accessToken();

        if (! $token) {
            return null;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post(self::API_BASE.'/users/me/meetings', [
                'topic' => $topic,
                'type' => 2, // scheduled meeting
                'start_time' => $startAt->clone()->setTimezone(config('zoom.timezone'))->format('Y-m-d\TH:i:s'),
                'duration' => $durationMinutes,
                'timezone' => config('zoom.timezone'),
                'settings' => [
                    'join_before_host' => true,
                    'waiting_room' => false,
                    'approval_type' => 2,
                ],
            ]);

        if ($response->failed()) {
            Log::warning('Zoom meeting creation failed', ['status' => $response->status(), 'body' => $response->body()]);

            return null;
        }

        return [
            'id' => (string) $response->json('id'),
            'join_url' => (string) $response->json('join_url'),
            'start_url' => (string) $response->json('start_url'),
        ];
    }

    /**
     * Fetch (and cache) a Server-to-Server OAuth access token.
     */
    private function accessToken(): ?string
    {
        return Cache::remember('zoom.access_token', now()->addMinutes(50), function (): ?string {
            $response = Http::asForm()
                ->withBasicAuth(config('zoom.client_id'), config('zoom.client_secret'))
                ->post(self::TOKEN_URL, [
                    'grant_type' => 'account_credentials',
                    'account_id' => config('zoom.account_id'),
                ]);

            if ($response->failed()) {
                Log::warning('Zoom token request failed', ['status' => $response->status(), 'body' => $response->body()]);

                return null;
            }

            return $response->json('access_token');
        });
    }
}
