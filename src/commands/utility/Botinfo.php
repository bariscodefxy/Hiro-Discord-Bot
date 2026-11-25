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

use Discord\Parts\Embed\Embed;
use hiro\Version;

/**
 * Botinfo
 */
class Botinfo extends Command
{
    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "botinfo";
        $this->description = "Shows bot's information.";
        $this->aliases = [];
        $this->category = "utility";
    }

    /**
     * handle
     *
     * @param [type] $msg
     * @param [type] $args
     * @return void
     */
    public function handle(\hiro\parts\Respondable $msg, $args): void
    {
        global $language;
        $guilds             = $this->discord->formatNumber(sizeof($this->discord->guilds));
        $members            = $this->discord->formatNumber(sizeof($this->discord->users));
        $embed = new Embed($this->discord);
        $embed->setTitle($language->getTranslator()->trans('commands.botinfo.title'));
        $embed->addField($this->discord->makeField($language->getTranslator()->trans('commands.botinfo.shard_id'), $this->discord->options['shardId']));
        $embed->addField($this->discord->makeField($language->getTranslator()->trans('commands.botinfo.shard_count'), $this->discord->options['shardCount']));
        $embed->addField($this->discord->makeField($language->getTranslator()->trans('commands.botinfo.guilds'), $guilds));
        $embed->addField($this->discord->makeField($language->getTranslator()->trans('commands.botinfo.members'), $members));
        $embed->addField($this->discord->makeField($language->getTranslator()->trans('commands.botinfo.commands'), $this->loader->getCommandsCount()));
        $embed->addField($this->discord->makeField($language->getTranslator()->trans('commands.botinfo.version'), sprintf("%s %s", Version::VERSION, Version::TYPE)));
        $embed->setThumbnail($this->discord->avatar);
        $embed->setAuthor($msg->author->username, $msg->author->avatar);
        $embed->setTimestamp();
        $msg->reply($embed);
    }
}
