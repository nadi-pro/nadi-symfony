# Nadi for Symfony

[![run-tests](https://github.com/nadi-pro/nadi-symfony/actions/workflows/run-tests.yml/badge.svg)](https://github.com/nadi-pro/nadi-symfony/actions/workflows/run-tests.yml)

Nadi monitoring SDK for Symfony applications. Monitor exceptions, slow queries, HTTP errors, and application performance in your Symfony projects.

## Requirements

- PHP 8.1+
- Symfony 6.4 / 7.x

## Installation

```bash
composer require nadi-pro/nadi-symfony
```

If you're using Symfony Flex, the bundle will be auto-registered. Otherwise, add it to your `config/bundles.php`:

```php
return [
    // ...
    Nadi\Symfony\NadiBundle::class => ['all' => true],
];
```

Run the install command:

```bash
bin/console nadi:install
```

## Configuration

Configure Nadi in `config/packages/nadi.yaml`:

```yaml
nadi:
    enabled: true
    driver: 'http' # log, http, opentelemetry

    connections:
        log:
            path: '%kernel.logs_dir%/nadi'
        http:
            api_key: '%env(NADI_API_KEY)%'
            app_key: '%env(NADI_APP_KEY)%'
            endpoint: 'https://nadi.pro/api'
            version: 'v1'
        opentelemetry:
            endpoint: 'http://localhost:4318'
            service_name: '%env(APP_NAME)%'
            service_version: '1.0.0'
            environment: '%kernel.environment%'

    query:
        slow_threshold: 500

    http:
        hidden_request_headers:
            - 'Authorization'
            - 'php-auth-pw'
        hidden_parameters:
            - 'password'
            - 'password_confirmation'
        ignored_status_codes:
            - '200-307'

    sampling:
        strategy: 'fixed_rate'
        config:
            sampling_rate: 0.1
```

Add your API keys to `.env`:

```
NADI_API_KEY=your-api-key
NADI_APP_KEY=your-app-key
```

## Features

- **Exception Monitoring**: Automatically captures unhandled exceptions via `kernel.exception` event
- **HTTP Monitoring**: Tracks HTTP requests and responses via `kernel.terminate` event
- **Database Monitoring**: Monitors slow SQL queries via Doctrine DBAL middleware
- **Console Monitoring**: Tracks console command execution via `console.terminate` event
- **OpenTelemetry**: Trace context propagation support

## Console Commands

```bash
# Install Nadi configuration and shipper
bin/console nadi:install

# Test the connection
bin/console nadi:test

# Verify configuration
bin/console nadi:verify

# Update shipper binary
bin/console nadi:update-shipper
```

## Doctrine Integration

For database query monitoring, the package automatically registers a Doctrine DBAL middleware. Ensure `doctrine/dbal` is installed:

```bash
composer require doctrine/dbal
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
