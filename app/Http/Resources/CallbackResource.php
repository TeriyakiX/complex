<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $statusTranslations = [
            'pending'   => 'В ожидании',
            'completed' => 'Завершено',
            'reject'    => 'Отклонено',
        ];


        $status = $this->status ?? 'pending';

        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'phone'      => $this->phone,
            'text'       => $this->text,
            'agree'      => $this->agree,
            'status'     => $statusTranslations[$status] ?? 'Не указан',
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
