<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Repositories\SubscriptionRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionRepository $subscriptions,
    ) {}

    public function store(StoreSubscriptionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('video_file_path')) {
            $data['video_file_path'] = Storage::disk('public')
                ->putFile('subscriptions', $request->file('video_file_path'));
        } else {
            $data['video_file_path'] = null;
        }

        $data['privacy'] = (bool) ($data['privacy'] ?? false);
        $data['data_iscrizione'] = now();
        $data['published'] = true;

        $this->subscriptions->create($data);

        return back()->with('success', 'Iscrizione inviata con successo.');
    }
}
