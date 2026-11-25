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

namespace hiro\parts;

use hiro\interfaces\PresenceManagerInterface;
use hiro\interfaces\HiroInterface;
use Discord\Parts\User\Activity;

class PresenceManager implements PresenceManagerInterface
{
    
    /** Presence List */
    public $presences = [];
    
    /** timer */
    public $looptime;
    
    /** HiroInterface */
    public $discord;
    
    /** current presence */
    public $currentPresence = 0;
    
    /** presence type */
    public $presenceType = Activity::TYPE_WATCHING;
    
    /**
      * Constructor function
      *
      * @param HiroInterface discord
      */
    public function __construct(HiroInterface $discord)
    {
        $this->discord = $discord;
        return $this;
    }
    
    /**
      * setLoopTime
      * 
      * @param int time
      */
    public function setLoopTime($time)
    {
        $this->looptime = $time;
        return $this;
    }
    
    /**
      * addPresence
      *
      * @param string presence
      */
    public function addPresence(string $presence)
    {
        $this->presences[] = $presence;
        return $this;
    }
    
    /**
      * setPresences
      *
      * @param array presences
      */
    public function setPresences(array $presences)
    {
        $this->presences = $presences;
        return $this;
    }
    
    /**
      * setPresenceType
      *
      * @param mixed type
      */
    public function setPresenceType($type)
    {
        $this->presenceType = $type;
        return $this;
    }
    
    /**
      * startThread
      */
    public function startThread()
    {
        $this->changePresence(); // run first presence
        $this->discord->getLoop()->addPeriodicTimer($this->looptime, function(){$this->changePresence();});
        return $this;
    }
    
    /**
      * changePresence
      */
    protected function changePresence()
    {
        if($this->currentPresence > sizeof($this->presences) - 1) $this->currentPresence = 0;
        $act = $this->discord->factory(Activity::class, [
            "name" => $this->presences[$this->currentPresence],
            "type" => $this->presenceType
        ]);
        $this->discord->updatePresence($act, false, 'online');
        $this->currentPresence += 1;
    }
    
}
