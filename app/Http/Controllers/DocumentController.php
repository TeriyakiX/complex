<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    public function index()
    {
        $folder = 'docs'; // папка с документами
        $files = Storage::disk('public')->files($folder);

        $data = collect($files)->map(function ($file) {
            $basename = basename($file);
            return [
                'name' => $basename,
                'url'  => url('/api/documents/' . urlencode($basename)),
            ];
        });

        return response()->json($data);
    }

    public function show($filename)
    {
        $filename = urldecode($filename);
        $path = 'docs/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['error' => 'Файл не найден'], Response::HTTP_NOT_FOUND);
        }

        // просто отдаём PDF для просмотра
        return response()->file(storage_path('app/public/' . $path));
    }
}
