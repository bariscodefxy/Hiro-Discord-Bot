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
use hiro\security\AuthorCommand;
use hiro\database\Database;
use Discord\Parts\Interactions\Command\Option;

/**
 * Botban
 */
class Botban extends AuthorCommand
{
	/**
	 * configure
	 *
	 * @return void
	 */
	public function configure(): void
	{
		$this->command = "botban";
		$this->description = "Ban/unban a player from bot. **ONLY FOR AUTHOR**";
		$this->aliases = [];
		$this->category = "author";
		$this->options = [
			(new Option($this->discord))
				->setType(Option::USER)
				->setName('user')
				->setDescription('User to ban/unban')
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
		$user = null;
		if ($args instanceof Collection && $args->get('name', 'user') !== null) {
			$user = $msg->mentions->first() ?? $this->discord->members->get('id', $args->get('name', 'user')->value);
		} else if (is_array($args)) {
			$user = $msg->mentions->first();
		}

		if (!$user) {
			$msg->reply("You should mention a user to ban.");
			return;
		}

		if ($user->id == $msg->author->id) {
			$msg->reply("You can't ban yourself.");
			return;
		}

		$db = new Database();
		if (!$db->isConnected) {
			$msg->reply("Couldn't connect to database.");
			return;
		}

		if (!$db->isUserBannedFromBot($user->id)) {
			$db->banUserFromBot($user->id);
			$msg->reply("{$user->username} has been banned.");
		} else {
			$db->unbanUserFromBot($user->id);
			$msg->reply("{$user->username}'s ban has been removed.");
		}
	}
}
