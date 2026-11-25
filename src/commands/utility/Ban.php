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
use Discord\Parts\Interactions\Command\Option;

class Ban extends Command
{

    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "ban";
        $this->description = "Bans mentioned user.";
        $this->aliases = [];
        $this->category = "utility";
        $this->options = [
            (new Option($this->discord))
                ->setType(Option::USER)
                ->setName('user')
                ->setDescription('User to ban')
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
        if (@$msg->member->getPermissions()['ban_members']) {
            if ($args instanceof Collection && $args->get('name', 'user') !== null) {
                $user = $msg->guild->members->get($args->get('name', 'user')->value);
            } else if (is_array($args)) {
                $user = $msg->mentions->first() ?? null;
            }
            $user ??= null;
            if ($user) {
                $banner = $msg->author;
                if (!isset($msg->guild->members[$user->id])) {
                    $embed = new Embed($this->discord);
                    $embed->setColor('#ff0000');
                    $embed->setDescription($language->getTranslator()->trans('global.user_not_found'));
                    $embed->setTimestamp();
                    $msg->reply($embed);
                    return;
                }
                $roles_men = $this->rolePositionsMap($msg->guild->members[$user->id]->roles);
                $roles_self = $this->rolePositionsMap($msg->member->roles);
                if ($roles_men) {
                    $roles_men = max($roles_men);
                } else {
                    $roles_men = 0;
                }
                if ($roles_self) {
                    $roles_self = max($roles_self);
                } else {
                    $roles_men = 0;
                }
                if ($banner->id == $user->id) {
                    $embed = new Embed($this->discord);
                    $embed->setColor('#ff0000');
                    $embed->setDescription($language->getTranslator()->trans('commands.ban.selfban'));
                    $embed->setTimestamp();
                    $msg->reply($embed);
                    return;
                } else {
                    if (($roles_self < $roles_men) && !($msg->guild->owner_id == $msg->member->id)) {
                        $embed = new Embed($this->discord);
                        $embed->setColor('#ff0000');
                        $embed->setDescription($language->getTranslator()->trans('commands.ban.role_pos_low'));
                        $embed->setTimestamp();
                        $msg->reply($embed);
                    } else {
                        $msg->guild->members[$user->id]->ban(null, null)
                            ->then(function () use ($msg, $user, $banner, $language) {
                                $embed = new Embed($this->discord);
                                $embed->setColor('#ff0000');
                                $embed->setDescription(sprintf($language->getTranslator()->trans('commands.ban.banned'), $user, $banner));
                                $embed->setTimestamp();
                                $msg->reply($embed);
                            }, function (\Throwable $reason) use ($msg, $language) {
                                $msg->reply($reason->getCode() === 50013 ? $language->getTranslator()->trans('commands.ban.no_bot_perm') : $language->getTranslator()->trans('global.unknown_error'));
                            });
                    }
                }
            } else {
                $embed = new Embed($this->discord);
                $embed->setColor('#ff0000');
                $embed->setDescription($language->getTranslator()->trans('commands.ban.no_user'));
                $embed->setTimestamp();
                $msg->reply($embed);
            }
        } else {
            $embed = new Embed($this->discord);
            $embed->setColor('#ff0000');
            $embed->setDescription($language->getTranslator()->trans('commands.ban.no_perm'));
            $embed->setTimestamp();
            $msg->reply($embed);
        }
    }

    /**
     * rolePositionsMap
     * 
     * This function returns descending list role positions of server for user.
     *
     * @param [type] $rolesCollision
     * @return void
     */
    protected function rolePositionsMap($rolesCollision)
    {
        $rolesArray = $rolesCollision->toArray();
        $new = [];
        foreach ($rolesArray as $role) {
            $new[] = $role->position;
        }
        return $new;
    }
}
