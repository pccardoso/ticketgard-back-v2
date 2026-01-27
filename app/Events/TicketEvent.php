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

    public function __construct(
        public int $id_ticket,
        public array $data
    ){}

    public function broadcastOn()
    {
        return new PrivateChannel('Ticket.'.$this->id_ticket);
    }

    public function broadcastAs()
    {
        return 'Enviar';
    }
}