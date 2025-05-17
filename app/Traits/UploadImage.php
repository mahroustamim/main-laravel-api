<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait UploadImage
{
    public function uploadImage($file, $folder)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $file->move(public_path("images/$folder"), $filename);
        return $filename;
    }

    public function deleteImage($filename, $folder)
    {
        $path = public_path("images/$folder/" . $filename);
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
