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

namespace hiro\parts\monster;

use hiro\interfaces\GeneratorReturn;

/**
 * Monster Base Class
 */
abstract class Monster implements GeneratorReturn {

    /**
     * $health
     */
    public int $health;

    /**
     * $damage
     */
    public int $damage;

    /**
     * $type
     */
    public string $type;

    /**
     * $image
     */
    public string $image;

    /**
     * $xp
     */
    public int $xp;

    /**
     * setHealth
     * 
     * @var int $health
     */
    public function setHealth(int $health): void
    {
        $this->health = $health;
    }

    /**
     * setDamage
     * 
     * @var int $damage
     */
    public function setDamage(int $damage): void
    {
        $this->damage = $damage;
    }

    /**
     * setType
     * 
     * @var string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * setImage
     * 
     * @var string $image
     */
    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * setXp
     * 
     * @var int $xp
     */
    public function setXp(int $xp): void
    {
        $this->xp = $xp;
    }

    /**
     * getHealth
     * 
     * @return int
     */
    public function getHealth(): int
    {
        return $this->health;
    }   

    /**
     * getDamage
     * 
     * @return int
     */
    public function getDamage(): int
    {
        return $this->damage;
    }

    /**
     * getType
     * 
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * getImage
     * 
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * getName
     * 
     * @return string
     */
    public function getName(): string
    {
        $namespace = explode('\\', $this::class);
        return end($namespace);
    }

    /**
     * getXp
     * 
     * @return int
     */
    public function getXp(): int
    {
        return $this->xp;
    }
    
}