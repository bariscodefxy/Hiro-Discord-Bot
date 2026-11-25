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
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Command\Option;

/**
 * Howgay
 */
class Howgay extends Command
{
    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "howgay";
        $this->description = "How much u are gay?";
        $this->aliases = ["gay"];
        $this->category = "reactions";
        $this->options = [
            (new Option($this->discord))
                ->setType(Option::USER)
                ->setName('user')
                ->setDescription('User to check')
                ->setRequired(false)
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
        global $language;
        if ($args instanceof Collection && $args->get('name', 'user') !== null) {
            $user = $this->discord->users->get('id', $args->get('name', 'user')->value);
        } else if (is_array($args)) {
            $user = $msg->mentions->first();
        }
        $user ??= null;
        if (!$user) $user = $msg->author;
        $random = rand(0, 100);
        $embed = new Embed($this->discord);
        $embed->setColor("#EB00EA");
        $embed->setDescription(sprintf("%s :gay_pride_flag:", sprintf($language->getTranslator()->trans('commands.howgay.description'), $user->username, $random . "%")));
        $embed->setTimestamp();
        $msg->reply($embed);
    }
}
