<?php

namespace Nadi\Symfony\Command;

use Nadi\Symfony\Nadi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'nadi:test',
    description: 'Test the Nadi monitoring connection',
)]
class TestCommand extends Command
{
    public function __construct(
        private Nadi $nadi,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Testing Nadi Connection');

        $transporter = $this->nadi->getTransporter();

        if (! $transporter) {
            $io->error('Nadi transporter is not configured. Please run nadi:install first.');

            return Command::FAILURE;
        }

        try {
            $result = $transporter->test();

            if ($result) {
                $io->success('Successfully connected to Nadi!');

                return Command::SUCCESS;
            }

            $io->error('Connection test failed. Please check your configuration.');

            return Command::FAILURE;
        } catch (\Exception $e) {
            $io->error('Connection test failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
