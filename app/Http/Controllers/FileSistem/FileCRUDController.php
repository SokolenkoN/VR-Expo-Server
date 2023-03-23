<?php

namespace App\Http\Controllers\FileSistem;

use App\Http\Controllers\Controller;
use App\Http\Requests\File\StoreRequest;
use App\Http\Requests\File\UpdateRequest;
use App\Http\Resources\File\FileResource;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;

class FileCRUDController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = strtolower($request->input('query'));

        $files = Storage::allFiles('files');
        $result = [];


        foreach ($files as $file) {
            if (str_ends_with($file, '.meta')) {
                if (Storage::exists($file)) {
                    $metaArray = json_decode(Storage::get($file), true);
                    if (strpos(strtolower($metaArray['name']), $query) !== false || strpos(strtolower($metaArray['description']), $query) !== false) {
                        $result[] = new FileResource(new Fluent($metaArray));
                    }
                }
            }
        }
        if (count($result) == 0) {
            return FileResource::collection($result);
        }
        return FileResource::collection($result);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreRequest $request
     * @return FileResource|JsonResponse
     */
    public function store(StoreRequest $request)
    {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $uniqueName = md5(microtime(true)) . '.' . $extension;

            $path = Storage::putFileAs('files', $file, $uniqueName);
            $meta = [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'size' => $file->getSize(),
                'link' => asset('storage/' . $path),
                'microtime_name' => $uniqueName,
                'created_at' => $meta['created_at'] ?? (new DateTime)->format('d-m-Y H:i:s'),
            ];

            $metaFileName = $uniqueName . '.meta';
            Storage::put('files/' . $metaFileName, json_encode($meta, JSON_THROW_ON_ERROR));

            return new FileResource(new Fluent($meta));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $name)
    {
        if (Storage::exists('files/' . $name)) {
            $metaFileName = $name . '.meta';
            $metaData = Storage::get('files/' . $metaFileName);
            $decode = json_decode($metaData, true);
            $decode['link'] = asset('storage/files/' . $name);
            return new FileResource(new Fluent($decode));
        }
        return response()->json(['message' => 'Файл не найден.'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $name)
    {
        $metaFileName = $name . '.meta';
        $metaData = Storage::get('files/' . $metaFileName);

        if (Storage::exists('files/' . $name)) {
            $metaData = json_decode($metaData, true);
            $metaData['name'] = $request->input('name', $metaData['name'] ?? '');
            $metaData['description'] = $request->input('description', $metaData['description'] ?? '');
            $metaData['updated_at'] = (new DateTime)->format('d-m-Y H:i:s');
            Storage::put('files/' . $metaFileName, json_encode($metaData));

            return new FileResource(new Fluent($metaData));
        }
        return response()->json(['message' => 'Файл не найден.'], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $name)
    {
        if (Storage::exists('files/' . $name)) {
            $metaFileName = 'files/' . $name . '.meta';
            Storage::delete(['files/' . $name, $metaFileName]);
            return response()->json(['message' => 'Файл успешно удалён.'], 200);
        }
        return response()->json(['message' => 'Файл не найден.'], 404);
    }
}
