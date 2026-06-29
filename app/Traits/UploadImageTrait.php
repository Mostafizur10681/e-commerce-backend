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
        $image = $manager->decode($file);

        // Resize / Crop
        $image->cover($width, $height);

        // Save
        $destination = Storage::disk('public')->path($path);
        $image->save($destination);

        return $path;
    }

    /**
     * Upload and crop/resize a base64 encoded image.
     */
     protected function uploadBase64Image(string $base64Data, string $folder, int $width = 600, int $height = 600): string
     {
         // In the new approach, we store the raw base64 string in the DB.
         // Optionally you could validate that it's a proper data URI.
         // Ensure the string has a prefix like data:image/...;base64,; if missing, add a generic one.
         if (!preg_match('/^data:image\/\w+;base64,/', $base64Data)) {
             // Assume JPEG if no prefix
             $base64Data = 'data:image/jpeg;base64,' . $base64Data;
         }
         return $base64Data;
     }
}
