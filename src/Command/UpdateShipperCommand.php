<?php

namespace Nadi\Symfony\Command;

use Nadi\Symfony\Shipper\Shipper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'nadi:update-shipper',
    description: 'Update the Nadi shipper binary',
)]
class UpdateShipperCommand extends Command
{
    public function __construct(
        private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Updating Nadi Shipper');

        try {
            $shipper = new Shipper($this->projectDir);
            $shipper->install();

            $io->success('Shipper binary has been updated successfully.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to update shipper: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
