<?php

namespace Nadi\Symfony\Command;

use Nadi\Symfony\Shipper\Shipper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'nadi:install',
    description: 'Install and configure Nadi monitoring for your Symfony application',
)]
class InstallCommand extends Command
{
    public function __construct(
        private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Installing Nadi Monitoring');

        // Copy default config if not exists
        $configSource = dirname(__DIR__, 2) . '/config/nadi.yaml';
        $configDest = $this->projectDir . '/config/packages/nadi.yaml';

        if (! file_exists($configDest)) {
            if (file_exists($configSource)) {
                if (! is_dir(dirname($configDest))) {
                    mkdir(dirname($configDest), 0755, true);
                }
                copy($configSource, $configDest);
                $io->success('Configuration file published to config/packages/nadi.yaml');
            } else {
                $io->warning('Default configuration file not found. Please create config/packages/nadi.yaml manually.');
            }
        } else {
            $io->note('Configuration file already exists at config/packages/nadi.yaml');
        }

        // Install shipper binary
        $io->section('Installing Shipper Binary');

        try {
            $shipper = new Shipper($this->projectDir);
            $shipper->install();
            $io->success('Shipper binary installed successfully.');
        } catch (\Exception $e) {
            $io->warning('Could not install shipper binary: ' . $e->getMessage());
            $io->note('You can install it later with: bin/console nadi:update-shipper');
        }

        // Environment variables reminder
        $io->section('Environment Configuration');
        $io->text([
            'Add the following to your .env file:',
            '',
            'NADI_API_KEY=your-api-key',
            'NADI_APP_KEY=your-app-key',
            '',
            'Get your keys at: https://nadi.pro',
        ]);

        $io->success('Nadi monitoring has been installed successfully!');

        return Command::SUCCESS;
    }
}
