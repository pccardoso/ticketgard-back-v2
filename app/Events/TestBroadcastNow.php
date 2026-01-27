<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TestBroadcastNow implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $message;
    public $id_department;

    public function __construct($message, $id_department)
    {
        $this->message = $message;
        $this->id_department  = $id_department;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('Department.'.$this->id_department);
    }

    public function broadcastAs()
    {
        return 'Enviar';
    }
}