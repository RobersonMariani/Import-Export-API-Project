<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Controllers;

use App\Api\Modules\Export\Data\CreateExportData;
use App\Api\Modules\Export\Resources\ExportResource;
use App\Api\Modules\Export\UseCases\CreateExportUseCase;
use App\Api\Modules\Export\UseCases\DownloadExportUseCase;
use App\Api\Modules\Export\UseCases\GetExportUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
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
}
