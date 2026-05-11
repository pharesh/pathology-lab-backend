<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    .header { background: #1a5276; color: #fff; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; }
    .header h1 { font-size: 20px; letter-spacing: 1px; }
    .header .sub { font-size: 10px; opacity: 0.85; margin-top: 3px; }
    .patient-bar { background: #eaf4fb; border: 1px solid #aed6f1; padding: 10px 20px; margin: 10px 0; }
    .patient-bar table { width: 100%; }
    .patient-bar td { padding: 2px 8px; font-size: 10.5px; }
    .patient-bar td:first-child { font-weight: bold; color: #1a5276; width: 120px; }
    .section-title { background: #2980b9; color: #fff; padding: 5px 20px; font-size: 12px; font-weight: bold; margin: 10px 0 0 0; }
    table.results { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    table.results th { background: #d6eaf8; color: #1a5276; padding: 5px 8px; font-size: 10px; text-align: left; border-bottom: 2px solid #2980b9; }
    table.results td { padding: 4px 8px; border-bottom: 1px solid #eaecee; font-size: 10.5px; }
    table.results tr:nth-child(even) td { background: #f8f9fa; }
    .abnormal { color: #c0392b; font-weight: bold; }
    .flag-H { color: #c0392b; font-weight: bold; }
    .flag-L { color: #1a5276; font-weight: bold; }
    .footer { border-top: 1px solid #ccc; margin-top: 20px; padding: 10px 20px; font-size: 9px; color: #555; display: flex; justify-content: space-between; }
    .sig-line { margin-top: 30px; border-top: 1px solid #333; width: 160px; text-align: center; font-size: 9px; padding-top: 3px; }
    .page-num { text-align: right; font-size: 9px; color: #888; margin: 4px 20px; }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div>
        <h1>{{ strtoupper($lab->name) }}</h1>
        @if ($lab->address)
        <div class="sub">{{ $lab->address }}</div>
        @endif
        @if ($lab->phone)
        <div class="sub">Phone: {{ $lab->phone }}{{ $lab->email ? ' | ' . $lab->email : '' }}</div>
        @endif
    </div>
    <div style="text-align:right; font-size:10px;">
        <div><strong>Report Date:</strong> {{ now()->format('d M Y') }}</div>
        <div><strong>Report No:</strong> {{ $order->order_uid }}</div>
    </div>
</div>

{{-- Patient Info --}}
<div class="patient-bar">
    <table>
        <tr>
            <td>Patient ID</td><td>{{ $patient->patient_uid }}</td>
            <td>Name</td><td>{{ $patient->name }}</td>
            <td>Age / Gender</td><td>{{ $patient->age }} {{ $patient->age_unit }} / {{ ucfirst($patient->gender) }}</td>
        </tr>
        <tr>
            <td>Phone</td><td>{{ $patient->phone }}</td>
            <td>Referred By</td><td>{{ $patient->referred_by ?? 'Self' }}</td>
            <td>Collection Date</td><td>{{ $order->ordered_at->format('d M Y H:i') }}</td>
        </tr>
    </table>
</div>

{{-- Results per Test --}}
@foreach ($items as $item)
<div class="section-title">{{ $item->test->test_name }} ({{ $item->test->test_code }})</div>
<table class="results">
    <thead>
        <tr>
            <th style="width:30%">Parameter</th>
            <th style="width:18%">Observed Value</th>
            <th style="width:10%">Unit</th>
            <th style="width:25%">Reference Range</th>
            <th style="width:8%">Flag</th>
            <th style="width:9%">Remarks</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($item->results as $result)
        @php
            $range = $item->test->referenceRanges->first(fn($r) => strtolower($r->parameter_name) === strtolower($result->parameter_name));
            $refDisplay = $range ? ($range->text_range ?? ($range->min_value . ' - ' . $range->max_value)) : '-';
            $flag = '';
            if ($result->is_abnormal) {
                if ($range && $range->min_value !== null && is_numeric($result->observed_value) && (float)$result->observed_value < (float)$range->min_value) $flag = 'L';
                elseif ($range && $range->max_value !== null && is_numeric($result->observed_value) && (float)$result->observed_value > (float)$range->max_value) $flag = 'H';
                else $flag = '*';
            }
        @endphp
        <tr>
            <td>{{ $result->parameter_name }}</td>
            <td class="{{ $result->is_abnormal ? 'abnormal' : '' }}">{{ $result->observed_value }}</td>
            <td>{{ $result->unit ?? ($range->unit ?? '-') }}</td>
            <td>{{ $refDisplay }}</td>
            <td class="{{ $flag ? 'flag-' . $flag : '' }}">{{ $flag }}</td>
            <td>{{ $result->remarks ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endforeach

{{-- Footer --}}
<div class="footer">
    <div>
        <em>This report is computer generated and valid without signature. Results are for clinical reference only.</em>
    </div>
    <div>
        <div class="sig-line">Pathologist Signature & Seal</div>
    </div>
</div>

</body>
</html>
