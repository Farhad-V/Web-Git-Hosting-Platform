<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RequestObservability
{
    /**
     * Attach a correlation identifier and emit bounded request diagnostics.
     *
     * Request bodies, query strings, cookies, authorization headers,
     * passwords, PAT credentials, and other secret-bearing values are
     * intentionally excluded from the log context.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $requestId = (string) Str::uuid();
        $startedAt = hrtime(true);

        $request->attributes->set(
            'request_id',
            $requestId,
        );

        Log::info(
            'request_started',
            [
                'event' => 'request_started',
                'request_id' => $requestId,
                'method' => $request->method(),
                'path' => $request->path(),
                'actor_state' => $request->user() === null
                    ? 'anonymous'
                    : 'authenticated',
            ],
        );

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            Log::error(
                'request_failed',
                [
                    'event' => 'request_failed',
                    'request_id' => $requestId,
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'route' => $request->route()?->getName(),
                    'user_id' => $request->user()?->getAuthIdentifier(),
                    'result' => 'failure',
                    'failure_category' => 'unhandled_exception',
                    'duration_ms' => $this->durationMilliseconds(
                        $startedAt,
                    ),
                    'exception_class' => $exception::class,
                ],
            );

            throw $exception;
        }

        $statusCode = $response->getStatusCode();

        Log::info(
            'request_completed',
            [
                'event' => 'request_completed',
                'request_id' => $requestId,
                'method' => $request->method(),
                'path' => $request->path(),
                'route' => $request->route()?->getName(),
                'user_id' => $request->user()?->getAuthIdentifier(),
                'status_code' => $statusCode,
                'result' => $statusCode >= 500
                    ? 'failure'
                    : 'completed',
                'duration_ms' => $this->durationMilliseconds(
                    $startedAt,
                ),
            ],
        );

        $response->headers->set(
            'X-Request-ID',
            $requestId,
        );

        return $response;
    }

    /**
     * Convert monotonic elapsed time to milliseconds.
     */
    private function durationMilliseconds(
        int $startedAt,
    ): float {
        return round(
            (hrtime(true) - $startedAt) / 1_000_000,
            3,
        );
    }
}