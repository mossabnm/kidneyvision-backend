<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>KidneyVision AI - Analysis Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .title {
            color: #2563eb;
            font-size: 24px;
            margin: 0;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
        }
        .section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .section-title {
            font-size: 18px;
            color: #131b2e;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 10px;
            margin-top: 0;
        }
        .data-row {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .value {
            color: #444;
        }
        .image-container {
            text-align: center;
            margin: 20px 0;
        }
        .image-container img {
            max-width: 100%;
            max-height: 300px;
            border: 1px solid #ccc;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            margin-top: 40px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .recommendation-list {
            margin: 0;
            padding-left: 20px;
        }
        .badge-red {
            color: #ef4444;
            font-weight: bold;
        }
        .badge-green {
            color: #10b981;
            font-weight: bold;
        }
        .badge-yellow {
            color: #f59e0b;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">KidneyVision AI</h1>
        <div class="subtitle">Diagnostic Analysis Report</div>
    </div>

    <div class="section">
        <h2 class="section-title">Patient & Analysis Information</h2>
        <div class="data-row">
            <span class="label">Patient Name:</span>
            <span class="value">{{ $analysis->original_filename }}</span>
        </div>
        <div class="data-row">
            <span class="label">Analysis ID:</span>
            <span class="value">KV-{{ str_pad($analysis->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="data-row">
            <span class="label">Date Processed:</span>
            <span class="value">{{ $analysis->processed_at ? $analysis->processed_at->format('M d, Y h:i A') : 'Pending' }}</span>
        </div>
        <div class="data-row">
            <span class="label">Physician:</span>
            <span class="value">{{ $user->name }} ({{ $user->email }})</span>
        </div>
    </div>

    @if($analysis->image_path)
    <div class="image-container">
        <h3>Uploaded Ultrasound Image</h3>
        <?php
            // DomPDF needs absolute paths or base64 data URIs
            $path = storage_path('app/public/' . $analysis->image_path);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            if (file_exists($path)) {
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                echo '<img src="' . $base64 . '" alt="Ultrasound Scan">';
            }
        ?>
    </div>
    @endif

    <div class="section">
        <h2 class="section-title">AI Findings</h2>
        <div class="data-row">
            <span class="label">Prediction:</span>
            <span class="value 
                @if($analysis->prediction === 'Anomaly') badge-red 
                @elseif($analysis->prediction === 'Normal') badge-green 
                @else badge-yellow @endif
            ">
                {{ strtoupper($analysis->prediction) }}
            </span>
        </div>
        <div class="data-row">
            <span class="label">Confidence Score:</span>
            <span class="value">{{ $analysis->confidence }}%</span>
        </div>
        
        @if($analysis->report)
        <div class="data-row" style="margin-top: 15px;">
            <span class="label">AI Summary:</span>
            <div class="value" style="margin-top: 5px;">{{ $analysis->report->summary }}</div>
        </div>
        @endif
    </div>

    @if($analysis->report && $analysis->report->recommendations)
    <div class="section">
        <h2 class="section-title">Clinical Recommendations</h2>
        <ul class="recommendation-list">
            @foreach($analysis->report->recommendations as $rec)
                <li>{{ $rec }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="footer">
        <p>This report was automatically generated by KidneyVision AI. It is intended for professional medical reference only and should not replace formal radiological diagnosis.</p>
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

</body>
</html>
