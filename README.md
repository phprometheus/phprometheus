# PHPrometheus

> A lightweight, modular, object-oriented library for exporting metrics to Prometheus.

This is the main package for PHPrometheus. This package aims to provide a higher-level interface over the [officially-sanctioned Prometheus package](https://github.com/PromPHP/prometheus_client_php), at the cost of being slightly more opinionated.

Headline features:

- Lightweight by default, additional functionality or support for specific frameworks pulled in with optional extra packages.
- Object-oriented for sensible typing, and to match the ergonomics of your application.
- Metrics are registered on the fly as they are used - no need to maintain a separate list of metrics and their labels elsewhere in your application.

## Installation

Requires PHP 7.3+.

```sh
composer require phprometheus/phprometheus
```

### Modules

PHPrometheus is highly modular. This means that you might find other functionality to plug in, but you don't need to load the kitchen sink if you only need the basics.

- [phprometheus/phprometheus-push-gateway](https://github.com/phprometheus/phprometheus-push-gateway) [wip] - For sending metrics to a configured [Prometheus Push Gateway](https://prometheus.io/docs/practices/pushing/) so you don't need to expose a metrics endpoint. Great for queue workers, for example.
- [phprometheus/phprometheus-laravel](https://github.com/phprometheus/phprometheus-laravel) [wip] - For easy integration within a Laravel application. Includes a pre-configured service provider, `/metrics` endpoint, and route middleware to automatically instrument your HTTP endpoints.
- phprometheus/phprometheus-guzzle-middleware [coming soon!] - automatically instrument your calls to external services.
- phprometheus/phprometheus-psr15-middleware [coming soon!] - automatically instrument your HTTP endpoints with this handy middleware.
- phprometheus/phprometheus-container [coming soon!] - pre-configured PHPrometheus IoC implementation in a PSR-11 container.
- phprometheus/phprometheus-bundle [coming, perhaps] - Symfony support.

## Usage

Each of your metrics is a class that subclasses a Prometheus metric type.

```php
class HappyCustomer extends \Phprometheus\Counter
{
    public static function name(): string
    {
        return 'happy_customers_total';
    }

    public static function help(): string
    {
        return 'Counter of customers who like our app.';
    } 
}
```

Then, elsewhere in your application:

```php
/** In your service container */
$prometheus = new \Phprometheus\PrometheusExporter(
    'my_namespace',
    new \Prometheus\CollectorRegistry(
        new \Prometheus\Storage\InMemory()
    )
);

/** Then, you can inject and use it in your domain logic: */
$this->prometheus->incrementCounter(new HappyCustomer([
    'custom_label' => 'yes'
]));
```

Finally, you can export the metrics on a `/metrics` endpoint, as is the norm for Prometheus:

```php
/** In a controller or similar: */
$metrics = $this->prometheus->flush();
$renderer = new RenderTextFormat();

/** Assuming Symfony HttpFoundation here, but use what you prefer */
return new Response(
    $renderer->render($metrics),
    Response::HTTP_OK,
    [ 'Content-Type' => RenderTextFormat::MIME_TYPE ]
);
```

### Metric types

You may extend any of the following classes when creating your metrics:

- `Phprometheus\Counter::class`
- `Phprometheus\Gauge::class`
- `Phprometheus\Histogram::class`

All the above implement a common interface, `Phprometheus\Metric::class`.

Extending any of these metric types requires filling out a metric name and help string. In addition, the Histogram allows overriding its default buckets, if you desire:

```php
class RequestDuration extends \Phprometheus\Histogram
{
    public static function name(): string
    {
        return 'request_duration_seconds';
    }

    public static function help(): string
    {
        return 'How long it takes to service requests.';
    }
    
    public static function buckets(): array
    {
        return [ 0.2, 0.3, 0.5, 0.8, 1.3 ];
    }
}
```

### Storage adapters

When creating an instance of PHPrometheus, you must pass a storage adapter to it. This is because of PHP's request model, to allow persisting of metrics across requests.

```php
$prometheus = new \Phprometheus\PrometheusExporter(
    'my_namespace',
    new \Prometheus\CollectorRegistry(
        new \Prometheus\Storage\InMemory()
    )
);
```

Note that in most cases (unless using the [Push Gateway](https://github.com/phprometheus/phprometheus-push-gateway)) you won't want to use the InMemory adapter, as metrics are not persisted anywhere. The following additional storage adapters are available:

- `Prometheus\Storage\APC::class`
- `Prometheus\Storage\Redis::class`

Some more info on this is available in the [PromPHP](https://github.com/PromPHP/prometheus_client_php) repository that this is built on.

### Labels

Each Metric's constructor accepts an optional array of labels. These are simple key/value pairs that correspond to [Prometheus labels](https://prometheus.io/docs/concepts/data_model/).

For example, the following:

```php
$metric = new HappyCustomer([ 'star_rating' => 5, 'has_paid_us_money' => true ]);

$this->prometheus->observeHistogram($metric, 2.5);
```

Would result in the following output:

```
# HELP happy_customers Counter of customers who like our app.
# TYPE happy_customers counter
happy_customers{star_rating="5", has_paid_us_money="true"} 123
```