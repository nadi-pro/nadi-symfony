<?php

namespace Nadi\Symfony\Handler;

use Nadi\Data\Type;
use Nadi\Symfony\Data\Entry;
use Nadi\Symfony\Support\OpenTelemetrySemanticConventions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

class HandleCommandEvent extends Base
{
    public function handle(ConsoleTerminateEvent $event): void
    {
        if ($event->getExitCode() === Command::SUCCESS) {
            return;
        }

        $command = $event->getCommand();
        $commandName = $command ? $command->getName() : 'unknown';

        $otelAttributes = OpenTelemetrySemanticConventions::commandAttributes($commandName);
        $userAttributes = OpenTelemetrySemanticConventions::userAttributes();
        $otelData = array_merge($otelAttributes, $userAttributes);

        $entryData = [
            'command' => $commandName,
            'exit_code' => $event->getExitCode(),
            'otel' => $otelData,
        ];

        $this->store(
            Entry::make(Type::COMMAND, $entryData)
                ->setHashFamily($this->hash($commandName.$event->getExitCode().date('Y-m-d')))
                ->tags(['command:'.$commandName, 'exit_code:'.$event->getExitCode()])
                ->toArray()
        );
    }
}
