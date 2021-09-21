<?php

declare(strict_types=1);

use Phprometheus\AbstractPrometheus;
use Phprometheus\Counter;
use Phprometheus\Gauge;
use Phprometheus\Histogram;
use Phprometheus\PrometheusExporter;
use Prometheus\CollectorRegistry;
use Prometheus\MetricFamilySamples;
use Prometheus\Sample;
use Prometheus\Storage\InMemory;

describe(AbstractPrometheus::class, function () {
    given('registry', function () {
        return new CollectorRegistry(new InMemory(), false);
    });

    describe('counters', function () {
        given('counter', function () {
            return new class extends Counter {
                public static function name(): string
                {
                    return 'example_counter';
                }

                public static function help(): string
                {
                    return 'An example counter';
                }
            };
        });

        it('will increment a counter', function () {
            $prometheus = new PrometheusExporter('ns', $this->registry);
            $prometheus->incrementCounter($this->counter, 3);
            $output = $prometheus->flush();

            expect(count($output))->toBe(1);
            /** @var MetricFamilySamples $metric */
            foreach ($output as $metric) {
                expect($metric)->toBeAnInstanceOf(MetricFamilySamples::class);
                expect($metric->getName())->toBe('ns_example_counter');
                /** @var Sample $sample */
                $sample = $metric->getSamples()[0];
                expect((int) $sample->getValue())->toBe(3);
            }
        });
    });

    describe('gauges', function () {
        given('gauge', function () {
            return new class extends Gauge {
                public static function name(): string
                {
                    return 'example_gauge';
                }

                public static function help(): string
                {
                    return 'An example gauge';
                }
            };
        });

        it('will increment a gauge', function () {
            $prometheus = new PrometheusExporter('ns', $this->registry);
            $prometheus->incrementGauge($this->gauge, 3);
            $output = $prometheus->flush();

            expect(count($output))->toBe(1);
            /** @var MetricFamilySamples $metric */
            foreach ($output as $metric) {
                expect($metric)->toBeAnInstanceOf(MetricFamilySamples::class);
                expect($metric->getName())->toBe('ns_example_gauge');
                /** @var Sample $sample */
                $sample = $metric->getSamples()[0];
                expect((int) $sample->getValue())->toBe(3);
            }
        });

        it('will set a gauge', function () {
            $prometheus = new PrometheusExporter('ns', $this->registry);
            $prometheus->setGauge($this->gauge, 3);
            $output = $prometheus->flush();

            expect(count($output))->toBe(1);
            /** @var MetricFamilySamples $metric */
            foreach ($output as $metric) {
                expect($metric)->toBeAnInstanceOf(MetricFamilySamples::class);
                expect($metric->getName())->toBe('ns_example_gauge');
                /** @var Sample $sample */
                $sample = $metric->getSamples()[0];
                expect((int) $sample->getValue())->toBe(3);
            }
        });
    });

    describe('histograms', function () {
        given('histogram', function () {
            return new class extends Histogram {
                public static function name(): string
                {
                    return 'example_histogram';
                }

                public static function help(): string
                {
                    return 'An example histogram';
                }

                public static function buckets(): array
                {
                    return [0, 1, 2, 3, 5];
                }
            };
        });

        it('will record an histogram', function () {
            $prometheus = new PrometheusExporter('ns', $this->registry);
            $prometheus->observeHistogram($this->histogram, 2.5);
            $output = $prometheus->flush();

            expect(count($output))->toBe(1);
            /** @var MetricFamilySamples $metric */
            foreach ($output as $metric) {
                expect($metric)->toBeAnInstanceOf(MetricFamilySamples::class);
                expect($metric->getName())->toBe('ns_example_histogram');
                $samples = $metric->getSamples();

                /** @var Sample $sample */
                foreach ($samples as $sample) {
                    if ($sample->getName() === 'ns_example_histogram_bucket') {
                        foreach ($sample->getLabelValues() as $value) {
                            if ($value === '+Inf') {
                                expect((int) $sample->getValue())->toBe(1);
                                break;
                            }

                            if ((int) $value >= 3) {
                                expect((int) $sample->getValue())->toBe(1);
                                break;
                            }

                            expect((int) $sample->getValue())->toBe(0);
                        }
                    }

                    if ($sample->getName() === 'ns_example_histogram_sum') {
                        expect((float) $sample->getValue())->toBe(2.5);
                    }
                }
            }
        });
    });
});
