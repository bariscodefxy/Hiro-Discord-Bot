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

use hiro\security\MusicCommand;

class Skip extends MusicCommand
{
    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "skip";
        $this->description = "Skips the current song.";
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
        global $voiceSettings;

        $voiceClient = $this->discord->getVoiceClient($msg->channel->guild_id);
        
        $settings = @$voiceSettings[$msg->guild_id];
        
        try {
            $voiceClient->stop();
            if($cmd = $this->loader->getCmd("play"))
            {
                $settings->nextSong();
                $cmd->playMusic($msg->channel, $settings, $language);
            }
        } catch (\Throwable $e) {
            $msg->reply($e->getMessage());
        }
    }
}
