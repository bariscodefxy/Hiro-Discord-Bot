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

namespace hiro\consts;

/**
 * RPG - Constants of RPG
 */
class RPG
{
    /**
     * Character Types
     */
    public const WARRIOR_CHAR                      = (1 << 1);
    public const RANGER_CHAR                       = (1 << 2);
    public const MAGE_CHAR                         = (1 << 3);
    public const HEALER_CHAR                       = (1 << 4);

    /**
     * Race Types
     */
    public const HUMAN_RACE                        = (1 << 1);
    public const DRAGON_RACE                       = (1 << 2);
    public const ELF_RACE                          = (1 << 3);
    public const EVILEYETRIBE_RACE                 = (1 << 4);
    public const ONI_RACE                          = (1 << 7);
    public const WOLFHUMAN_RACE                    = (1 << 8);

    /**
     * Gender Types
     */
    public const MALE_GENDER                       = (1 << 1);
    public const FEMALE_GENDER                     = (1 << 2);

    /**
     * Item Types
     */
    public const ITEM_DEFAULT                      = (1 << 1);
    public const ITEM_WEAPON                       = (1 << 2);
    public const ITEM_ARMOR                        = (1 << 3);
    public const ITEM_ARMOR_BOOTS                  = (1 << 4);
    public const ITEM_ARMOR_GLOVES                 = (1 << 5);
    public const ITEM_ARMOR_HELMET                 = (1 << 6);
    public const ITEM_ARMOR_PANTS                  = (1 << 7);
    public const ITEM_ARMOR_PAULDRON               = (1 << 8);
    public const ITEM_ARMOR_EARRING                = (1 << 9);
    public const ITEM_ARMOR_RING                   = (1 << 10);
    public const ITEM_ARMOR_BELT                   = (1 << 11);
    public const ITEM_ARMOR_PENDANT                = (1 << 12);
    public const ITEM_ARMOR_SHIELD                 = (1 << 13); // shield & weapon 2
    public const ITEM_WEAPON_TWOHANDED             = (1 << 14);
    public const ITEM_WEAPON_ONEHANDED             = (1 << 15);
    public const ITEM_USED_LEFT                    = (1 << 16);
    public const ITEM_USED_RIGHT                   = (1 << 17);
    public const ITEM_DOUBLE_USABLE                = (1 << 18);
    public const ITEM_USABLE_BY_WARRIOR            = (1 << 19);
    public const ITEM_USABLE_BY_MAGE               = (1 << 20);
    public const ITEM_USABLE_BY_RANGER             = (1 << 21);
    public const ITEM_USABLE_BY_HEALER             = (1 << 22);


    /**
     * Inventory UI
     */
    public const MAX_ITEM_SLOT                     = 7 * 4;
    public const POS_HELMET                        = [292, 96];
    public const POS_PAULDRON                      = [292, 148];
    public const POS_PANTS                         = [292, 252];
    public const POS_BOOTS                         = [292, 304];
    public const POS_GLOVES                        = [240, 304];
    public const POS_WEAPON                        = [240, 200];
    public const POS_SHIELD                        = [344, 200];
    public const POS_PENDANT                       = [240, 148];
    public const POS_RING_LEFT                     = [240, 252];
    public const POS_RING_RIGHT                    = [344, 252];
    public const POS_EARRING_LEFT                  = [240, 96];
    public const POS_EARRING_RIGHT                 = [344, 96];

    /**
     * getRacesAsArray
     *
     * @param boolean $swap
     * @return array
     */
    public static function getRacesAsArray(bool $swap = false): array
    {
        $array = [
            "human" => self::HUMAN_RACE,
            "dragon" => self::DRAGON_RACE,
            "elf" => self::ELF_RACE,
            "evileyetribe" => self::EVILEYETRIBE_RACE,
            "oni" => self::ONI_RACE,
            "wolfhuman" => self::WOLFHUMAN_RACE,
        ];

        if ($swap) {
            $array = array_flip($array);
        }

        return $array;
    }
}
