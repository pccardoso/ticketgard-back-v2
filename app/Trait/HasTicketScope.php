<?php

namespace App\Trait;

use App\Models\Scopes\TicketScope;

trait HasTicketScope{

    protected static function booted(): void
    {
        static::addGlobalScope(new TicketScope);
    }

}