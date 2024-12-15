<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // Set the response
        $response = new JsonResponse(
            [
                'message' => $exception->getMessage(),
                'code' => 500,
            ],
            200
        );

        // Set the response on the event
        $event->setResponse($response);
    }
}
