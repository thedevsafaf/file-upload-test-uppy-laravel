<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\DailyReport;

class DailyReportController extends Controller
{

    public function create()
    {
        return view('daily_reports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'report_images.*' => 'nullable|file|mimes:jpg,jpeg,png|max:20480', //100 MB
            'report_docs.*' => 'nullable|file|mimes:pdf,doc,docx,txt|max:204800', //100 MB
        ]);
        
        $images = [];
        $docs = [];

        if ($request->hasFile('report_images')) {
            foreach ($request->file('report_images') as $image) {
                $path = $image->store('daily_reports/images', 'public');
                $images[] = $path;
            }
        }

        if ($request->hasFile('report_docs')) {
            foreach ($request->file('report_docs') as $doc) {
                $path = $doc->store('daily_reports/docs', 'public');
                $docs[] = $path;
            }
        }

        DailyReport::create([
            'report_date' => $request->report_date,
            'report_images' => $images,
            'report_docs' => $docs,
        ]);

        return response()->json(['message' => 'Report submitted successfully']);
    }

    public function index()
    {
        $reports = DailyReport::latest()->get();
        return view('daily_reports.index', compact('reports'));
    }

    public function uppyCreate()
    {
        return view('daily_reports_uppy.create');
    }

    public function uppyUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100 MB
        ]);

        $file = $request->file('file');
        $path = $file->store('daily_reports/uploads', 'public');

        return response()->json([
            'success' => true,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
        ]);
    }

    public function storeUppy(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'uploaded_files' => 'required|json',
        ]);

        $files = json_decode($request->uploaded_files, true);

        // Separate images vs docs (optional logic)
        $images = array_filter($files, fn($f) => preg_match('/\.(jpe?g|png|gif)$/i', $f));
        $docs   = array_filter($files, fn($f) => preg_match('/\.(pdf|docx?|txt)$/i', $f));

        DailyReport::create([
            'report_date' => $request->report_date,
            'report_images' => array_values($images),
            'report_docs' => array_values($docs),
        ]);

        return response()->json(['message' => 'Daily Report with Uppy uploaded!']);
    }

}