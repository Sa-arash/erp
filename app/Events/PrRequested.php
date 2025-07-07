<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use pxlrbt\FilamentExcel\Exports\Formatters\ArrayFormatter;

class PrRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pr;

    public function __construct($pr)
    {

        $this->pr = $pr;
    }


    public function broadcastOn(): array
    {
        return ['pr.requested'];
    }

    public function broadcastAs(): string
    {
        return 'pr.requested';
    }
}
