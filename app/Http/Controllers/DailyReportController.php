<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//for RESUMABLE JS

use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Illuminate\Support\Facades\Storage;

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
            'report_images.*' => 'nullable|file|mimes:jpg,jpeg,png|max:204800', //100 MB
            'report_docs.*' => 'nullable|file|mimes:pdf,doc,docx,txt|max:204800', //100 MB
        ]);
        
        $images = [];
        $docs = [];

        // standard image uploads
        if ($request->hasFile('report_images')) {
            foreach ($request->file('report_images') as $image) {
                $path = $image->store('daily_reports/images', 'public');
                $images[] = $path;
            }
        }

        // standard doc uploads
        if ($request->hasFile('report_docs')) {
            foreach ($request->file('report_docs') as $doc) {
                $path = $doc->store('daily_reports/docs', 'public');
                $docs[] = $path;
            }
        }

        // Uploaded image paths from frontend (Resumable)
        if ($request->filled('uploaded_images')) {
            $json = json_decode($request->uploaded_images, true);
            if (is_array($json)) {
                $images = array_merge($images, $json);
            }
        }

        // Uploaded doc paths from frontend (Resumable)
        if ($request->filled('uploaded_docs')) {
            $json = json_decode($request->uploaded_docs, true);
            if (is_array($json)) {
                $docs = array_merge($docs, $json);
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
        return view('index', compact('reports'));
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
        $path = $file->store('daily_reports/uppy-uploads', 'public');

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

    public function resumableCreate()
    {
        return view('daily_reports_resumable.create');
    }

    public function resumableUpload(Request $request)
    {
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        if ($receiver->isUploaded() === false) {
            return response()->json(['message' => 'File not uploaded'], 400);
        }

        $save = $receiver->receive(); // receive chunk

        if ($save->isFinished()) {

            $file = $save->getFile();
            // Save directly to final folder
            $path = $file->store('daily_reports/resumable', 'public');

            return response()->json([
                'path' => $path,
                'name' => $file->getClientOriginalName()
            ]);
        }

        $handler = $save->handler();

        return response()->json([
            "done" => $handler->getPercentageDone(),
        ]);
    }

}