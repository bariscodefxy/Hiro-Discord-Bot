<?php

/**
 * Copyright © 2025-present bariscodefx
 * 
 * This file part of project Hiro Discord Bot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace hiro\commands;

use Discord\Helpers\Collection;
use Discord\Parts\Interactions\Command\Option;
use hiro\security\AuthorCommand;
use React\ChildProcess\Process;

/**
 * Exec
 */
class Exec extends AuthorCommand
{
    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "exec";
        $this->description = "Executes an command **ONLY FOR AUTHOR**";
        $this->aliases = ["execute", "shell-exec"];
        $this->category = "author";
        $this->options = [
            (new Option($this->discord))
                ->setType(Option::STRING)
                ->setName('command')
                ->setDescription('Command to execute')
                ->setRequired(true)
        ];
    }

    /**
     * handle
     *
     * @param [type] $msg
     * @param [type] $args
     * @return void
     */
    public function handle($msg, $args): void
    {
        $ex = null;
        if ($args instanceof Collection && $args->get('name', 'command') !== null) {
            $ex = $args->get('name', 'command')->value;
        } else if (is_array($args)) {
            $ex = implode(' ', $args);
        }

        if (!$ex) $ex = " ";

        $process = new Process($ex);
        $process->start();

        $process->stdout->on('data', function ($chunk) use ($msg) {
            $msg->reply("```\n{$chunk}\n```");
        });

        $this->discord->getLoop()->addTimer(20.0, function () use ($process) {
            $process->terminate();
        });
    }
}
