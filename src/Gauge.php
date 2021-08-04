<?php

declare(strict_types=1);

namespace Phprometheus;

abstract class Gauge implements Metric
{
    protected $labels;

    public function __construct(array $labels = [])
    {
        $this->labels = $labels;
    }

    public function labels(): array
    {
        return $this->labels;
    }
}
