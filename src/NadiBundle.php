<?php

namespace Nadi\Symfony;

use Nadi\Symfony\DependencyInjection\NadiExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class NadiBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new NadiExtension;
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
