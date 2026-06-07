<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Mail\SubscriptionConfirmation;
use App\Mail\SubscriptionReceived;
use App\Repositories\SubscriptionRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionRepository $subscriptions,
    ) {
    }

    public function store(StoreSubscriptionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $upload = $request->file('video_file_path');
        unset($data['video_file_path']);

        $eventId = (int) $data['evento'];
        unset($data['evento']);
        $data['browsers'] = ['event' => [['id' => $eventId]]];

        $data['title'] = $data['band'];
        $data['privacy'] = (bool) ($data['privacy'] ?? false);
        $data['data_iscrizione'] = now();
        $data['published'] = true;

        $subscription = $this->subscriptions->create($data);

        if ($upload) {
            $this->subscriptions->attachVideoFile($subscription, $upload);
        }

        // Send notification email to configured receiver
        try {
            $receiver = env('MAIL_NOTIFICATION_RECEIVER', config('mail.from.address'));
            Mail::to($receiver)->send(new SubscriptionReceived($subscription));
        } catch (\Throwable $e) {
            // Don't block the user; log or ignore
            report($e);
        }

        // Send confirmation email to subscriber
        try {
            Mail::to($subscription->email)->send(new SubscriptionConfirmation($subscription));
        } catch (\Throwable $e) {
            // Don't block the user; log or ignore
            report($e);
        }

        return back()->with('success', 'Iscrizione inviata con successo.');
    }
}
