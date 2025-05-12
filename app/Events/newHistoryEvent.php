<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class newHistoryEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $history;
    public $deviceId;

    /**
     * Create a new event instance.
     * 
     * @param mixed $history The history data
     * @param string|null $deviceId The device ID for channel-specific broadcasting
     */
    public function __construct($history, $deviceId = null)
    {
        $this->history = $history;
        $this->deviceId = $deviceId;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'history' => $this->history,
        ];
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('history'), // Main history channel for all devices
        ];

        // Add a device-specific channel if a deviceId exists
        if ($this->deviceId) {
            $channels[] = new Channel('device.' . $this->deviceId);
        }

        return $channels;
    }
}