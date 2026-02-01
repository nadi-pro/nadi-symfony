<?php

namespace Nadi\Symfony\Handler;

use Nadi\Data\Type;
use Nadi\Symfony\Actions\ExceptionContext;
use Nadi\Symfony\Data\ExceptionEntry;
use Nadi\Symfony\Support\OpenTelemetrySemanticConventions;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class HandleExceptionEvent extends Base
{
    public function handle(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        $trace = array_map(function ($item) {
            return array_intersect_key($item, array_flip(['file', 'line']));
        }, $exception->getTrace());

        $otelAttributes = OpenTelemetrySemanticConventions::exceptionAttributes($exception);
        $userAttributes = OpenTelemetrySemanticConventions::userAttributes();
        $sessionAttributes = OpenTelemetrySemanticConventions::sessionAttributes();
        $otelData = array_merge($otelAttributes, $userAttributes, $sessionAttributes);

        $httpAttributes = OpenTelemetrySemanticConventions::httpAttributesFromRequest($request);
        $otelData = array_merge($otelData, $httpAttributes);

        $entryData = [
            'class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'message' => $exception->getMessage(),
            'trace' => $trace,
            'line_preview' => ExceptionContext::get($exception),
            'otel' => $otelData,
        ];

        $this->store(
            ExceptionEntry::make(
                $exception,
                Type::EXCEPTION,
                $entryData
            )->setHashFamily(
                $this->hash(
                    get_class($exception).
                    $exception->getFile().
                    $exception->getLine().
                    $exception->getMessage().
                    date('Y-m-d')
                )
            )->tags([
                OpenTelemetrySemanticConventions::EXCEPTION_TYPE.':'.get_class($exception),
                OpenTelemetrySemanticConventions::ERROR_TYPE.':'.get_class($exception),
            ])->toArray()
        );
    }
}
