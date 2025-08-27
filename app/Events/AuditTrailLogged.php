<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuditTrailLogged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $action;
    public $description;
    public $modelType;
    public $modelId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($userId, $action, $description, $modelType, $modelId)
    {
        $this->userId = $userId;
        $this->action = $action;
        $this->description = $description;
        $this->modelType = $modelType;
        $this->modelId = $modelId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}