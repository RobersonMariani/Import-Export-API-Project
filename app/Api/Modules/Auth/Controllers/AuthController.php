<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\Controllers;

use App\Api\Modules\Auth\Data\LoginAuthData;
use App\Api\Modules\Auth\Data\RegisterAuthData;
use App\Api\Modules\Auth\Resources\AuthTokenResource;
use App\Api\Modules\Auth\Resources\UserResource;
use App\Api\Modules\Auth\UseCases\GetMeAuthUseCase;
use App\Api\Modules\Auth\UseCases\LoginAuthUseCase;
use App\Api\Modules\Auth\UseCases\LogoutAuthUseCase;
use App\Api\Modules\Auth\UseCases\RefreshTokenAuthUseCase;
use App\Api\Modules\Auth\UseCases\RegisterAuthUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request, RegisterAuthUseCase $useCase): JsonResponse
    {
        $data = RegisterAuthData::validateAndCreate($request->all());
        $result = $useCase->execute($data);

        return AuthTokenResource::make([
            'token' => $result['token'],
            'expires_in' => $result['expires_in'],
            'user' => $result['user'],
        ])->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function login(Request $request, LoginAuthUseCase $useCase): AuthTokenResource|JsonResponse
    {
        $data = LoginAuthData::validateAndCreate($request->all());
        $result = $useCase->execute($data);

        if ($result === null) {
            return response()->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return AuthTokenResource::make($result);
    }

    public function refresh(RefreshTokenAuthUseCase $useCase): AuthTokenResource
    {
        $result = $useCase->execute();

        return AuthTokenResource::make($result);
    }

    public function logout(LogoutAuthUseCase $useCase): JsonResponse
    {
        $useCase->execute();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function me(GetMeAuthUseCase $useCase): JsonResponse
    {
        $user = $useCase->execute();

        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated'], Response::HTTP_UNAUTHORIZED);
        }

        return UserResource::make($user)->response()->setStatusCode(Response::HTTP_OK);
    }
}
