<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationService
{
    protected $messaging;

    public function __construct()
    {
        // Busca a instância do Firebase Messaging
        $this->messaging = app('firebase.messaging');
    }

    public function sendToToken(string $token, string $title, string $body, string $codTicket)
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body))
            ->withData(['url' => "/open-chat/".$codTicket]);

        return $this->messaging->send($message);
    }
}
