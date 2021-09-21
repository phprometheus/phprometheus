<?php

declare(strict_types=1);

namespace Phprometheus;

class PrometheusExporter extends AbstractPrometheus implements Prometheus
{
    public function flush(): array
    {
        return $this->registry->getMetricFamilySamples();
    }
}
