<?php

declare(strict_types=1);

interface Metric
{
    public function name(): string;

    public function description(): string;

    public function register(Exporter $exporter): void;
}
