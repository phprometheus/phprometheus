<?php

declare(strict_types=1);

namespace Phprometheus;

use Prometheus\Collector;
use Prometheus\CollectorRegistry;

abstract class AbstractPrometheus implements Prometheus
{
    private $namespace;

    protected $collectorRegistry;

    public function __construct(string $namespace, CollectorRegistry $collectorRegistry)
    {
        $this->namespace = $namespace;
        $this->collectorRegistry = $$this->collectorRegistry;
    }

    public function incrementCounter(Counter $metric, float $amount = 1): Collector
    {
        $collector = $this->collectorRegistry->getOrRegisterCounter(
            $this->namespace,
            $metric::name(),
            $metric::help(),
            array_keys($metric->labels()),
        );

        $collector->incBy($amount, array_values($metric->labels()));

        return $collector;
    }

    public function incrementGauge(Gauge $metric, float $amount = 1): Collector
    {
        $collector = $this->collectorRegistry->getOrRegisterGauge(
            $this->namespace,
            $metric::name(),
            $metric::help(),
            array_keys($metric->labels()),
        );

        $collector->incBy($amount, array_values($metric->labels()));

        return $collector;
    }

    public function setGauge(Gauge $metric, float $value): Collector
    {
        $collector = $this->collectorRegistry->getOrRegisterGauge(
            $this->namespace,
            $metric::name(),
            $metric::help(),
            array_keys($metric->labels()),
        );

        $collector->set($value, array_values($metric->labels()));

        return $collector;
    }

    public function observeHistogram(Histogram $metric, float $value): Collector
    {
        $collector = $this->collectorRegistry->getOrRegisterHistogram(
            $this->namespace,
            $metric::name(),
            $metric::help(),
            array_keys($metric->labels()),
            $metric::buckets(),
        );

        $collector->observe($value, array_values($metric->labels()));

        return $collector;
    }
}
