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
use hiro\database\Database;

class Pay extends Command
{
    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "pay";
        $this->description = "Send your money to anybody.";
        $this->aliases = [];
        $this->category = "economy";
        $this->options = [
            (new Option($this->discord))
                ->setType(Option::USER)
                ->setName('user')
                ->setDescription('User to send money')
                ->setRequired(true),
            (new Option($this->discord))
                ->setType(Option::INTEGER)
                ->setName('amount')
                ->setDescription('Amount of money')
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
        global $language;
        $database = new Database();
        if (!$database->isConnected) {
            $msg->reply($language->getTranslator()->trans('database.notconnect'));
            return;
        }
        if ($args instanceof Collection && $args->get('name', 'user') !== null) {
            $user = $this->discord->users->get('id', $args->get('name', 'user')->value);
        } else {
            $user = $msg->mentions->first() ?? null;
        }
        $user ??= null;
        if (!$user) {
            $msg->reply($language->getTranslator()->trans('commands.pay.no_user'));
            return;
        }
        if ($user->id === $msg->author->id) {
            $msg->reply($language->getTranslator()->trans('commands.pay.selfsend'));
            return;
        }
        if($args instanceof Collection && $args->get('name', 'amount') !== null) {
            $amount = $args->get('name', 'amount')->value;
        } else if (is_array($args)) {
            $amount = explode("$user ", implode(' ', $args))[1] ?? null;
        }
        $amount ??= null;
        if (!isset($amount) && !is_numeric($amount)) {
            $msg->reply($language->getTranslator()->trans('commands.pay.no_numeric_arg'));
            return;
        }
        if (!$database->pay($database->getUserIdByDiscordId($msg->author->id), $database->getUserIdByDiscordId($user->id), $amount)) {
            $msg->reply($language->getTranslator()->trans('commands.pay.fail_msg'));
            return;
        }
        setlocale(LC_MONETARY, 'en_US');
        $msg->reply(
            sprintf(
                $language->getTranslator()->trans('commands.pay.pay_msg'),
                $msg->user->username, number_format($amount, 2, ',', '.'), "<:hirocoin:1130392530677157898>",
                $user->username, number_format($amount, 2, ',', '.'), "<:hirocoin:1130392530677157898>" 
            )
        );
        return;
    }
}
