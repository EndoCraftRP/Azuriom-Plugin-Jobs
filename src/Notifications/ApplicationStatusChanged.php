<?php

namespace Azuriom\Plugin\Jobs\Notifications;

use Azuriom\Plugin\Jobs\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusChanged extends Notification
{
    use Queueable;

    public function __construct(private readonly Application $application)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->application->status;
        $subject = match ($status) {
            'reviewing' => trans('jobs::messages.mail_reviewing_subject'),
            'accepted' => trans('jobs::messages.mail_accepted_subject'),
            'refused' => trans('jobs::messages.mail_refused_subject'),
            default => trans('jobs::messages.mail_pending_subject'),
        };

        $line = match ($status) {
            'reviewing' => trans('jobs::messages.mail_reviewing_body'),
            'accepted' => trans('jobs::messages.mail_accepted_body'),
            'refused' => trans('jobs::messages.mail_refused_body'),
            default => trans('jobs::messages.status_pending'),
        };

        return (new MailMessage())
            ->subject($subject)
            ->line($line)
            ->action(trans('jobs::messages.view_status'), route('jobs.status', $this->application));
    }
}
