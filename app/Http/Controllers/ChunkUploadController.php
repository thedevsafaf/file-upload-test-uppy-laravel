<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyReport;

use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class ChunkUploadController extends Controller
{
    public function createChunkUppy()
    {
        return view('daily_reports_uppy.create-chunk');
    }

    // to catch uploaded_images and uploaded_docs separately
    public function storeChunkUppy(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'uploaded_images' => 'nullable|json',
            'uploaded_docs' => 'nullable|json',
        ]);

        $images = json_decode($request->uploaded_images, true) ?? [];
        $docs = json_decode($request->uploaded_docs, true) ?? [];

        DailyReport::create([
            'report_date' => $request->report_date,
            'report_images' => $images,
            'report_docs' => $docs,
        ]);

        return response()->json(['message' => 'Daily Report with Uppy uploaded successfully!']);
    }

    // to handle file uploads into paths based on the file extension
    public function uploadChunkUppy(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        // Determine folder based on file type
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $folder = 'daily_reports_chunks/images';
        } elseif (in_array($extension, ['pdf', 'doc', 'docx', 'txt'])) {
            $folder = 'daily_reports_chunks/docs';
        } else {
            // Fallback: upload to a "misc" folder if needed
            $folder = 'daily_reports_chunks/misc';
        }

        $path = $file->store($folder, 'public');

        return response()->json([
            'success' => true,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
        ]);
    }

    public function createChunkResumable()
    {
        return view('daily_reports_resumable.create-chunk');
    }

    public function uploadChunkResumable(Request $request)
    {
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        if ($receiver->isUploaded() === false) {
            return response()->json(['message' => 'File not uploaded'], 400);
        }

        $save = $receiver->receive(); // receive chunk

        if ($save->isFinished()) {

            $file = $save->getFile();
            $extension = strtolower($file->getClientOriginalExtension());

            // Determine folder based on file type
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $folder = 'daily_reports_resumable_chunks/images';
            } elseif (in_array($extension, ['pdf', 'doc', 'docx', 'txt'])) {
                $folder = 'daily_reports_resumable_chunks/docs';
            } else {
                $folder = 'daily_reports_resumable_chunks/misc'; // Optional: for unsupported files
            }

            $path = $file->store($folder, 'public');

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
