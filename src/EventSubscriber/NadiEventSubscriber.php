<?php

namespace Nadi\Symfony\EventSubscriber;

use Nadi\Symfony\Handler\HandleCommandEvent;
use Nadi\Symfony\Handler\HandleExceptionEvent;
use Nadi\Symfony\Handler\HandleHttpRequestEvent;
use Nadi\Symfony\Nadi;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class NadiEventSubscriber implements EventSubscriberInterface
{
    private bool $enabled;

    private array $config;

    public function __construct(
        private Nadi $nadi,
        array $config = []
    ) {
        $this->enabled = $config['enabled'] ?? true;
        $this->config = $config;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
            KernelEvents::TERMINATE => ['onKernelTerminate', 0],
            ConsoleEvents::TERMINATE => ['onConsoleTerminate', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (! $this->enabled) {
            return;
        }

        try {
            $handler = new HandleExceptionEvent($this->nadi);
            $handler->handle($event);
        } catch (\Throwable $e) {
            // Silently ignore monitoring errors
        }
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        if (! $this->enabled) {
            return;
        }

        try {
            $handler = new HandleHttpRequestEvent($this->nadi);
            $handler->setConfig($this->config);
            $handler->handle($event);
        } catch (\Throwable $e) {
            // Silently ignore monitoring errors
        }
    }

    public function onConsoleTerminate(\Symfony\Component\Console\Event\ConsoleTerminateEvent $event): void
    {
        if (! $this->enabled) {
            return;
        }

        try {
            $handler = new HandleCommandEvent($this->nadi);
            $handler->handle($event);
        } catch (\Throwable $e) {
            // Silently ignore monitoring errors
        }
    }
}
