<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::query();

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = (int) $request->query('per_page', 15);
        $documents = $query->paginate($perPage);

        $resourceData = DocumentResource::collection($documents)->response()->getData(true);

        return response()->json([
            'message' => 'Документы успешно получены',
            'data'    => $resourceData['data'],
            'links'   => $resourceData['links'],
            'meta'    => $resourceData['meta'],
        ]);
    }

    public function show($id)
    {
        $document = \App\Models\Document::find($id);

        if (!$document) {
            return response()->json(['error' => 'Документ не найден'], 404);
        }

        if (!$document->path || !Storage::disk('public')->exists($document->path)) {
            return response()->json(['error' => 'Файл не найден'], 404);
        }

        return response()->file(storage_path('app/public/' . $document->path));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('docs', 'public');

        $document = Document::create([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'path' => $path,
        ]);

        return new DocumentResource($document);
    }
}
