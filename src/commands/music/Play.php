<?php

/**
 * Copyright © 2025-present bariscodefx
 * 
 * This file is part of project Hiro Discord Bot.
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
use React\ChildProcess\Process;
use Discord\Builders\MessageBuilder;
use Discord\Helpers\Collection;
use hiro\parts\voice\VoiceFile;
use React\Http\Browser;
use Discord\Parts\Interactions\Command\Option;

class Play extends MusicCommand
{
    /**
     * Browser
     *
     * @var Browser
     */
    public Browser $browser;

    public function configure(): void
    {
        $this->command = "play";
        $this->description = "Plays music from youtube.";
        $this->aliases = [];
        $this->category = "music";
        $this->browser = new Browser(null, $this->discord->getLoop());
        $this->options = [
            (new Option($this->discord))
                ->setType(Option::STRING)
                ->setName('url')
                ->setDescription('Youtube video url.')
                ->setRequired(true)
        ];
    }

    public function playMusic($text_channel, $settings, $language)
    {
        $voice_client = $settings->getVoiceClient();
        $current_voice_file = $settings->getQueue()[0] ?? null;

        if (!$current_voice_file) {
            $text_channel->sendMessage($language->getTranslator()->trans('commands.play.no_queue'));
            return;
        }
        $author_id = $settings->getQueue()[0]->getAuthorId();

        @unlink($author_id . ".m4a");
        @unlink($author_id . ".info.json");

        $executable = (PHP_OS_FAMILY === 'Windows') ? 'yt-dlp.exe' : './yt-dlp';
        $fds = (PHP_OS_FAMILY === 'Windows') ? [] : null;
        $command = "{$executable} -f bestaudio[ext=m4a] --ignore-config --ignore-errors --write-info-json --output=./{$author_id}.m4a --audio-quality=0 \"{$settings->getQueue()[0]->getUrl()}\"";
        $process = new Process($command, null, null, $fds ?? array(
            array('pipe', 'r'),
            array('pipe', 'w'),
            array('pipe', 'w'),
        ));
        $process->start();

        $editmsg = $text_channel->sendMessage($language->getTranslator()->trans('commands.play.downloading'));

        $process->on('exit', function ($code, $term) use ($voice_client, $author_id, $editmsg, $settings, $text_channel, $language) {
            if (is_file($author_id . ".m4a")) {
                $play_file_promise = $voice_client->playFile($author_id . ".m4a");
            }

            $editmsg->then(function ($m) use ($text_channel, $author_id, $play_file_promise, $settings, $language) {

                if (!is_file($author_id . ".m4a")) {
                    $m->edit(MessageBuilder::new()->setContent($language->getTranslator()->trans('commands.play.couldnt_download')));
                    return;
                }

                $jsondata = json_decode(file_get_contents($author_id . ".info.json"));

                $m->edit(MessageBuilder::new()->setContent(sprintf($language->getTranslator()->trans('commands.play.playing'), $jsondata->title) . " :musical_note: :tada:"))->then(function () use ($m, $play_file_promise, $settings, $text_channel, $language) {
                    $play_file_promise->then(function () use ($m, $settings, $text_channel, $language) {
                        if (@$settings->getQueue()[0]) {
                            if (!$settings->getLoopEnabled()) {
                                $settings->nextSong();
                            }
                            $this->playMusic($text_channel, $settings, $language);
                        } else {
                            $m->channel->sendMessage(MessageBuilder::new()->setContent($language->getTranslator()->trans('commands.play.no_queue')));
                        }

                        $m->delete();
                    });
                });
            });

            $this->discord->getLoop()->addTimer(0.5, function () use ($author_id) {
                @unlink($author_id . ".m4a");
                @unlink($author_id . ".info.json");
            });
        });
    }

    public function handle($msg, $args): void
    {
        global $language;
        global $voiceSettings;

        $url = null;
        if ($args instanceof Collection && $args->get('name', 'url') !== null) {
            $url = $args->get('name', 'url')->value;
        } else if (is_array($args)) {
            $url = substr($msg->content, strlen($_ENV['PREFIX'] . "play "));
        }

        if (!$url) {
            $msg->reply($language->getTranslator()->trans('commands.play.no_url'));
            return;
        }

        $settings = @$voiceSettings[$msg->channel->guild_id];

        $url = str_replace('\\', '', trim($url));
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            $msg->reply($language->getTranslator()->trans('commands.play.invalid_url'));
            return;
        }

        preg_match('/https?:\/\/(www\.)?youtube\.com\/watch\?v\=([A-Za-z0-9-_]+)/', $url, $matches);
        preg_match('/https?:\/\/(www\.)?youtu\.be\/([A-Za-z0-9-_]+)/', $url, $matches2);
        preg_match('/https?:\/\/(www\.)?youtube\.com\/shorts\/([A-Za-z0-9-_]+)/', $url, $matches3);
        if (!@$matches[0] && !@$matches2[0] && !@$matches3[0]) {
            $msg->reply($language->getTranslator()->trans('commands.play.no_youtube_url'));
            return;
        }
        $url = $matches[0] ?? $matches2[0] ?? $matches3[0];

        if (sizeof($settings->getQueue()) >= 10) {
            $msg->reply($language->getTranslator()->trans('commands.play.queue_overflow'));
            return;
        }

        $settings->addToQueue($voice_file = new VoiceFile(null, $url, $msg->author->id));

        $this->browser->get('https://noembed.com/embed?url=' . $url)->then(function (\Psr\Http\Message\ResponseInterface $response) use ($voice_file) {
            $data = json_decode((string) $response->getBody());
            if (isset($data->title)) {
                $voice_file->setTitle($data->title);
            }
        }, function (\Exception $e) {
        });

        if (@$settings->getQueue()[1]) {
            $msg->reply($language->getTranslator()->trans('commands.play.added_to_queue'));
            return;
        }

        $this->playMusic($msg->channel, $settings, $language);
    }
}
