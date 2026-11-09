<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\DTOs\Api\V1\Auth\LoginDTO;
use App\Http\DTOs\Api\V1\Auth\RegisterDTO;
use App\Http\Middleware\Api\SetLocaleFromHeader;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Models\User;
use App\Services\Api\V1\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * Mobile app sign-up / login with Sanctum tokens.
 * The app sends `Authorization: Bearer {token}` on every authenticated call.
 */
#[Prefix('auth')]
#[Middleware(SetLocaleFromHeader::class)]
class AuthController extends Controller
{
    public function __construct(private readonly AuthService $service) {}

    #[Post('register', name: 'api.v1.auth.register', middleware: 'throttle:5,1')]
    public function register(Request $request): JsonResponse
    {
        [$user, $token] = $this->service->register(RegisterDTO::fromRequest($request));

        return $this->tokenResponse($user, $token, Response::HTTP_CREATED);
    }

    #[Post('login', name: 'api.v1.auth.login', middleware: 'throttle:10,1')]
    public function login(Request $request): JsonResponse
    {
        [$user, $token] = $this->service->login(LoginDTO::fromRequest($request));

        return $this->tokenResponse($user, $token);
    }

    #[Get('me', name: 'api.v1.auth.me', middleware: 'auth:sanctum')]
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    #[Post('logout', name: 'api.v1.auth.logout', middleware: 'auth:sanctum')]
    public function logout(Request $request): Response
    {
        $this->service->logout($request->user());

        return response()->noContent();
    }

    /** Delete my account (required by Google Play). */
    #[Delete('me', name: 'api.v1.auth.delete', middleware: 'auth:sanctum')]
    public function destroy(Request $request): Response
    {
        $this->service->deleteAccount($request->user());

        return response()->noContent();
    }

    private function tokenResponse(User $user, string $token, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ], $status);
    }
}
