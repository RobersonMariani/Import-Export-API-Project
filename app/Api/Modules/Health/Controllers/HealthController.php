<?php

namespace App\Api\Modules\Health\Controllers;

use App\Api\Modules\Health\Resources\HealthResource;
use App\Api\Modules\Health\UseCases\CheckHealthUseCase;
use App\Http\Controllers\Controller;

class HealthController extends Controller
{
    public function check(CheckHealthUseCase $useCase): HealthResource
    {
        return HealthResource::make($useCase->execute());
    }
}
