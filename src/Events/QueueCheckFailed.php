<?php

declare(strict_types=1);

namespace F9Web\QueueCheck\Events;

class QueueCheckFailed
{
    /** @var string */
    public $output;

    /**
     * @param  string|null  $output
     */
    public function __construct(?string $output)
    {
        $this->output = $output;
    }

    /**
     * @return string|null
     */
    public function getOutput(): ?string
    {
        return $this->output ?? null;
    }
}
