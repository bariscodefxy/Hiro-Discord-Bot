<?php

namespace hiro\parts;

use hiro\interfaces\HiroInterface;

class Command extends \Discord\CommandClient\Command
{

    public function __construct(
        HiroInterface $client,
        string $command,
        callable $callable,
        string $description,
        string $longDescription,
        string $usage,
        int $cooldown,
        string $cooldownMessage,
        bool $showHelp = true
    ) {
        $this->client = $client;
        $this->command = $command;
        $this->callable = $callable;
        $this->description = $description;
        $this->longDescription = $longDescription;
        $this->usage = $usage;
        $this->cooldown = $cooldown;
        $this->cooldownMessage = $cooldownMessage;
        $this->showHelp = $showHelp;
    }

}