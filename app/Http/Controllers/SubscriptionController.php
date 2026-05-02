<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Repositories\SubscriptionRepository;
use Illuminate\Http\RedirectResponse;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionRepository $subscriptions,
    ) {}

    public function store(StoreSubscriptionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $upload = $request->file('video_file_path');
        unset($data['video_file_path']);

        $data['title'] = $data['band'];
        $data['privacy'] = (bool) ($data['privacy'] ?? false);
        $data['data_iscrizione'] = now();
        $data['published'] = true;

        $subscription = $this->subscriptions->create($data);

        if ($upload) {
            $this->subscriptions->attachVideoFile($subscription, $upload);
        }

        return back()->with('success', 'Iscrizione inviata con successo.');
    }
}
