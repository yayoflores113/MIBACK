<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensaje;
    public $idDestinatario;

    public function __construct($mensaje, $idDestinatario)
    {
        $this->mensaje = $mensaje;
        $this->idDestinatario = $idDestinatario;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notificaciones.' . $this->idDestinatario);
    }

    public function broadcastWith()
    {
        return [
            'mensaje' => $this->mensaje,
        ];
    }
}
