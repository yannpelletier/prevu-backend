<?php

namespace App\Jobs\Compilation;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Events\ModelCompilationStateChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

abstract class CompileFilePreview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $compiledModel;
    public $filters;
    public $source;
    public $destination;
    public $private;
    public $beginEvent;
    public $endEvent;
    public $failedEvent;

    /**
     * Create a new job instance.
     *
     * CompileFilePreview constructor.
     * @param Model $compiledModel
     * @param array $filters
     * @param string $source
     * @param string $destination
     * @param string $visibility
     * @param $beginEvent
     * @param $endEvent
     * @param $failedEvent
     */
    public function __construct(Model $compiledModel, array $filters, string $source, string $destination, bool $private, $beginEvent, $endEvent, $failedEvent)
    {
        $this->compiledModel = $compiledModel;
        $this->filters = $filters;
        $this->source = $source;
        $this->destination = $destination;
        $this->private = $private;
        $this->beginEvent = $beginEvent;
        $this->endEvent = $endEvent;
        $this->failedEvent = $failedEvent;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public final function handle()
    {
        $this->before();
        $this->compileFile();
        $this->after();
    }

    private function before()
    {
        event(new ModelCompilationStateChanged($this->compiledModel->user, $this->compiledModel, 'compiling'));
        if(isset($this->beginEvent)){
            event($this->beginEvent);
        }
    }

    private function after()
    {
        event(new ModelCompilationStateChanged($this->compiledModel->user, $this->compiledModel, 'compiled'));
        if(isset($this->endEvent)){
            event($this->endEvent);
        }
    }

    public function failed()
    {
        event(new ModelCompilationStateChanged($this->compiledModel->user, $this->compiledModel, 'failed'));
        if(isset($this->failedEvent)){
            event($this->failedEvent);
        }
    }

    abstract public function compileFile();
}
