<?php

namespace App\Jobs;

use App\Models\PushLog;
use App\Models\PushMessage;
use App\Models\UserPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class SendPushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;   // seconds between retries

    public function __construct(
        public readonly int $pushMessageId,
        public readonly int $userPreferenceId,
    ) {}

    public function handle(): void
    {
        $message = PushMessage::find($this->pushMessageId);
        $pref    = UserPreference::find($this->userPreferenceId);

        if (! $message || ! $pref || ! $pref->push_endpoint || ! $pref->push_keys) {
            return;
        }

        $auth = [
            'VAPID' => [
                'subject'    => config('app.url'),
                'publicKey'  => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ];

        $webPush = new WebPush($auth);

        $subscription = Subscription::create([
            'endpoint'       => $pref->push_endpoint,
            'keys'           => $pref->push_keys,
            'contentEncoding' => 'aesgcm',
        ]);

        $payload = json_encode([
            'title' => $message->title,
            'body'  => $message->body,
            'url'   => $message->url ?? '/',
            'icon'  => $message->icon ?? '/images/icons/android/android-launchericon-192-192.png',
            'badge' => '/images/icons/android/android-launchericon-96-96.png',
        ]);

        $webPush->queueNotification($subscription, $payload);

        foreach ($webPush->flush() as $report) {
            $status = 'sent';
            $error  = null;

            if (! $report->isSuccess()) {
                // 410 Gone = subscription is dead, clear it
                if ($report->getResponse()?->getStatusCode() === 410) {
                    $status = 'expired';
                    $pref->push_endpoint = null;
                    $pref->push_keys     = null;
                    $pref->save();
                } else {
                    $status = 'failed';
                    $error  = $report->getReason();
                }
            }

            PushLog::create([
                'push_message_id'    => $this->pushMessageId,
                'user_preference_id' => $this->userPreferenceId,
                'status'             => $status,
                'error'              => $error,
                'delivered_at'       => $status === 'sent' ? now() : null,
            ]);
        }
    }

    public function failed(\Throwable $e): void
    {
        PushLog::create([
            'push_message_id'    => $this->pushMessageId,
            'user_preference_id' => $this->userPreferenceId,
            'status'             => 'failed',
            'error'              => $e->getMessage(),
        ]);
    }
}