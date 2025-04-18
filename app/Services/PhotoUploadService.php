<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoUploadService
{
    public function __construct(
        private UploadedFile $file,
        private string $folder = 'photos',
        private ?string $subfolder = null,
        private string $disk = 'public'
    ) {}

    public function upload(): array
    {
        $this->validateFolder();
        $this->createDirectoryIfNeeded();

        $filename = $this->generateFilename();
        $path = $this->storeFile($filename);

        return [
            'path' => $path,
            'url' => Storage::disk($this->disk)->url($path),
            'filename' => $filename,
            'folder' => $this->folder,
            'full_path' => Storage::disk($this->disk)->path($path)
        ];
    }

    private function validateFolder(): void
    {
        $allowedFolders = ['users', 'travels']; // Убедитесь, что 'users' есть в списке

        if (!in_array($this->folder, $allowedFolders)) {
            throw new \InvalidArgumentException("Invalid upload folder specified");
        }
    }

    private function createDirectoryIfNeeded(): void
    {
        $fullPath = $this->getFullPath();

        if (!Storage::disk($this->disk)->exists($fullPath)) {
            Storage::disk($this->disk)->makeDirectory($fullPath);
        }
    }

    private function generateFilename(): string
    {
        return Str::random(20) . '_' . time() . '.' . $this->file->extension();
    }

    private function storeFile(string $filename): string
    {
        return $this->file->storeAs(
            $this->getFullPath(),
            $filename,
            $this->disk
        );
    }

    private function getFullPath(): string
    {
        return trim("{$this->folder}/{$this->subfolder}", '/');
    }

    public function uploadAndSaveToDB(
        Model $model,
        string $dbField,
        array $extraData = [],
        bool $deleteOld = true
    ): array {
        $uploadData = $this->upload();

        // Удаление старого файла
        if ($deleteOld && !empty($model->$dbField)) {
            $oldPath = $model->$dbField;
            if ($oldPath && Storage::disk($this->disk)->exists($oldPath)) {
                Storage::disk($this->disk)->delete($oldPath);
            }
        }

        $model->update([
            $dbField => $uploadData['path'],
            ...$extraData
        ]);

        return array_merge($uploadData, ['model' => $model->fresh()]);
    }
}
