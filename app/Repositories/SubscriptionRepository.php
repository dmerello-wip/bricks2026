<?php

namespace App\Repositories;

use A17\Twill\Models\File as TwillFile;
use A17\Twill\Repositories\Behaviors\HandleFiles;
use A17\Twill\Repositories\ModuleRepository;
use App\Models\Subscription;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SubscriptionRepository extends ModuleRepository
{
    use HandleFiles;

    public function __construct(Subscription $model)
    {
        $this->model = $model;
    }

    public function attachVideoFile(Subscription $subscription, UploadedFile $upload): void
    {
        $disk = config('twill.file_library.disk');
        $folder = (string) Str::uuid();
        $cleanName = Str::slug(pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME))
            .'.'.$upload->getClientOriginalExtension();

        $path = $upload->storeAs($folder, $cleanName, $disk);

        $file = TwillFile::create([
            'uuid' => $path,
            'filename' => $upload->getClientOriginalName(),
            'size' => $upload->getSize(),
        ]);

        $subscription->files()->attach($file->id, [
            'role' => 'video_file',
            'locale' => app()->getLocale(),
            'position' => 1,
        ]);
    }
}
