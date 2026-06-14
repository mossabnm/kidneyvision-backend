<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\AnalysisServiceInterface;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PDFReportController extends Controller
{
    public function __construct(
        private readonly AnalysisServiceInterface $analysisService
    ) {}

    public function download(Request $request, int $id)
    {
        try {
            $analysis = $this->analysisService->getAnalysis($id, $request->user()->id);
            $analysis->load('report');

            if (!$analysis->report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report not yet generated for this analysis.'
                ], 404);
            }

            // Generate PDF using DOMPDF
            $pdf = Pdf::loadView('pdf.report', [
                'analysis' => $analysis,
                'user' => $request->user()
            ]);

            return $pdf->download("kidneyvision_report_{$analysis->id}.pdf");

        } catch (\Exception $e) {
            Log::error('Failed to generate PDF report', [
                'analysis_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF report.'
            ], 500);
        }
    }
}
