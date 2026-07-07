<?php

namespace App\Http\Responses\Auth;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as Contract;
use Illuminate\Http\RedirectResponse;

/**
 * After logging out of any Filament panel (admin / teacher / supervisor),
 * send the user to the shared login portal instead of the panel's own login.
 */
class LogoutResponse implements Contract
{
    public function toResponse($request): RedirectResponse
    {
        return redirect()->to('/portal');
    }
}
