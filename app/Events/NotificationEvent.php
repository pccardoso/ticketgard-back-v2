<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class NotificationEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public $data,
        public int $id_user
    )
    {}


    public function broadcastOn()
    {
        return new PrivateChannel('user.'.$this->id_user);
    }

    public function broadcastAs(){
        return 'new.notification';
    }
}
