<?php

declare(strict_types=1);

namespace Phprometheus;

interface Metric
{
    public static function name(): string;

    public static function help(): string;

    public function labels(): array;
}
