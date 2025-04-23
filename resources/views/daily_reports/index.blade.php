<!DOCTYPE html>
<html>
<head>
    <title>Daily Reports</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 2rem;
            max-width: 800px;
            margin: auto;
        }
        .report {
            border: 1px solid #ccc;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
        }
        .file-list {
            margin-top: 0.5rem;
        }
        .file-list a {
            display: block;
        }
    </style>
</head>
<body>

    <h1>Submitted Daily Reports</h1>

    @forelse ($reports as $report)
        <div class="report">
            <strong>Date:</strong> {{ $report->report_date }}<br>

            <div class="file-list">
                <strong>Images:</strong>
                @if ($report->report_images)
                    @foreach ($report->report_images as $image)
                        <a href="{{ asset('storage/' . $image) }}" target="_blank">{{ basename($image) }}</a>
                    @endforeach
                @else
                    <p>No images</p>
                @endif
            </div>

            <div class="file-list">
                <strong>Documents:</strong>
                @if ($report->report_docs)
                    @foreach ($report->report_docs as $doc)
                        <a href="{{ asset('storage/' . $doc) }}" target="_blank">{{ basename($doc) }}</a>
                    @endforeach
                @else
                    <p>No documents</p>
                @endif
            </div>
        </div>
    @empty
        <p>No reports found.</p>
    @endforelse

</body>
</html>
