<?php

namespace App\Services;

use App\Models\Callback;

class CallbackStatusService
{
    public function updateStatus(Callback $callback, string $status)
    {
        $statusActions = [
            'reject' => 'rejectCallback',
            'completed' => 'completeCallback',
        ];

        if (!array_key_exists($status, $statusActions)) {
            return ['message' => 'Неверный статус', 'status' => 400];
        }

        return $this->{$statusActions[$status]}($callback);
    }

    private function rejectCallback(Callback $callback)
    {
        $callback->delete();
        return [
            'message' => 'Заявка отклонена и удалена',
            'status' => 200,
        ];
    }

    private function completeCallback(Callback $callback)
    {

        $callback->status = 'completed';
        $callback->save();

        return [
            'message' => 'Заявка завершена',
            'status' => 200,
            'data' => $callback,
        ];
    }
}
