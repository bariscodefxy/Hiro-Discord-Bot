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

namespace hiro\database;

use hiro\consts\RPG;
use PDO;
use PDOException;

/**
 * Database
 */
class Database extends PDO
{
    /**
     * isConnected
     *
     * @var boolean
     */
    public $isConnected = false;

    /**
     * __construct
     */
    public function __construct()
    {
        if (!isset($_ENV['DB_USER']) || !isset($_ENV['DB_PASS']) || !isset($_ENV['DB_HOST']) || !isset($_ENV['DB_NAME'])) {
            return false;
        }
        try {
            parent::__construct("mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8", $_ENV['DB_USER'], $_ENV['DB_PASS']);
        } catch (PDOException $e) {
            echo $e . PHP_EOL;
            return false;
        }
        $this->isConnected = true;
        $this->createTables();
    }

    /**
     * getUser
     *
     * @param  integer $user_id
     * @return boolean|array
     */
    public function getUser(int $user_id): bool|array
    {
        $query = $this->prepare("SELECT * FROM users WHERE id = ?");
        $query->execute([$user_id]);
        if ($query->rowCount()) {
            return $query->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    /**
     * getServer
     *
     * @param  integer $server_id
     * @return boolean|array
     */
    public function getServer(int $server_id): bool|array
    {
        $query = $this->prepare("SELECT * FROM servers WHERE id = ?");
        $query->execute([$server_id]);
        if ($query->rowCount()) {
            return $query->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    /**
     * getUserMoney
     *
     * @param  integer $user_id
     * @return boolean|integer
     */
    public function getUserMoney(int $user_id): bool|int
    {
        if ($this->getUser($user_id)) {
            $user = $this->getUser($user_id);
            return $user['money'];
        } else {
            return false;
        }
    }
    
    /**
     * getUserLevel
     *
     * @param  integer $user_id
     * @return boolean|integer
     */
    public function getUserLevel(int $user_id): bool|int
    {
        if ($this->getUser($user_id)) {
            $user = $this->getUser($user_id);
            return $user['level'];
        } else {
            return false;
        }
    }
    
    /**
     * getUserExperience
     *
     * @param  integer $user_id
     * @return boolean|integer
     */
    public function getUserExperience(int $user_id): bool|int
    {
        if ($this->getUser($user_id)) {
            $user = $this->getUser($user_id);
            return $user['experience'];
        } else {
            return false;
        }
    }

    /**
     * getUserIdByDiscordId
     *
     * @param  integer $discord_id
     * @return boolean|integer
     */
    public function getUserIdByDiscordId(int $discord_id): bool|int
    {
        $query = $this->prepare("SELECT * FROM users WHERE discord_id = ?");
        $query->execute([$discord_id]);
        if ($query->rowCount()) {
            return $query->fetch(PDO::FETCH_ASSOC)['id'];
        } else {
            $this->addUser(['discord_id' => $discord_id]);
            return $this->getUserIdByDiscordId($discord_id);
        }
    }

    /**
     * getServerIdByDiscordId
     *
     * @param  integer $discord_id
     * @return boolean|integer
     */
    public function getServerIdByDiscordId(int $discord_id): bool|int
    {
        $query = $this->prepare("SELECT * FROM servers WHERE discord_id = ?");
        $query->execute([$discord_id]);
        if ($query->rowCount()) {
            return $query->fetch(PDO::FETCH_ASSOC)['id'];
        } else {
            $this->addServer(['discord_id' => $discord_id]);
            return $this->getServerIdByDiscordId($discord_id);
        }
    }

    /**
     * addUser
     *
     * @param  array $data
     * @return boolean
     */
    public function addUser(array $data): bool
    {
        if (!$data['discord_id']) {
            return false;
        } else {
            $query = $this->prepare("INSERT INTO users SET discord_id = :discord_id, money = :money, register_time = :register_time, last_daily = :last_daily");
            if ($query->execute(
                [
                "discord_id" => $data['discord_id'],
                "money" => 0,
                "register_time" => time(),
                "last_daily" => 0,
                ]
            )
            ) {
                return true;
            } else {
                print_r($query->errorInfo());
                return false;
            }
        }
    }

    /**
     * addServer
     *
     * @param  array $data
     * @return boolean
     */
    public function addServer(array $data): bool
    {
        if (!$data['discord_id']) {
            return false;
        } else {
            $query = $this->prepare("INSERT INTO servers SET discord_id = :discord_id");
            if ($query->execute(
                [
                "discord_id" => $data['discord_id']
                ]
            )
            ) {
                return true;
            } else {
                print_r($query->errorInfo());
                return false;
            }
        }
    }

    /**
     * getRPGChannelForServer
     *
     * @param  integer $serverid
     * @return boolean|integer
     */
    public function getRPGChannelForServer(int $serverid): bool|int
    {
        $query = $this->prepare('SELECT rpg_channel FROM servers WHERE id = :id');
        $query->execute(
            [
            "id" => $serverid
            ]
        );
        return $query->fetch()[0] ?? false;
    }

    /**
     * getRPGEnabledForServer
     *
     * @param  integer $serverid
     * @return boolean|integer
     */
    public function getRPGEnabledForServer(int $serverid): bool|int
    {
        $query = $this->prepare('SELECT rpg_enabled FROM servers WHERE id = :id');
        $query->execute(
            [
            "id" => $serverid
            ]
        );
        return $query->fetch()[0] ?? false;
    }

    /**
     * getLastDailyForUser
     *
     * @param  integer $userid
     * @return boolean|integer
     */
    public function getLastDailyForUser(int $userid): bool|int
    {
        $query = $this->prepare('SELECT last_daily FROM users WHERE id = :id');
        $query->execute(
            [
            "id" => $userid
            ]
        );
        return $query->fetch()[0] ?? false;
    }

    /**
     * daily
     *
     * @param  integer $userid
     * @return boolean|integer
     */
    public function daily(int $userid): bool|int
    {
        $query = $this->prepare('SELECT money FROM users WHERE id = :id');
        $query->execute(
            [
            "id" => $userid
            ]
        );
        $usermoney = $query->fetch()[0];
        $daily = 500;
        $query = $this->prepare('UPDATE users SET money = :money, last_daily = :last_daily WHERE id = :id');
        $exec = $query->execute(["money" => $usermoney + $daily, "last_daily" => time(), "id" => $userid]);
        if ($exec) {
            return $daily;
        } else {
            return false;
        }
    }

    /**
     * setUserMoney
     *
     * @param  integer $user_id
     * @param  integer $money
     * @return boolean
     */
    public function setUserMoney(int $user_id, int $money): bool
    {
        $query = $this->prepare('UPDATE users SET money = :money WHERE id = :id');
        return $query->execute(
            [
            "money" => $money,
            "id" => $user_id
            ]
        );
    }

    /**
     * setUserExperience
     *
     * @param  integer $user_id
     * @param  integer $xp
     * @return boolean
     */
    public function setUserExperience(int $user_id, int $xp): bool
    {
        $query = $this->prepare('UPDATE users SET experience = :experience WHERE id = :id');
        return $query->execute(
            [
            "experience" => $xp,
            "id" => $user_id
            ]
        );
    }

    /**
     * setUserLevel
     *
     * @param  integer $user_id
     * @param  integer $level
     * @return boolean
     */
    public function setUserLevel(int $user_id, int $level): bool
    {
        $query = $this->prepare('UPDATE users SET level = :level WHERE id = :id');
        return $query->execute(
            [
            "level" => $level,
            "id" => $user_id
            ]
        );
    }

    /**
     * setServerRPGChannel
     *
     * @param  integer $server_id
     * @param  integer $channel
     * @return boolean
     */
    public function setServerRPGChannel(int $server_id, int $channel): bool
    {
        $query = $this->prepare('UPDATE servers SET rpg_channel = :rpg_channel WHERE id = :id');
        return $query->execute(
            [
            "rpg_channel" => $channel,
            "id" => $server_id
            ]
        );
    }

    /**
     * setServerRPGEnabled
     *
     * @param  integer $server_id
     * @param  integer $enabled
     * @return boolean
     */
    public function setServerRPGEnabled(int $server_id, int $enabled): bool
    {
        $query = $this->prepare('UPDATE servers SET rpg_enabled = :rpg_enabled WHERE id = :id');
        return $query->execute(
            [
            "rpg_enabled" => $enabled,
            "id" => $server_id
            ]
        );
    }

    /**
     * pay
     *
     * @param  integer $user1
     * @param  integer $user2
     * @param  integer $payamount
     * @return boolean
     */
    public function pay(int $user1, int $user2, int $payamount): bool
    {
        $user1_money = $this->getUserMoney($user1);
        $user2_money = $this->getUserMoney($user2);
        if ($payamount > $user1_money) {
            return false;
        }
        if ($payamount < 1) {
            return false;
        }
        if (!$this->setUserMoney($user1, $user1_money - $payamount)) {
            return false;
        }
        if (!$this->setUserMoney($user2, $user2_money + $payamount)) {
            return false;
        }
        return true;
    }

    /**
     * getRPGCharType
     *
     * @param  integer $user_id
     * @return integer|null
     */
    public function getRPGCharType(int $user_id): ?int
    {
        $user = $this->getUser($user_id);

        if ($user) {
            return $user['rpg_chartype'];
        }

        return null;
    }

    /**
     * setRPGCharType
     *
     * @param  integer $user_id
     * @param  integer $type
     * @return boolean
     */
    public function setRPGCharType(int $user_id, int $type): bool
    {
        $query = $this->prepare("UPDATE users SET rpg_chartype = ? WHERE id = ?");

        return $query->execute([$type, $user_id]);
    }

    /**
     * getRPGCharRace
     *
     * @param  integer $user_id
     * @return integer|null
     */
    public function getRPGCharRace(int $user_id): ?int
    {
        $user = $this->getUser($user_id);

        if ($user) {
            return $user['rpg_charrace'];
        }

        return null;
    }

    /**
     * setRPGCharRace
     *
     * @param  integer $user_id
     * @param  integer $race
     * @return boolean
     */
    public function setRPGCharRace(int $user_id, int $race): bool
    {
        $query = $this->prepare("UPDATE users SET rpg_charrace = ? WHERE id = ?");

        return $query->execute([$race, $user_id]);
    }

    /**
     * getRPGCharGender
     *
     * @param  integer $user_id
     * @return integer|null
     */
    public function getRPGCharGender(int $user_id): ?int
    {
        $user = $this->getUser($user_id);

        if ($user) {
            return $user['rpg_chargender'];
        }

        return null;
    }

    /**
     * setRPGCharGender
     *
     * @param  integer $user_id
     * @param  integer $gender
     * @return boolean
     */
    public function setRPGCharGender(int $user_id, int $gender): bool
    {
        $query = $this->prepare("UPDATE users SET rpg_chargender = ? WHERE id = ?");

        return $query->execute([$gender, $user_id]);
    }

    /**
     * getRPGCharGenderAsText
     *
     * @param  integer $user_id
     * @return string|null
     */
    public function getRPGCharGenderAsText(int $user_id): ?string
    {
        $gender = self::getRPGCharGender($user_id);
        if ($gender == RPG::MALE_GENDER) {
            $gender = "male";
        } elseif ($gender == RPG::FEMALE_GENDER) {
            $gender = "female";
        }

        return $gender;
    }

    /**
     * getRPGCharRaceAsText
     *
     * @param  integer $user_id
     * @return string|null
     */
    public function getRPGCharRaceAsText(int $user_id): ?string
    {
        $races = RPG::getRacesAsArray(true);

        $race = $races[self::getRPGCharRace($user_id)];
        return $race;
    }

    /**
     * getRPGCharTypeAsText
     *
     * @param  integer $user_id
     * @return string|null
     */
    public function getRPGCharTypeAsText(int $user_id): ?string
    {
        $type = self::getRPGCharType($user_id);
        if ($type == RPG::WARRIOR_CHAR) {
            $type = "warrior";
        } elseif ($type == RPG::RANGER_CHAR) {
            $type = "ranger";
        } elseif ($type == RPG::MAGE_CHAR) {
            $type = "mage";
        } elseif ($type == RPG::HEALER_CHAR) {
            $type = "healer";
        }

        return $type;
    }

    /**
     * getRPGUserItems
     *
     * @param  integer $user_id
     * @return array|null
     */
    public function getRPGUserItems(int $user_id): ?array
    {
        $query = $this->prepare(sprintf("SELECT * FROM rpg_items WHERE item_owner = ? LIMIT %d", RPG::MAX_ITEM_SLOT));
        $query->execute([$user_id]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * getRPGUserItemBySlot
     *
     * @param  integer $user_id
     * @param  integer $slot
     * @return array|null
     */
    public function getRPGUserItemBySlot(int $user_id, int $slot): ?array
    {
        $query = $this->prepare("SELECT * FROM rpg_items WHERE item_owner = ? AND item_slot = ?");
        $query->execute([$user_id, $slot]);

        $fetch = $query->fetch(\PDO::FETCH_ASSOC);
        if (!$fetch) {
            return null;
        }

        return $fetch;
    }

    /**
     * useRPGUserItem
     *
     * @param  integer $user_id
     * @param  integer $slot
     * @return boolean|null
     */
    public function useRPGUserItem(int $user_id, int $slot): ?bool
    {
        $item = $this->getRPGUserItemBySlot($user_id, $slot);
        if (!$item) {
            return null;
        }

        if (!($item['item_type'] & RPG::ITEM_ARMOR) && !($item['item_type'] & RPG::ITEM_WEAPON)) {
            return null;
        }

        return $this->query(
            sprintf(
                "UPDATE rpg_items 
            SET item_using = 1, item_slot = NULL
            WHERE item_owner = %d 
            AND item_slot = %d",
                $user_id,
                $slot
            )
        ) ? true : false;
    }

    /**
     * releaseRPGUserItem
     *
     * @param  integer $user_id
     * @param  integer $itemtype
     * @param  integer $toslot
     * @return boolean|null
     */
    public function releaseRPGUserItem(int $user_id, int $itemtype, int $toslot): ?bool
    {
        $item = $this->getRPGUsingItemByType($user_id, $itemtype);
        if (!$item) {
            return null;
        }

        if (!($item['item_type'] & $itemtype)) {
            return null;
        }

        return $this->query(
            sprintf(
                "UPDATE rpg_items SET item_using = NULL, item_slot = %d WHERE item_owner = %d AND item_type = %d AND item_using = 1",
                $toslot,
                $user_id,
                $item['item_type']
            )
        ) ? true : false;
    }

    /**
     * getRPGUsingItemByType
     *
     * @param  integer $user_id
     * @param  integer $type
     * @param  boolean $equals
     * @return array|null
     */
    public function getRPGUsingItemByType(int $user_id, int $type, bool $equals = true): ?array
    {
        $items = $this->getRPGUserItems($user_id);

        foreach ($items as $item) {
            if ($equals) {
                if ($item['item_type'] === $type) {
                    return $item;
                }
            } else {
                if ($item['item_type'] & $type) {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * findRPGEmptyInventorySlot
     *
     * @param  integer $user_id
     * @return integer|boolean
     */
    public function findRPGEmptyInventorySlot(int $user_id): int|bool
    {
        $items = $this->getRPGUserItems($user_id);

        for ($i = 0; $i < RPG::MAX_ITEM_SLOT; $i++) {
            $slot = $i;
            foreach ($items as $item) {
                if ($item['item_slot'] === $i) {
                    $slot = false;
                    continue 2;
                }
            }
            break;
        }

        return $slot;
    }

    /**
     * setRPGItemType
     *
     * @param  integer $id
     * @param  integer $type
     * @return boolean
     */
    public function setRPGItemType(int $id, int $type): bool
    {
        return $this->query(
            sprintf(
                "UPDATE rpg_items SET 
                    item_type = %d WHERE
                    id = %d", $type, $id
            )
        ) ? true : false;
    }

    /**
     * banUserFromBot
     *
     * @param integer $discord_id
     * @return \PDOStatement
     */
    public function banUserFromBot(int $discord_id): ?\PDOStatement
    {
        return $this->query(
            sprintf("INSERT INTO bans SET discord_id = %d", $discord_id)
        );
    }

    /**
     * unbanUserFromBot
     *
     * @param integer $discord_id
     * @return \PDOStatement
     */
    public function unbanUserFromBot(int $discord_id): ?\PDOStatement
    {
    	   return $this->query(
            sprintf("DELETE FROM bans WHERE discord_id = %d", $discord_id)
        );
    }

    /**
     * isUserBannedFromBot
     *
     * @param integer $discord_id
     * @return boolean
     */
    public function isUserBannedFromBot(int $discord_id): bool
    {
        $banned = $this->query(
            sprintf(
                "SELECT * FROM bans WHERE discord_id = %d", $discord_id
            )
        )->fetch() ? true : false;
        return $banned;
    }

    /**
     * getUserLocale
     *
     * @param integer $user_id
     * @return string|null
     */
    public function getUserLocale(int $user_id): ?string
    {
        return $this->getUser($user_id)['locale'] ?? null;
    }
    
    /**
     * setUserLocale
     *
     * @param integer $user_id
     * @return string|null
     */
    public function setUserLocale(int $user_id, ?string $locale = null): ?bool
    {
        return $this->query(
            sprintf(
                "UPDATE users SET locale = '%s' WHERE id = '%d'", $locale, $user_id
            )
        ) ? true : false;
    }

    /**
     * createTables
     *
     * @return void
     */
    public function createTables(): void
    {
        $this->exec(
            <<<EOF
        CREATE TABLE IF NOT EXISTS `users` (
            `id` bigint(21) NOT NULL AUTO_INCREMENT PRIMARY KEY
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        CREATE TABLE IF NOT EXISTS `servers` (
            `id` bigint(21) NOT NULL AUTO_INCREMENT PRIMARY KEY
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        CREATE TABLE IF NOT EXISTS `rpg_items` (
            `id` bigint(21) NOT NULL AUTO_INCREMENT PRIMARY KEY
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        CREATE TABLE IF NOT EXISTS `bans` (
            `id` bigint(21) NOT NULL AUTO_INCREMENT PRIMARY KEY
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        EOF
        );

        $columns = [
            "users" => [
                "discord_id" => "bigint(21) NOT NULL",
                "money" => "int(11) DEFAULT '0'",
                "last_daily" => "int(11) DEFAULT NULL",
                "register_time" => "int(11) DEFAULT NULL",
                "rpg_chartype" => "int(11) DEFAULT NULL",
                "rpg_charrace" => "int(11) DEFAULT NULL",
                "rpg_chargender" => "int(11) DEFAULT NULL",
                "experience" => "int(11) DEFAULT '0'",
                "level" => "int(11) DEFAULT '1'",
                "locale" => "varchar(100) DEFAULT NULL"
            ],
            "servers" => [
                "discord_id" => "bigint(21) NOT NULL",
                "rpg_channel" => "bigint(21) DEFAULT NULL",
                "rpg_enabled" => "int(11) DEFAULT NULL",
            ],
            "rpg_items" => [
                "item_owner" => "bigint(21) NOT NULL",
                "item_name" => "varchar(100) NOT NULL",
                "item_type" => "int(11) NOT NULL",
                "item_upgrade" => "int(11) NOT NULL",
                "item_image" => "varchar(100) NOT NULL",
                "item_slot" => "int(11) NOT NULL",
                "item_count" => "int(11) DEFAULT NULL",
                "item_using" => "int(11) DEFAULT NULL",
	    ],
	    "bans" => [
	    	"discord_id" => "bigint(21) NOT NULL",
	    ],
        ];

        foreach ($columns as $table_key => $table) {
            foreach ($table as $column_key => $column) {
                try {
                    $col = $this->query("SELECT $column_key FROM $table_key");
                } catch (\Exception $e) {
                    $this->exec("ALTER TABLE `$table_key` ADD $column_key $column;");
                }
            }
        }
    }
}
