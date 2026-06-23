<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

trait UploadImageTrait
{
    /**
     * Upload and crop/resize an image.
     */
    protected function uploadImage(UploadedFile $file, string $folder, int $width = 600, int $height = 600): string
    {
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $fileName;

        // Ensure directory exists
        Storage::disk('public')->makeDirectory($folder);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);

        // Resize / Crop
        $image->cover($width, $height);

        // Save
        $destination = Storage::disk('public')->path($path);
        $image->save($destination);

        return $path;
    }
}
