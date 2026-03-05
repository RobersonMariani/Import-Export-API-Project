<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Controllers;

use App\Api\Modules\Export\Data\CreateExportData;
use App\Api\Modules\Export\Data\ExportQueryData;
use App\Api\Modules\Export\Resources\ExportResource;
use App\Api\Modules\Export\UseCases\CreateExportUseCase;
use App\Api\Modules\Export\UseCases\DeleteExportUseCase;
use App\Api\Modules\Export\UseCases\DownloadExportUseCase;
use App\Api\Modules\Export\UseCases\GetExportUseCase;
use App\Api\Modules\Export\UseCases\GetExportsUseCase;
use App\Api\Modules\Export\UseCases\RetryExportUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    public function index(Request $request, GetExportsUseCase $useCase): AnonymousResourceCollection
    {
        $query = ExportQueryData::validateAndCreate($request->query());
        $userId = $request->user()?->getKey();

        return ExportResource::collection($useCase->execute($query, $userId !== null ? (int) $userId : null));
    }

    public function store(Request $request, CreateExportUseCase $useCase): JsonResponse
    {
        $data = CreateExportData::validateAndCreate($request->all());
        $userId = (int) $request->user()?->getKey();
        $export = $useCase->execute($data, $userId);

        return ExportResource::make($export)
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function show(Request $request, string $export, GetExportUseCase $useCase): ExportResource|JsonResponse
    {
        $userId = (int) $request->user()?->getKey();

        return ExportResource::make($useCase->execute($export, $userId));
    }

    public function download(Request $request, string $export, DownloadExportUseCase $useCase): JsonResponse
    {
        $userId = (int) $request->user()?->getKey();
        $exportModel = $useCase->execute($export, $userId);
        $url = $useCase->getDownloadUrl($exportModel);

        return response()->json([
            'download_url' => $url,
        ]);
    }

    public function destroy(Request $request, string $export, DeleteExportUseCase $useCase): JsonResponse
    {
        $userId = (int) $request->user()?->getKey();
        $useCase->execute($export, $userId);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function retry(Request $request, string $export, RetryExportUseCase $useCase): JsonResponse
    {
        $userId = (int) $request->user()?->getKey();
        $result = $useCase->execute($export, $userId);

        return ExportResource::make($result)
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }
}
