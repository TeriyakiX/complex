<?php

namespace App\Http\Controllers;

use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $certificates = Certificate::paginate($perPage);

        $resourceData = CertificateResource::collection($certificates)->response()->getData(true);

        return response()->json([
            'message' => 'Список сертификатов',
            'data'    => $resourceData['data'],
            'links'   => $resourceData['links'],
            'meta'    => $resourceData['meta'],
        ]);
    }

    public function show(Certificate $certificate)
    {
        return response()->json([
            'message' => 'Информация о сертификате',
            'data'    => new CertificateResource($certificate),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $path = $request->file('image')->store('certificates', 'public');

        $certificate = Certificate::create([
            'name'  => $request->name,
            'image' => $path,
        ]);

        return response()->json([
            'message' => 'Сертификат успешно загружен',
            'data'    => new CertificateResource($certificate),
        ], 201);
    }
}
