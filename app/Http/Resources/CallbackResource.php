<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallbackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'phone' => $this->phone,
            'text'  => $this->text,
            'agree' => $this->agree,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
