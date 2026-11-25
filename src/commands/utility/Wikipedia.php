<?php

/**
 * This file is part of project Hiro Discord Bot.
 *
 * Copyright 2023
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
use Discord\Parts\Interactions\Command\Option;
use hiro\commands\Command;
use hiro\Version;
use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;

/**
 * Wikipedia
 */
class Wikipedia extends Command
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
        $this->command = "wikipedia";
        $this->description = "Searches for a Wikipedia page.";
        $this->aliases = ["wiki", "w"];
        $this->category = "utility";
        $this->browser = new Browser(null, $this->discord->getLoop());
        $this->options = [
            (new Option($this->discord))
                ->setType(Option::STRING)
                ->setName('query')
                ->setDescription('The search query for the Wikipedia page.')
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

        if($args instanceof Collection && $args->get('name', 'query') !== null) {
            $query = $args->get('name', 'query')->value;
        } else {
            $query = implode(" ", $args);
        }
        
        $query ??= null;

        if (empty($args)) {
            $msg->channel->sendMessage($language->getTranslator->trans('commands.wikipedia.no_query'));
            return;
        }

        $this->browser->get("https://en.wikipedia.org/w/api.php?action=query&format=json&list=search&srprop=snippet&srsearch=$query")->then(
            function( ResponseInterface $response ) use ( $msg, $language ) {
                $body = (string)$response->getBody();
                $json = json_decode($body);

                if (empty($json->query->search)) {
                    $msg->channel->sendMessage($language->getTranslator->trans('commands.wikipedia.no_results'));
                    return;
                }

                $result = $json->query->search[0];

                // Create an embed with the result information
                $embed = new Embed($this->discord);
                $embed->setTitle($result->title);
                $embed->setDescription($result->snippet);
                $embed->setURL("https://en.wikipedia.org/wiki/" . urlencode($result->title));
                $embed->setThumbnail("https://en.wikipedia.org/static/favicon/wikipedia.ico");
                $embed->setAuthor($msg->member->username, $msg->author->avatar);
                $embed->setTimestamp();
        
                // Send the embed
                $msg->reply($embed);
            },
            function (\Exception $e) use ($msg, $language) {
                $msg->reply($language->getTranslator->trans('commands.wikipedia.api_error'));
            }
        );
    }
}
