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
use Discord\Parts\Embed\Field;
use Discord\Parts\Interactions\Command\Option;

/**
 * Help
 */
class Help extends Command
{
    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->discord->unregisterCommand('help');
        $this->command = "help";
        $this->description = "Displays commands.";
        $this->aliases = ["?"];
        $this->category = "utility";
        $this->options = [
            (new Option($this->discord))
                ->setType(Option::STRING)
                ->setName('command')
                ->setDescription('Specific command for help')
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
        if($args instanceof Collection && $args->get('name', 'command') !== null) {
            $command = $args->get('name', 'command')->value;
        } else {
            $command = $args[0] ?? null;
        }
        $command ??= null;
        if ($command) {
            if ($cmd = $this->findCommand($command)) {
                $description = $cmd->description ?? $language->getTranslator()->trans('commands.help.specific_command.no_description');
                $cooldown = $cmd->cooldown ? $cmd->cooldown / 1000 . $language->getTranslator()->trans('commands.help.specific_command.seconds') : $language->getTranslator()->trans('commands.help.specific_command.no_cooldown');

                $embed = new Embed($this->discord);
                $embed->setTitle($command);
                $embed->setColor("#ff0000");
                $embed->setDescription(
                    <<<EOF
{$language->getTranslator()->trans('commands.help.specific_command.description.name')}: {$command}
{$language->getTranslator()->trans('commands.help.specific_command.description.description')}: {$description}
{$language->getTranslator()->trans('commands.help.specific_command.description.cooldown')}: {$cooldown}
EOF
                );
                $embed->setAuthor($msg->author->username, $msg->author->avatar);
                $msg->reply($embed);
            } else {
                $msg->channel->sendMessage(sprintf($language->getTranslator()->trans('commands.help.specific_command.not_found'), $command));
            }
            return;
        }
        $prefix = $this->discord->getCommandClientOptions()['prefix'];
        $emotes = [
            "economy" => ":money_with_wings:",
            "reactions" => ":wink:",
            "rpg" => ":crossed_swords:",
            "music" => ":notes:",
            "utility" => ":tools:",
        ];
        $embed = new Embed($this->discord);
        $embed->setColor("#ff0000");
        $embed->setTitle("Bot Commands");
        $embed->setDescription(
            sprintf(
                $language->getTranslator()->trans('commands.help.description'),
                $prefix,
                "[Github](https://github.com/bariscodefxy/Hiro-Discord-Bot)"
            )
        );
	$embed->setImage("https://raw.githubusercontent.com/bariscodefxy/Hiro-Discord-Bot/master/resources/zero-two-hiro.gif");
	$embed->setThumbnail($this->discord->avatar);
        foreach ($this->loader->categories as $category) {
            $category_name = array_search($category, $this->loader->categories);
            if ($category_name == "author") continue;
            $field = new Field($this->discord);
            $value = "`";
            $b = 0;
            foreach ($category as $cmd) {
                $cmd = $cmd->command;
                if ($b == sizeof($category) - 1) {
                    if (isset($emotes[$category_name]))
                        $field->name = $emotes[$category_name] . " " . array_search($category, $this->loader->categories) . " (" . sizeof($category) . ")";
                    else
                        $field->name = ":grey_question: " . $category_name . " (" . sizeof($category) . ")";
                    $value .= "$cmd`";
                } else {
                    $value .= "$cmd, ";
                }
                $b++;
            }
            $field->value = $value;
            $embed->addField($field);
        }
        $embed->setAuthor($msg->author->username, $msg->author->avatar);
        $embed->setTimestamp();
        $msg->reply($embed);
    }

    /**
     * findCommand
     *
     * @param string $command_name
     * @return Command|null
     */
    protected function findCommand(string $command_name): ?Command
    {
        $cmd = null;
        foreach ($this->loader->categories as $category) {
            foreach ($category as $command) {
                if ($command->command === $command_name) {
                    $cmd = $command;
                }
            }
        }
        return $cmd;
    }
}
