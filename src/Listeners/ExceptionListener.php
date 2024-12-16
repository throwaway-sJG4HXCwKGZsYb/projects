<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // Set the response
        $response = new JsonResponse(
            [
                'message' => $exception->getMessage(),
                'code' =>
                    $exception instanceof HttpExceptionInterface
                        ? $exception->getStatusCode()
                        : 500,
            ],
            200
        );

        $event->allowCustomResponseCode();

        $event->setResponse($response);
    }
}
