<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;

class SetModelCompilationState
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $compiledModel = $event->getCompiledModel();
        $newCompilationState = $event->getNewCompilationState();

        $compiledModel->compilation_state = $newCompilationState;
        $compiledModel->save();
    }
}
