<?php

declare(strict_types=1);

namespace App\Api\Modules\Health\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'status' => $data['status']->value,
            'status_label' => $data['status']->label(),
            'services' => $data['services'],
            'timestamp' => $data['timestamp']->toIso8601String(),
        ];
    }
}
