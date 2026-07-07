<?php

namespace App\Http\Middleware\Filament;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as BaseAuthenticate;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Like Filament's Authenticate, but when a logged-in user opens a panel that
 * isn't theirs we send them back to their own panel (or the portal) instead of
 * showing a 403 error page — the same friendly behaviour for every role.
 */
class Authenticate extends BaseAuthenticate
{
    /**
     * @param  array<string>  $guards
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);

            return; /** @phpstan-ignore-line */
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $guard->user();

        $panel = Filament::getCurrentPanel();

        if ($user instanceof FilamentUser && ! $user->canAccessPanel($panel)) {
            throw new HttpResponseException(redirect()->to($this->homePanelUrl($user)));
        }
    }

    /** URL of the first panel this user can access, or the shared portal. */
    protected function homePanelUrl(Model $user): string
    {
        foreach (Filament::getPanels() as $panel) {
            if ($user instanceof FilamentUser && $user->canAccessPanel($panel)) {
                // Build from the panel's own path — $panel->getUrl() is resolved
                // against the *current* panel here and can return the wrong one.
                return url('/'.ltrim($panel->getPath(), '/'));
            }
        }

        return url('/portal');
    }
}
