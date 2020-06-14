<?php

declare(strict_types=1);

namespace F9Web\QueueCheck\Console\Commands;

use F9Web\QueueCheck\Events\QueueCheckFailed;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

use function event;
use function explode;

class CheckQueueIsRunning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'f9web:check-queue-is-running';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crudely, check if the Redis queue worker is running';

    /** @var string */
    protected $output = '';

    /** @var int */
    protected $processCount = 0;

    /** @var null */
    protected $process = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = config('f9web-queue-check');

        // the target process that should be running
        $expectedOutput = $options['expected-output'];

        // the number of expected processes
        $expectedProcessCount = $options['processes'] ?? 1;

        $process = $this->getProcess();
        $process->run();

        $this->output = $process->getOutput();

        foreach (explode(PHP_EOL, $this->output) as $line) {
            if (Str::contains($line, $expectedOutput)) {
                $this->processCount++;
            }
        }

        // If the process count is zero the target process is not running.
        // The process count differing to the expected count could
        // indicate issues too.
        if ($this->processCount < $expectedProcessCount) {
            event(new QueueCheckFailed($this->output ?? ''));
        }
    }

    /**
     * @return \Symfony\Component\Process\Process|mixed
     */
    private function getProcess()
    {
        if (null === $this->process) {
            return new Process(['ps', 'aux']);
        }

        return $this->process;
    }

    /**
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * @param  null  $process
     */
    public function setProcess($process): void
    {
        $this->process = $process;
    }

    /**
     * @return int
     */
    public function getProcessCount(): int
    {
        return $this->processCount;
    }
}
