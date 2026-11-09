<?php

namespace App\Traits;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

trait UploadTrait
{
    /**
     * Upload and resize image, convert to WebP (Intervention Image v3)
     */
    public function uploadImage($file, $path, $width = null, $height = null)
    {
        if (!$file) return null;

        $filename = Str::uuid() . '.webp';
        $destinationPath = public_path('uploads/' . $path);

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        // Initialize manager with GD driver
        $manager = new ImageManager(new Driver());
        
        // Read image from file system
        $img = $manager->read($file->getRealPath());
        
        // Resize if needed
        if ($width || $height) {
            $img->scale(width: $width, height: $height);
        }

        // Encode to WebP and save
        $img->toWebp(80)->save($destinationPath . '/' . $filename);

        return 'uploads/' . $path . '/' . $filename;
    }

    /**
     * Upload and resize base64 image, convert to WebP (Intervention Image v3)
     */
    public function uploadBase64Image($base64Data, $path, $width = null, $height = null)
    {
        if (!$base64Data) return null;

        $filename = Str::uuid() . '.webp';
        $destinationPath = public_path('uploads/' . $path);

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        // Initialize manager with GD driver
        $manager = new ImageManager(new Driver());
        
        // Read image from base64 string
        $img = $manager->read($base64Data);
        
        // Resize if needed
        if ($width || $height) {
            $img->scale(width: $width, height: $height);
        }

        // Encode to WebP and save
        $img->toWebp(80)->save($destinationPath . '/' . $filename);

        return '/uploads/' . $path . '/' . $filename;
    }

    /**
     * Upload any file
     */
    public function uploadFile($file, $path)
    {
        if (!$file) return null;

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('uploads/' . $path);

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        $file->move($destinationPath, $filename);

        return 'uploads/' . $path . '/' . $filename;
    }

    /**
     * Delete file
     */
    public function deleteFile($path)
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}
