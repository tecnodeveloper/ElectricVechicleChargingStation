<?php

namespace App\Events;

use App\Models\Station;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $station;

    /**
     * Create a new event instance.
     */
    public function __construct(Station $station)
    {
        $this->station = $station;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('stations-channel'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'station.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->station->id,
            'name' => $this->station->name,
            'address' => $this->station->address,
            'latitude' => $this->station->latitude,
            'longitude' => $this->station->longitude,
            'price_per_hour' => $this->station->price_per_hour,
            'status' => $this->station->status,
            'created_at' => $this->station->created_at->toISOString(),
        ];
    }
}
