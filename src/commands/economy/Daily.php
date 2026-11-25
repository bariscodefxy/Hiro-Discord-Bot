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

use hiro\database\Database;

/**
 * Daily
 */
class Daily extends Command
{
    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "daily";
        $this->description = "Daily moneys.";
        $this->aliases = [];
        $this->category = "economy";
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
        if (!$database->isConnected) {
            $msg->reply($language->getTranslator()->trans('database.notconnect'));
            return;
        }
        $user_money = $database->getUserMoney($database->getUserIdByDiscordId($msg->author->id));
        $last_daily = $database->getLastDailyForUser($database->getUserIdByDiscordId($msg->author->id));

        if (time() - $last_daily < 86400) {
            $msg->reply(sprintf($language->getTranslator()->trans('commands.daily.cooldown_msg'), '<t:' . ($last_daily + 86400) . ':R>'));
            return;
        }
        
        if (!is_numeric($user_money)) {
            if (!$database->addUser([
                "discord_id" => $msg->member->id
            ])) {
                $msg->reply($language->getTranslator()->trans('database.user.couldnt_added'));
                return;
            } else {
                $user_money = 0;
            }
        }
        setlocale(LC_MONETARY, 'en_US');
        $daily = $database->daily($database->getUserIdByDiscordId($msg->author->id));
        if ($daily) {
            $msg->reply(sprintf($language->getTranslator()->trans('commands.daily.reward_msg'), number_format($daily, 2, ',', '.'), "<:hirocoin:1130392530677157898>"));
        } else {
            $msg->reply($language->getTranslator()->trans('commands.daily.fail_msg'));
        }
        $database = NULL;
    }
}
