<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The API is stateless, so the locale comes from the Accept-Language header
 * (the app sends "ar" or "en"). Arabic is the default.
 */
class SetLocaleFromHeader
{
    /**
     * @var array<int, string>
     */
    private array $supported = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($request->getPreferredLanguage($this->supported) ?? 'ar');

        return $next($request);
    }
}
