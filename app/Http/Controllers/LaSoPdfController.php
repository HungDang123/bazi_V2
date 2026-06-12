<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateLaSoPdfJob;
use App\Models\LaSoPdfExport;
use App\Services\LaSoPdfGeneratorService;
use App\Services\PdfDownloadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LaSoPdfController extends Controller
{
    public function queue(Request $request, LaSoPdfGeneratorService $generator): JsonResponse
    {
        $validated = $this->validatePdfParams($request);
        $params    = $generator->normalizeParams($validated);
        $userId    = (int) $request->user()->id;

        $export = LaSoPdfExport::query()->create([
            'id'          => (string) Str::uuid(),
            'user_id'     => $userId,
            'params_hash' => $generator->paramsHash($params),
            'params'      => $params,
            'q1_status'   => LaSoPdfExport::STATUS_PENDING,
            'q2_status'   => LaSoPdfExport::STATUS_PENDING,
            'queued_at'   => now(),
        ]);

        File::ensureDirectoryExists($export->storageDir(), 0755, true);

        GenerateLaSoPdfJob::dispatch($export->id, 1, $params);
        GenerateLaSoPdfJob::dispatch($export->id, 2, $params);

        return response()->json([
            'export_id' => $export->id,
            'q1_status' => $export->q1_status,
            'q2_status' => $export->q2_status,
        ]);
    }

    public function status(string $exportId, Request $request): JsonResponse
    {
        $export = $this->findOwnedExport($exportId, $request);

        return response()->json([
            'export_id' => $export->id,
            'quyen_1'   => $this->quyenPayload($export, 1),
            'quyen_2'   => $this->quyenPayload($export, 2),
        ]);
    }

    public function download(string $exportId, int $quyen, Request $request): Response
    {
        if (! in_array($quyen, [1, 2], true)) {
            abort(404);
        }

        $export = $this->findOwnedExport($exportId, $request);
        $status = $quyen === 1 ? $export->q1_status : $export->q2_status;
        $path   = $quyen === 1 ? $export->q1_path : $export->q2_path;

        if ($status !== LaSoPdfExport::STATUS_READY || ! $path || ! is_file($path)) {
            return response()->json([
                'error'  => 'PDF chưa sẵn sàng',
                'status' => $status,
            ], 409);
        }

        $filename = $quyen === 1
            ? PdfDownloadService::FILENAME_QUYEN_1
            : PdfDownloadService::FILENAME_QUYEN_2;

        return PdfDownloadService::serveStored($path, $filename);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePdfParams(Request $request): array
    {
        return $request->validate([
            'full_name'              => 'required|string|max:255',
            'y'                      => 'required|integer|min:1900|max:2100',
            'm'                      => 'required|integer|min:1|max:12',
            'd'                      => 'required|integer|min:1|max:31',
            'g'                      => 'nullable|string|in:male,female',
            'h'                      => 'nullable|integer|min:0|max:23',
            'minute'                 => 'nullable|integer|min:0|max:59',
            'address'                => 'nullable|string|max:500',
            'gender'                 => 'nullable|string|max:50',
            'birth_date'             => 'nullable|string|max:100',
            'bat_tu'                 => 'nullable|string|max:500',
            'uknow_birthdate'        => 'nullable|in:0,1',
            'chat_luong_thap_than'   => 'nullable|array',
            'bieu_do_ngu_hanh'       => 'nullable|array',
            'ngu_hanh_dong'          => 'nullable|array',
            'chi_so_bieu_do_cot'     => 'nullable|array',
            'phan_tram_nien_van'     => 'nullable|array',
            'hanh_noi_dung_nien_van' => 'nullable|array',
        ]);
    }

    private function findOwnedExport(string $exportId, Request $request): LaSoPdfExport
    {
        return LaSoPdfExport::query()
            ->where('id', $exportId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    /**
     * @return array{status: string, ready_at: ?string, error: ?string}
     */
    private function quyenPayload(LaSoPdfExport $export, int $quyen): array
    {
        if ($quyen === 1) {
            return [
                'status'   => $export->q1_status,
                'ready_at' => $export->q1_ready_at?->toIso8601String(),
                'error'    => $export->q1_error,
            ];
        }

        return [
            'status'   => $export->q2_status,
            'ready_at' => $export->q2_ready_at?->toIso8601String(),
            'error'    => $export->q2_error,
        ];
    }
}
