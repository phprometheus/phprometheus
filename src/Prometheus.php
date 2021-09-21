<?php

declare(strict_types=1);

namespace Phprometheus;

use Prometheus\Collector;

interface Prometheus
{
    public function incrementCounter(Counter $metric, float $amount = 1): void;

    public function incrementGauge(Gauge $metric, float $amount = 1): void;

    public function setGauge(Gauge $metric, float $value): void;

    public function observeHistogram(Histogram $metric, float $value): void;

    public function flush(): array;
}
