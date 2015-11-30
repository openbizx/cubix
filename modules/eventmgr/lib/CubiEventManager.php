<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.eventmgr.sample
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id$
 */
use Openbizx\Openbizx;
use Openbizx\Event\EventManagerInterface;
use Openbizx\Event\Event;

/**
 * CubiEventManager is the class that trigger events
 *
 * @package   openbiz.bin
 * @author    Rocky Swen <rocky@phpopenbiz.org>
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @access    public
 */
class CubiEventManager implements EventManagerInterface
{

    protected $eventObsevers;

    public function trigger($event_key, $target, $params)
    {
        $event = new Event($event_key, $target, $params);
        $matchedObservers = $this->getMatchObservers($event_key, $target);
        foreach ($matchedObservers as $observer) {
            $observer->observe($event);
        }
    }

    public function attach($event_key, $observer, $priority = null)
    {
        $this->eventObsevers[$event_key][] = $observer;
    }

    protected function getMatchObservers($event_key, $target)
    {
        if (isset($this->eventObsevers[$event_key])) {
            return $this->eventObsevers[$event_key];
        } else {
            // look up the event_observer table
            $evtobsDOName = "eventmgr.do.EventObserverDO";
            $evtobsDo = Openbizx::getObject($evtobsDOName);
            $event_target = $target->objectName;
            $event_name = $event_key;
            $rs = $evtobsDo->directFetch("[event_target]='$event_target' AND [event_name]='$event_name'");
            $observers = array();
            foreach ($rs as $rec) {
                $observerName = $rec['observer_name'];
                $observers[] = Openbizx::getObject($observerName);
            }
            return $observers;
        }
    }

}
