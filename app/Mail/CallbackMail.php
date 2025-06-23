<?php

namespace App\Mail;

use App\Models\Callback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CallbackMail extends Mailable
{
    use Queueable, SerializesModels;

    public $callback;

    public function __construct(Callback $callback)
    {
        $this->callback = $callback;
    }

    public function build()
    {
        return $this->subject('Новая заявка обратной связи')
            ->view('emails.callback');
    }
}
