<?php

namespace Nadi\Symfony\Tests\Feature;

use Nadi\Symfony\EventSubscriber\NadiEventSubscriber;
use Nadi\Symfony\Handler\HandleCommandEvent;
use Nadi\Symfony\Handler\HandleExceptionEvent;
use Nadi\Symfony\Handler\HandleHttpRequestEvent;
use Nadi\Symfony\Nadi;
use Nadi\Symfony\Tests\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class EventSubscriberTest extends TestCase
{
    public function test_subscriber_returns_subscribed_events(): void
    {
        $events = NadiEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::EXCEPTION, $events);
        $this->assertArrayHasKey(KernelEvents::TERMINATE, $events);
    }

    public function test_subscriber_can_be_instantiated(): void
    {
        $config = $this->getNadiConfig();
        $nadi = new Nadi($config);

        $subscriber = new NadiEventSubscriber(
            exceptionHandler: new HandleExceptionEvent($nadi),
            httpRequestHandler: new HandleHttpRequestEvent($nadi, $config),
            commandHandler: new HandleCommandEvent($nadi),
            enabled: true,
        );

        $this->assertInstanceOf(NadiEventSubscriber::class, $subscriber);
    }
}
