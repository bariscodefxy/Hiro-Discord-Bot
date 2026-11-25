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

use Discord\Voice\VoiceClient;
use hiro\parts\voice\VoiceSettings;

class Join extends Command
{
    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "join";
        $this->description = "Bot joins to voice channel that you in";
        $this->aliases = [];
        $this->category = "music";
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
        $channel = $msg->member->getVoiceChannel();

        if (!$channel) {
            $msg->reply($language->getTranslator()->trans('commands.join.no_channel'));
            return;
        }

        $this->discord->joinVoiceChannel($channel, false, false)->then(function (VoiceClient $vc) use ($channel) {
            global $voiceSettings;
            
            $settings = new VoiceSettings($vc);
            
            $voiceSettings[$channel->guild_id] = $settings;
            
            $vc->on('exit', function() use ($channel, $voiceSettings) {
                unset($voiceSettings[$channel->guild_id]);
            });
        }, function ($e) use ($msg, $language) {
            $msg->reply(sprintf($language->getTranslator()->trans('commands.join.on_error'), $e->getMessage()));
        });
    }
}
