<?php

namespace Nadi\Symfony\Command;

use Nadi\Symfony\Nadi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'nadi:verify',
    description: 'Verify the Nadi monitoring configuration',
)]
class VerifyCommand extends Command
{
    public function __construct(
        private Nadi $nadi,
        private array $nadiConfig,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Verifying Nadi Configuration');

        $errors = [];
        $warnings = [];

        // Check if enabled
        $enabled = $this->nadiConfig['enabled'] ?? false;
        if (! $enabled) {
            $warnings[] = 'Nadi monitoring is currently disabled.';
        }

        // Check driver
        $driver = $this->nadiConfig['driver'] ?? 'log';
        $io->text("Driver: <info>{$driver}</info>");

        // Check driver-specific configuration
        $connections = $this->nadiConfig['connections'] ?? [];
        $driverConfig = $connections[$driver] ?? [];

        if ($driver === 'http') {
            if (empty($driverConfig['api_key'])) {
                $errors[] = 'HTTP driver requires NADI_API_KEY to be set.';
            }
            if (empty($driverConfig['app_key'])) {
                $errors[] = 'HTTP driver requires NADI_APP_KEY to be set.';
            }
        }

        if ($driver === 'log') {
            $logPath = $driverConfig['path'] ?? '';
            if ($logPath && ! is_writable(dirname($logPath))) {
                $errors[] = "Log path directory is not writable: {$logPath}";
            }
        }

        if ($driver === 'opentelemetry') {
            if (empty($driverConfig['endpoint'])) {
                $errors[] = 'OpenTelemetry driver requires an endpoint to be configured.';
            }
        }

        // Check transporter
        $transporter = $this->nadi->getTransporter();
        if (! $transporter) {
            $errors[] = 'Transporter could not be initialized.';
        } else {
            try {
                $result = $transporter->verify();
                if ($result) {
                    $io->text('Transporter verification: <info>OK</info>');
                } else {
                    $errors[] = 'Transporter verification failed.';
                }
            } catch (\Exception $e) {
                $errors[] = 'Transporter verification error: ' . $e->getMessage();
            }
        }

        // Report results
        if (! empty($warnings)) {
            foreach ($warnings as $warning) {
                $io->warning($warning);
            }
        }

        if (! empty($errors)) {
            foreach ($errors as $error) {
                $io->error($error);
            }

            return Command::FAILURE;
        }

        $io->success('Nadi configuration is valid!');

        return Command::SUCCESS;
    }
}
