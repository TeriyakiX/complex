<?php

namespace App\Http\Controllers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);

        $files = collect(Storage::disk('public')->files('docs'))
            ->map(function ($file) {
                return [
                    'name' => basename($file),
                    'url' => url('/api/documents/' . urlencode(basename($file))),
                ];
            });

        $paginator = new LengthAwarePaginator(
            $files->forPage($page, $perPage)->values(),
            $files->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $resourceData = JsonResource::collection($paginator)->response()->getData(true);

        return response()->json([
            'message' => 'Документы успешно получены',
            'data'    => $resourceData['data'],
            'links'   => $resourceData['links'],
            'meta'    => $resourceData['meta'],
        ]);
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
