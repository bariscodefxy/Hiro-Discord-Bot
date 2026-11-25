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
use hiro\database\Database;
use Discord\Parts\Interactions\Command\Option;

class Language extends Command
{

    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "lang";
        $this->description = "Select your language.";
        $this->aliases = ["language"];
        $this->category = "utility";
        $this->options = [
            (new Option($this->discord))
                ->setType(Option::STRING)
                ->setName('language')
                ->setDescription('Language to select.')
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
        $database = new Database();
        if(!$database->isConnected)
        {
            $msg->reply($language->trans('database.notconnect'));
            return;
        }

        $lang_array = [
            "en_EN" => "English",
            "tr_TR" => "Turkish",
            "kr_KR" => "Korean",
            "ja_JP" => "Japanese",
        ];

        if($args instanceof Collection && $args->get('name', 'language') !== null)
        {
            $selected = $args->get('name', 'language')->value;
        } else {
            $selected = $args[0] ?? null;
        }

        $selected ??= null;

        if(!$selected)
        {
            $lang_msg = "Available languages; ";
            foreach($lang_array as $lang)
            {
                $lang_msg .= $lang . " ";
            }
            $msg->reply($lang_msg);
            return;
        }

        foreach($lang_array as $key => $lang)
        {
            if(strtolower($lang) === trim(strtolower($selected)))
            {
                $database->setUserLocale($database->getUserIdByDiscordId($msg->author->id), $key);
                $language->getTranslator()->setLocale($key);
                $msg->reply(sprintf($language->getTranslator()->trans('commands.language.changed'), $lang));
                return;
            }
        }

        $msg->reply($language->getTranslator()->trans('commands.language.notavailable'));
    }
}
