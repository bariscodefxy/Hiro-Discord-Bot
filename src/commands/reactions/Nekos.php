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
use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;
use Discord\Parts\Interactions\Command\Option;

/**
 * Nekos
 */
class Nekos extends Command
{
    /**
     * Browser
     *
     * @var Browser
     */
    public Browser $browser;

    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "nekos";
        $this->description = "Nekos API anime pictures & gifs.";
        $this->aliases = ["neko"];
        $this->category = "reactions";
        $this->browser = new Browser(null, $this->discord->getLoop());
        $this->options = [
            (new Option($this->discord))
                ->setType(Option::STRING)
                ->setName('category')
                ->setDescription('Category to get image or gif')
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
        $type_array = [];
        if($args instanceof Collection && $args->get('name', 'category') !== null) {
            $type = $args->get('name', 'category')->value;
        } else if (is_array($args)) {
            $type = $args[0] ?? null;
        }
        $type ??= "waifu";

        $this->browser->get("https://nekos.best/api/v2/endpoints")->then(function (ResponseInterface $response) use ($msg, $args, $type, $type_array, $language) {
            $result = json_decode((string)$response->getBody(), true);
            foreach ($result as $key => $useless) {
                $type_array[] = $key;
            }

            if (isset($args[0])) {
                if (!in_array($args[0], $type_array)) {
                    $msg->reply(sprintf($language->getTranslator()->trans('commands.nekos.not_available_category'), $args[0]) . " \n" . sprintf($language->getTranslator()->trans('commands.nekos.available_categories'), implode(", ", $type_array)));
                    return;
                }
                $type = $args[0];
            }
    
            $this->browser->get("https://nekos.best/api/v2/$type")->then(
                function (ResponseInterface $response) use ($msg) {
                    $result = (string)$response->getBody();
                    $api = json_decode($result)->results;
                    $embed = new Embed($this->discord);
                    $embed->setColor("#EB00EA");
                    $embed->setTitle('Nekos API');
                    $embed->setImage($api[0]->url);
                    $embed->setAuthor($msg->author->username, $msg->author->avatar);
                    $embed->setTimestamp();
                    $msg->reply($embed);
                },
                function (\Exception $e) use ($msg, $language) {
                    $msg->reply($language->getTranslator()->trans('commands.nekos.api_error'));
                }
            );
        });
    }
}
