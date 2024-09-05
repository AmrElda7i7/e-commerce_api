<?php

namespace App\Notifications;

use Ichtrojan\Otp\Otp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class ResetPasswordNotification extends Notification
{
    use Queueable;
    public string $subject ;
    public string $message ;
    public  $otp ;
    public string $mailer ;


    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->message= "use below code for password reset verification";
        $this->subject= "password reset";
        $this->mailer= "smtp";
        $this->otp= new Otp();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     * @throws \Exception
     */
    public function toMail(object $notifiable): MailMessage
    {
        $otp = $this->otp->generate($notifiable->email,"numeric",6,60);
        return (new MailMessage)
            ->mailer($this->mailer)
            ->subject($this->subject)
            ->greeting("Hello {$notifiable->first_name},")
            ->line($this->message)
            ->line("code:".$otp->token);

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
