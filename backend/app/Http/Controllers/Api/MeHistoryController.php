<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Me\RentalHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MeHistoryController extends Controller
{
    public function __construct(
        private readonly RentalHistoryService $history,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:all,car,campervan,guesthouse,vehicle'],
            'period' => ['nullable', 'string', 'in:all,upcoming,past'],
        ]);

        $type = $validated['type'] ?? null;
        if ($type === 'all') {
            $type = null;
        }

        $period = $validated['period'] ?? null;
        if ($period === 'all') {
            $period = null;
        }

        $result = $this->history->forUser($request->user(), $type, $period);

        return response()->json([
            'data' => $result['items'],
            'meta' => $result['summary'],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $result = $this->history->forUser($request->user());
        $filename = 'my-terrabook-trips-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($result) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'type',
                'reference',
                'title',
                'location',
                'starts_at',
                'ends_at',
                'status',
                'total',
                'currency',
            ]);

            foreach ($result['items'] as $item) {
                fputcsv($out, [
                    $item['type'],
                    $item['reference'],
                    $item['title'],
                    $item['subtitle'] ?? '',
                    $item['starts_at'],
                    $item['ends_at'],
                    $item['status'],
                    $item['total'],
                    $item['currency'],
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
