<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public $data,
        public int $id_ticket
    )
    {}


    public function broadcastOn()
    {
        return new PrivateChannel('ticket.'.$this->id_ticket);
    }

    public function broadcastAs(){
        return 'new.message';
    }
}
