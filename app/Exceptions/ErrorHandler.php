<?php

namespace App\Exceptions;

use Closure;
use GraphQL\Error\Error;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Nuwave\Lighthouse\Execution\ExtensionErrorHandler;

class ErrorHandler extends ExtensionErrorHandler
{
    public static function handle(Error $error, Closure $next): array
    {
        $underlyingException = $error->getPrevious();
        if ($underlyingException instanceof MyException) {
            return [
                'code' => $underlyingException->errorCode,
                'message' => $underlyingException->message,
                'detail' => $underlyingException
            ];
        }

        if ($underlyingException instanceof RendersErrorsExtensions) {
            // Reconstruct the error, passing in the extensions of the underlying exception
            $error = new Error(
                $error->message,
                $error->nodes,
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                $underlyingException,
                $underlyingException->extensionsContent()
            );
        }
        return $next($error);
    }
}
