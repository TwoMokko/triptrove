<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadPhotoRequest;
use App\Services\PhotoUploadService;

class PhotoController extends Controller
{
    public function upload(UploadPhotoRequest $request)
    {
        try {
            $uploadService = new PhotoUploadService(
                file: $request->file('photo'),
                folder: $request->input('folder', 'photos'),
                subfolder: $request->input('subfolder'),
                disk: 'public' // или config('filesystems.default')
            );

            $result = $uploadService->upload();

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
