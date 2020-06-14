<?php

declare(strict_types=1);

namespace F9Web\QueueCheck\Events;

class QueueCheckFailed
{
    /** @var string */
    public $processOutput;

    /**
     * @param  string|null  $processOutput
     */
    public function __construct(?string $processOutput)
    {
        $this->processOutput = $processOutput;
    }

    /**
     * @return string|null
     */
    public function getOutput(): ?string
    {
        return $this->processOutput ?? null;
    }
}
