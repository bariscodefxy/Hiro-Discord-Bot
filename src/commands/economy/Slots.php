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
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;
use Discord\Helpers\Collection;
use Discord\Parts\Interactions\Command\Option;

/**
 * Slots
 */
class Slots extends Command
{
    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "slots";
        $this->description = "An economy game.";
        $this->aliases = ["slot"];
        $this->category = "economy";
        $this->options = [
            (new Option($this->discord))
                ->setType(Option::INTEGER)
                ->setName('amount')
                ->setDescription('Amount of money to pay for slots.')
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
        $items = [
            "<:hiroslotsheart:1130403063203627080>",
            "<:hiroslotseggplant:1130403026318921848>",
            "<:hiroslotscherry:1130402988519850136>"
        ];
        $database = new Database();
        if (!$database->isConnected) {
            $msg->channel->sendMessage($language->getTranslator()->trans('database.notconnect'));
            return;
        }
        if($args instanceof Collection && $args->get('name', 'amount') !== null) {
            $payamount = $args->get('name', 'amount')->value;
        } else {
            $payamount = $args[0] ?? null;
        }
        $payamount ??= null;
        if (!isset($payamount)) {
            $msg->reply($language->getTranslator()->trans('commands.slots.no_amount'));
            return;
        }
        if (!is_numeric($payamount)) {
            $msg->reply($language->getTranslator()->trans('commands.slots.no_numeric_arg'));
            return;
        }
        if ($payamount < 1) {
            $msg->reply($language->getTranslator()->trans('commands.slots.invalid_amount'));
        }
        if ($payamount > $database->getUserMoney($database->getUserIdByDiscordId($msg->author->id))) {
            $msg->reply($language->getTranslator()->trans('commands.slots.not_enough_money'));
            return;
        }
        if (!$database->setUserMoney($database->getUserIdByDiscordId($msg->author->id), $database->getUserMoney($database->getUserIdByDiscordId($msg->author->id)) - $payamount)) {
            $msg->reply($language->getTranslator()->trans('commands.slots.fail_msg.pay'));
            return;
        }
        $chance = random_int(1, 3);
        if ($chance === 1) {
            if (!$database->setUserMoney($database->getUserIdByDiscordId($msg->author->id), $database->getUserMoney($database->getUserIdByDiscordId($msg->author->id)) + ($payamount * 3))) {
                $msg->reply($language->getTranslator()->trans('commands.slots.fail_msg.receive'));
                return;
            }
            $rand_emote = $items[random_int(0, sizeof($items) - 1)];
            $choosed = [
                $rand_emote,
                $rand_emote,
                $rand_emote
            ];
        } else {
            $rand_emotes = [
                random_int(0, sizeof($items) - 2),
                random_int(0, sizeof($items) - 2),
                random_int(0, sizeof($items) - 2)
            ];
            if ($rand_emotes[0] === $rand_emotes[1] && $rand_emotes[1] === $rand_emotes[2]) {
                if ($rand_emotes[0] === 0 || $rand_emotes[0] < sizeof($items) - 1) {
                    $rand_emotes[random_int(0, 2)] += 1;
                } else {
                    $rand_emotes[random_int(0, 2)] -= 1;
                }
            }
            $choosed = [
                $items[$rand_emotes[0]],
                $items[$rand_emotes[1]],
                $items[$rand_emotes[2]]
            ];
        }
        $msg->reply($language->getTranslator()->trans('commands.slots.spinning') . " \n<a:hiroslotspinning:1130399548523679754> <a:hiroslotspinning:1130399548523679754> <a:hiroslotspinning:1130399548523679754>")->then(function ($msg) use ($chance, $choosed, $payamount, $language) {
            if (!($msg instanceof Message)) {
                $msg->reply($language->getTranslator()->trans('global.unknown_error'));
                return;
            }
            $this->discord->getLoop()->addTimer(3.0, function () use ($msg, $chance, $choosed, $payamount, $language) {
                if ($chance === 1) $text = sprintf($language->getTranslator()->trans('commands.slots.win'), $payamount * 3, "<:hirocoin:1130392530677157898>");
                else $text = $language->getTranslator()->trans('commands.slots.lose');
                $msg->edit(MessageBuilder::new()->setContent(sprintf($language->getTranslator()->trans('commands.slots.spinned'), $choosed[0], $choosed[1], $choosed[2], $text)));
            });
        });
    }
}
