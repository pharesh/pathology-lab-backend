<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function downloadReport(Order $order): StreamedResponse
    {
        $pdf = $this->reportService->generateOrderReport($order);
        $filename = 'Report_' . $order->patient->patient_uid . '_' . now()->format('Ymd') . '.pdf';

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function downloadInvoice(Order $order): StreamedResponse
    {
        $pdf = $this->reportService->generateInvoice($order);
        $filename = 'Invoice_' . ($order->bill->bill_uid ?? $order->order_uid) . '.pdf';

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
