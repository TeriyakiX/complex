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
    public $statusTranslated;

    /**
     * Создание нового экземпляра сообщения.
     *
     * @param  Callback  $callback
     * @return void
     */
    public function __construct(Callback $callback)
    {
        $this->callback = $callback;

        $statusTranslations = [
            'pending'   => 'В ожидании',
            'completed' => 'Завершено',
            'reject'    => 'Отклонено',
        ];

        $this->statusTranslated = $statusTranslations[$callback->status] ?? 'Не указан';
    }

    /**
     * Составить содержание письма.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Mail\Mailable
     */
    public function build()
    {
        return $this->subject('Новая заявка обратной связи')
            ->view('emails.callback')
            ->with([
                'name'  => $this->callback->name,
                'phone' => $this->callback->phone,
                'text'  => $this->callback->text,
                'agree' => $this->callback->agree ? 'Да' : 'Нет',
                'status' => $this->statusTranslated,
            ]);
    }
}
