<?php

namespace App\Api\Modules\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthTokenResource extends JsonResource
{
    /** @param  array{token: string, expires_in: int, user?: \App\Models\User}  $resource */
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
