<?php

namespace App\Api\Modules\Health\Controllers;

use App\Api\Modules\Health\UseCases\GetMetricsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class MetricsController extends Controller
{
    public function index(GetMetricsUseCase $useCase): Response
    {
        $metrics = $useCase->execute();
        $lines = [];

        $lines[] = '# HELP app_up Application availability (1 = up, 0 = down)';
        $lines[] = '# TYPE app_up gauge';
        $lines[] = 'app_up 1';

        $lines[] = '';
        $lines[] = '# HELP import_total Total imports by status';
        $lines[] = '# TYPE import_total counter';
        foreach ($metrics['import_counts'] as $status => $count) {
            $lines[] = sprintf('import_total{status="%s"} %d', $status, $count);
        }

        $lines[] = '';
        $lines[] = '# HELP export_total Total exports by status';
        $lines[] = '# TYPE export_total counter';
        foreach ($metrics['export_counts'] as $status => $count) {
            $lines[] = sprintf('export_total{status="%s"} %d', $status, $count);
        }

        $lines[] = '';
        $lines[] = '# HELP queue_size Number of jobs in queue';
        $lines[] = '# TYPE queue_size gauge';
        foreach ($metrics['queue_sizes'] as $queue => $size) {
            $lines[] = sprintf('queue_size{queue="%s"} %d', $queue, $size);
        }

        $content = implode("\n", $lines);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8; version=0.0.4',
        ]);
    }
}
