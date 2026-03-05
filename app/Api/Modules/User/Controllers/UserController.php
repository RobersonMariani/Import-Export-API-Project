<?php

declare(strict_types=1);

namespace App\Api\Modules\User\Controllers;

use App\Api\Modules\User\Data\CreateUserData;
use App\Api\Modules\User\Data\UpdateUserData;
use App\Api\Modules\User\Data\UserQueryData;
use App\Api\Modules\User\Resources\UserResource;
use App\Api\Modules\User\UseCases\CountUsersUseCase;
use App\Api\Modules\User\UseCases\CreateUserUseCase;
use App\Api\Modules\User\UseCases\DeleteUserUseCase;
use App\Api\Modules\User\UseCases\GetUsersUseCase;
use App\Api\Modules\User\UseCases\GetUserUseCase;
use App\Api\Modules\User\UseCases\UpdateUserUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index(Request $request, GetUsersUseCase $useCase): AnonymousResourceCollection
    {
        $query = UserQueryData::validateAndCreate($request->query());

        return UserResource::collection($useCase->execute($query));
    }

    public function store(Request $request, CreateUserUseCase $useCase): Response
    {
        $data = CreateUserData::validateAndCreate($request->all());

        return UserResource::make($useCase->execute($data))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(int $user, GetUserUseCase $useCase): UserResource
    {
        return UserResource::make($useCase->execute($user));
    }

    public function update(int $user, Request $request, UpdateUserUseCase $useCase): UserResource
    {
        $data = UpdateUserData::validateAndCreate(array_merge($request->all(), ['user_id' => $user]));

        return UserResource::make($useCase->execute($user, $data));
    }

    public function destroy(int $user, DeleteUserUseCase $useCase): JsonResponse
    {
        $useCase->execute($user);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function count(Request $request, CountUsersUseCase $useCase): JsonResponse
    {
        $filters = array_filter([
            'search' => $request->query('search'),
            'role' => $request->query('role'),
            'state' => $request->query('state'),
            'city' => $request->query('city'),
        ], fn ($v) => $v !== null && $v !== '');

        return response()->json([
            'count' => $useCase->execute($filters),
        ]);
    }
}
