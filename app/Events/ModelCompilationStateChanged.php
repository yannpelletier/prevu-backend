<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelCompilationStateChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $userId;
    private $compiledModel;
    private $newCompilationState;

    /**
     * Create a new event instance.
     *
     * @param $user
     * @param Model $compiledModel
     * @param string $newCompilationState
     */
    public function __construct($user, Model $compiledModel, string $newCompilationState)
    {
        $this->userId = $user->id;
        $this->compiledModel = $compiledModel;
        $this->newCompilationState = $newCompilationState;
    }

    public function getCompiledModel()
    {
        return $this->compiledModel;
    }

    public function getNewCompilationState()
    {
        return $this->newCompilationState;
    }


    /**
     * The event's broadcast name.
     *
     * @return string
     * @throws \ReflectionException
     */
    public function broadcastAs()
    {
        $reflectionClass = new \ReflectionClass($this->compiledModel);
        $modelName = strtolower($reflectionClass->getShortName());
        return $modelName . '.compilationStateChanged';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->compiledModel->id,
            'newCompilationState' => $this->newCompilationState,
        ];
    }

    /**
     * The event's broadcast channel
     *
     * @return string
     */
    public function broadcastOn()
    {
        return new PrivateChannel('users.' . $this->userId);
    }
}
