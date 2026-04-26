<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Glide\GlideImage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageCropperController extends Controller
{
    public function processImage($path)
    {
        $cropsFolder = pathinfo($path, PATHINFO_DIRNAME);
        $folder = $cropsFolder !== '.' ? "/{$cropsFolder}" : '';
        $image = pathinfo($path, PATHINFO_BASENAME);
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $fname = substr($image, 0, strlen($image) - (strlen($ext) + 1));

        $params_encoded = (explode('__', strrev($fname)))[0];

        if (strlen($params_encoded) === strlen($fname)) {
            $params_encoded = '';
            $params = [];
            $originalFilename = $fname.'.'.$ext;
        } else {
            $params = hex2bin(strrev($params_encoded));
            if ($params === false) {
                throw new NotFoundHttpException;
            }
            parse_str($params, $params);
            $originalFilename = substr($fname, 0, strlen($fname) - (strlen($params_encoded) + 2)).'.'.$ext;
        }
        $tempFileName = match (config('twill.media_library.endpoint_type')) {
            'local' => $this->handleLocal($folder, $originalFilename),
            's3' => $this->handleS3($folder, $originalFilename),
            default => throw new NotFoundHttpException,
        };

        $folderToSave = storage_path('app/public/img/crops'.$folder);
        $pathToSave = $folderToSave.'/'.$image;

        if (! is_dir($folderToSave)) {
            $r = mkdir($folderToSave, 0777, true);
            if (! $r) {
                throw new Exception('Could not create folder');
            }
        }

        if (Str::endsWith($tempFileName, config('twill.glide.original_media_for_extensions'))) {
            return response(file_get_contents($tempFileName))->header('Content-type', mime_content_type($tempFileName));
        }

        GlideImage::create($tempFileName)
            ->modify($params)
            ->save($pathToSave);
        chmod($pathToSave, 0664);

        if (config('twill.media_library.endpoint_type') === 's3') {
            unlink($tempFileName);
        }

        // Respond with cached file
        return response()->file($pathToSave, [
            'Cache-Control' => 'max-age=31536000, public',
            'Expires' => now()->addYear()->toRfc7231String(),
        ]);
    }

    private function handleLocal($folder, $filename)
    {
        $path = config('twill.glide.source').'/'.$folder.'/'.$filename;
        if (! file_exists($path)) {
            throw new NotFoundHttpException("Originale non trovato: $path");
        }

        return $path;
    }

    private function handleS3($folder, $filename)
    {
        $s3Path = $folder.'/'.$filename;
        if (! Storage::disk('s3')->exists($s3Path)) {
            throw new NotFoundHttpException;
        }

        $tempFolder = sys_get_temp_dir().'/s3';
        if (! is_dir($tempFolder)) {
            mkdir($tempFolder, 0777, true);
        }

        $tempFile = tempnam($tempFolder, 's3_img_');
        file_put_contents($tempFile, Storage::disk('s3')->get($s3Path));

        return $tempFile;
    }
}
