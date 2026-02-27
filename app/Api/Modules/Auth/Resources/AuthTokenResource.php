<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array{token: string, expires_in: int, user?: User} $resource
 */
class AuthTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'access_token' => $this->resource['token'],
            'token_type' => 'bearer',
            'expires_in' => $this->resource['expires_in'],
        ];

        if (isset($this->resource['user'])) {
            $data['user'] = UserResource::make($this->resource['user'])->resolve($request);
        }

        return $data;
    }
}
