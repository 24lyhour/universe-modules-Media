<?php

namespace Modules\Media\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function __construct(
        private MediaService $mediaService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [];

        if ($type = $request->query('type')) {
            $mimeTypes = [
                'image' => 'image/',
                'video' => 'video/',
                'audio' => 'audio/',
                'document' => 'application/',
            ];
            if (isset($mimeTypes[$type])) {
                $filters['mime_type'] = $mimeTypes[$type];
            }
        }

        if ($collection = $request->query('collection')) {
            $filters['collection_name'] = $collection;
        }

        $media = $this->mediaService->all($filters);

        $data = $media->map(function (Media $item) {
            return [
                'id' => $item->id,
                'uuid' => $item->uuid,
                'name' => $item->name,
                'file_name' => $item->file_name,
                'url' => $item->getUrl(),
                'thumb_url' => $item->hasGeneratedConversion('thumb')
                    ? $item->getUrl('thumb')
                    : $item->getUrl(),
                'mime_type' => $item->mime_type,
                'size' => $item->size,
                'size_formatted' => $this->mediaService->formatBytes($item->size),
                'collection_name' => $item->collection_name,
                'created_at' => $item->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
            ],
        ]);
    }

    /**
     * Upload a new media file.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB max
            'collection' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');
        $collection = $request->input('collection', 'default');

        // Create a temporary model to attach media
        // In a real application, you might want to use a dedicated MediaFile model
        $tempMedia = \App\Models\User::find(auth()->id());

        if (!$tempMedia) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        try {
            $media = $tempMedia->addMedia($file)
                ->toMediaCollection($collection);

            return response()->json([
                'data' => [
                    'id' => $media->id,
                    'uuid' => $media->uuid,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'thumb_url' => $media->hasGeneratedConversion('thumb')
                        ? $media->getUrl('thumb')
                        : $media->getUrl(),
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'size_formatted' => $this->mediaService->formatBytes($media->size),
                    'collection_name' => $media->collection_name,
                    'created_at' => $media->created_at->toIso8601String(),
                ],
                'message' => 'File uploaded successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $media = $this->mediaService->find($id);

        if (!$media) {
            return response()->json([
                'message' => 'Media not found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $media->id,
                'uuid' => $media->uuid,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'url' => $media->getUrl(),
                'thumb_url' => $media->hasGeneratedConversion('thumb')
                    ? $media->getUrl('thumb')
                    : $media->getUrl(),
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'size_formatted' => $this->mediaService->formatBytes($media->size),
                'collection_name' => $media->collection_name,
                'custom_properties' => $media->custom_properties,
                'created_at' => $media->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->mediaService->delete($id);

        if (!$deleted) {
            return response()->json([
                'message' => 'Media not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Media deleted successfully',
        ]);
    }
}
