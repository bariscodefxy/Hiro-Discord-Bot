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

namespace hiro\security;

use hiro\interfaces\SecurityCommandInterface;
use hiro\commands\Command;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

/**
 * MusicCommand
 */
class MusicCommand extends Command implements SecurityCommandInterface
{

    /**
     * securityChecks
     *
     * @param array $args
     * @return boolean
     */
    public function securityChecks(array $args): bool
    {
        global $voiceSettings;
        if (!isset($args['respondable'])) {
            return false;
        }

        $userVoiceChannel = $args['respondable']->member->getVoiceChannel();
        if (!$userVoiceChannel) {
            $args['respondable']->reply("You're must be in a voice channel.");
            return false;
        }

        $voiceClient = $args['client']->getVoiceClient($args['respondable']->guild_id);
        if (!$voiceClient) {
            $args['respondable']->reply("You should use join command first.");
            return false;
        }

        if ($userVoiceChannel->id !== ($voiceClient->channel->id ?? null)) {
            $args['respondable']->reply("You must be in same channel with me.");
            return false;
        }

        if (!isset($voiceSettings[$args['respondable']->guild_id])) {
            $args['respondable']->reply("Voice options couldn't found.");
            return false;
        }

        return true;
    }

}