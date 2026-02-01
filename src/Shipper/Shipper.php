<?php

namespace Nadi\Symfony\Shipper;

use Nadi\Shipper\BinaryManager;

class Shipper
{
    private BinaryManager $binaryManager;

    public function __construct(
        private string $projectDir,
    ) {
        $this->binaryManager = new BinaryManager(
            $this->projectDir . '/vendor/bin',
        );
    }

    public function install(): void
    {
        $this->binaryManager->install();
    }

    public function isInstalled(): bool
    {
        return $this->binaryManager->isInstalled();
    }

    public function send(string $configPath): array
    {
        return $this->binaryManager->execute([
            'send',
            '--config',
            $configPath,
        ]);
    }

    public function test(string $configPath): array
    {
        return $this->binaryManager->execute([
            'test',
            '--config',
            $configPath,
        ]);
    }

    public function verify(string $configPath): array
    {
        return $this->binaryManager->execute([
            'verify',
            '--config',
            $configPath,
        ]);
    }

    public function getBinaryManager(): BinaryManager
    {
        return $this->binaryManager;
    }
}
