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

use Discord\Builders\MessageBuilder;
use hiro\interfaces\SecurityCommandInterface;
use hiro\commands\Command;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Channel\Message;

/**
 * AuthorCommand
 */
class AuthorCommand extends Command implements SecurityCommandInterface
{

    /**
     * securityChecks
     *
     * @param array $args
     * @return boolean
     */
    public function securityChecks(array $args): bool
    {
        if(!isset($args['respondable']))
        {
            return false;
        }

        if(!($args['respondable']->user->id == @$_ENV['AUTHOR']))
        {
            $args['respondable']->reply('Only bot author can use this command.');
            return false;
        }

        return true;
    }

}