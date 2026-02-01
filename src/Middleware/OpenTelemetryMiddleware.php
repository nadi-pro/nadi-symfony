<?php

namespace Nadi\Symfony\Middleware;

use Nadi\Symfony\Support\OpenTelemetrySemanticConventions;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class OpenTelemetryMiddleware implements EventSubscriberInterface
{
    private $span = null;

    private $scope = null;

    private string $driver;

    public function __construct(string $driver = 'log')
    {
        $this->driver = $driver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 255],
            KernelEvents::RESPONSE => ['onResponse', -255],
            KernelEvents::EXCEPTION => ['onException', -255],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if ($this->driver !== 'opentelemetry' || ! $event->isMainRequest()) {
            return;
        }

        try {
            $request = $event->getRequest();
            $carrier = [];
            foreach ($request->headers->all() as $name => $values) {
                $carrier[strtolower($name)] = $values[0] ?? '';
            }

            $context = TraceContextPropagator::getInstance()->extract($carrier);
            $spanName = $request->getMethod().' '.$request->getPathInfo();

            $tracer = \OpenTelemetry\API\Globals::tracerProvider()->getTracer('nadi-symfony');
            $this->span = $tracer->spanBuilder($spanName)
                ->setSpanKind(SpanKind::KIND_SERVER)
                ->setParent($context)
                ->startSpan();

            $this->scope = $this->span->activate();
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (! $this->span || ! $event->isMainRequest()) {
            return;
        }

        try {
            $statusCode = $event->getResponse()->getStatusCode();
            $this->span->setAttribute(OpenTelemetrySemanticConventions::HTTP_STATUS_CODE, $statusCode);

            if ($statusCode >= 400) {
                $this->span->setStatus(StatusCode::STATUS_ERROR, "HTTP {$statusCode}");
            } else {
                $this->span->setStatus(StatusCode::STATUS_OK);
            }

            $carrier = [];
            TraceContextPropagator::getInstance()->inject($carrier, null, Context::getCurrent());
            foreach ($carrier as $name => $value) {
                $event->getResponse()->headers->set($name, $value);
            }

            $this->span->end();
            $this->scope?->detach();
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }

    public function onException(ExceptionEvent $event): void
    {
        if (! $this->span) {
            return;
        }

        try {
            $this->span->recordException($event->getThrowable());
            $this->span->setStatus(StatusCode::STATUS_ERROR, $event->getThrowable()->getMessage());
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }
}
