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

namespace hiro\interfaces;

use hiro\interfaces\HiroInterface;

/**
 * PresenceManagerInterface
 */
interface PresenceManagerInterface
{
    
    /**
      * Constructor function
      *
      * @param HiroInterface discord
      */
    public function __construct(HiroInterface $discord);
    
    /**
      * setPresenceType
      *
      * @param mixed type
      */
    public function setPresenceType($type);
    
    /**
      * addPresence
      *
      * @param string presence
      */
    public function addPresence(string $presence);
    
    /**
      * setPresences
      *
      * @param array presences
      */
    public function setPresences(array $presences);
    
    /**
      * setLoopTime
      *
      * @param mixed type
      */
    public function setLoopTime($time);
    
    /**
      * startThread
      */
    public function startThread();
    
}
