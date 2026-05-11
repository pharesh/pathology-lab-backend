<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ReferenceRange;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportService
{
    public function generateOrderReport(Order $order): \Barryvdh\DomPDF\PDF
    {
        $order->load([
            'patient',
            'lab',
            'orderItems.test.referenceRanges',
            'orderItems.results',
        ]);

        $data = [
            'order'   => $order,
            'patient' => $order->patient,
            'lab'     => $order->lab,
            'items'   => $order->orderItems,
        ];

        return Pdf::loadView('pdf.patient_report', $data)
            ->setPaper('a4', 'portrait');
    }

    public function generateInvoice(Order $order): \Barryvdh\DomPDF\PDF
    {
        $order->load(['patient', 'lab', 'bill', 'orderItems.test']);

        $data = [
            'order'   => $order,
            'patient' => $order->patient,
            'lab'     => $order->lab,
            'bill'    => $order->bill,
            'items'   => $order->orderItems,
        ];

        return Pdf::loadView('pdf.invoice', $data)
            ->setPaper('a4', 'portrait');
    }

    public function matchReferenceRange(string $parameterName, int $patientAge, string $ageUnit, string $gender, array $ranges): ?ReferenceRange
    {
        foreach ($ranges as $range) {
            if (strtolower($range->parameter_name) !== strtolower($parameterName)) continue;
            if ($range->gender_filter !== 'all' && $range->gender_filter !== $gender) continue;
            if ($range->age_min !== null && $patientAge < $range->age_min) continue;
            if ($range->age_max !== null && $patientAge > $range->age_max) continue;
            return $range;
        }
        return null;
    }
}
