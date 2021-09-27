<?php

declare(strict_types=1);

namespace Phprometheus;

use Prometheus\RegistryInterface;

abstract class AbstractPrometheus implements Prometheus
{
    private $namespace;

    protected $registry;

    public function __construct(string $namespace, RegistryInterface $registry)
    {
        $this->namespace = $namespace;
        $this->registry = $registry;
    }

    public function incrementCounter(Counter $metric, float $amount = 1): void
    {
        $collector = $this->registry->getOrRegisterCounter(
            $this->namespace,
            $metric::name(),
            $metric::help(),
            array_keys($metric->labels()),
        );

        $collector->incBy($amount, array_values($metric->labels()));
    }

    public function incrementGauge(Gauge $metric, float $amount = 1): void
    {
        $collector = $this->registry->getOrRegisterGauge(
            $this->namespace,
            $metric::name(),
            $metric::help(),
            array_keys($metric->labels()),
        );

        $collector->incBy($amount, array_values($metric->labels()));
    }

    public function setGauge(Gauge $metric, float $value): void
    {
        $collector = $this->registry->getOrRegisterGauge(
            $this->namespace,
            $metric::name(),
            $metric::help(),
            array_keys($metric->labels()),
        );

        $collector->set($value, array_values($metric->labels()));
    }

    public function observeHistogram(Histogram $metric, float $value): void
    {
        $collector = $this->registry->getOrRegisterHistogram(
            $this->namespace,
            $metric::name(),
            $metric::help(),
            array_keys($metric->labels()),
            $metric::buckets(),
        );

        $collector->observe($value, array_values($metric->labels()));
    }
}
