<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Controllers;

use App\Api\Modules\Import\Data\CreateImportData;
use App\Api\Modules\Import\Data\ImportQueryData;
use App\Api\Modules\Import\Resources\ImportResource;
use App\Api\Modules\Import\UseCases\CreateImportUseCase;
use App\Api\Modules\Import\UseCases\GetImportsUseCase;
use App\Api\Modules\Import\UseCases\GetImportUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller
{
    public function index(Request $request, GetImportsUseCase $useCase): AnonymousResourceCollection
    {
        $query = ImportQueryData::validateAndCreate($request->query());

        $userId = $request->user()?->getKey();

        return ImportResource::collection($useCase->execute($query, $userId !== null ? (int) $userId : null));
    }

    public function store(Request $request, CreateImportUseCase $useCase): Response
    {
        $data = CreateImportData::validateAndCreate($request->all());

        $userId = (int) $request->user()?->getKey();
        $import = $useCase->execute($data, $userId);

        return ImportResource::make($import)
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function show(Request $request, string $import, GetImportUseCase $useCase): ImportResource
    {
        $userId = $request->user()?->getKey();

        return ImportResource::make($useCase->execute($import, $userId !== null ? (int) $userId : null));
    }
}
