<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TestBroadcastNow implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    //tudo que for public vai para payload do canal.
    public $message;
    public $id_department;

    public function __construct($message, $id_department)
    {
        $this->message = $message;
        $this->id_department  = $id_department;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('department.'.$this->id_department);
    }

    public function broadcastAs()
    {
        return 'new.ticket';
    }
}