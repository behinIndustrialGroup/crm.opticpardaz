<?php

namespace BehinFileControl\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public static function store($file, $dir = 'docs')
    {
        if (!in_array($file->getMimeType(), config('file_control.valid_file_type'))) {
            return [
                'status' => 400,
                'message' => trans("File Format Is Invalid")
            ];
        }
        $name = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = Storage::disk('parspack')->putFileAs('', $file, $name);
        Log::info($path);

        if ($path) {
            $downloadUrl = "https://3117204815.cloudydl.com/$path";
            Log::info($downloadUrl);
            return [
                'status' => 200,
                'message' => trans("File Uploaded"),
                'dir' => $downloadUrl
            ];

        }
        return [
            'status' => 500,
            'message' => trans("Error In Uploading File")
        ];

        $full_path = public_path($dir);
        if (!is_dir($full_path)) {
            mkdir($full_path);
        }
        $full_name = $full_path . '/' . $name;
        $result = move_uploaded_file($file, $full_name);
        if ($result) {
            return [
                'status' => 200,
                'message' => trans("File Uploaded"),
                'dir' => $dir . '/' . $name
            ];
        }
        return [
            'status' => 500,
            'message' => trans("Error In Uploading File")
        ];
    }
}
