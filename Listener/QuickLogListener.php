<?php

namespace Zan\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\PostResponseEvent;

use Zan\CoreBundle\Logging\QuickLogger;

/**
 * Flushes the QuickLogger onKernelTerminate
 */
class QuickLogListener
{
    public function onKernelTerminate(PostResponseEvent $event)
    {
        $token = $event->getResponse()->headers->get("X-Debug-Token");

        QuickLogger::zz_flushLogs($token);
    }
}
