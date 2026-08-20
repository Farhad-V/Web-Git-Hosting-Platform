<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class CsrfSecurityTest extends TestCase
{
    public function test_state_changing_session_request_without_csrf_token_is_rejected(): void
    {
        /*
         * Laravel intentionally bypasses CSRF validation while the
         * application is running automated tests. For this security test,
         * use a middleware instance that preserves all production behavior
         * except the test-environment bypass.
         */
        $middleware = new class(
            $this->app,
            $this->app['encrypter'],
        ) extends VerifyCsrfToken {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };

        $session = $this->app['session']->driver();

        $session->start();

        $session->put(
            '_token',
            'expected-csrf-token',
        );

        $request = Request::create(
            '/api/v1/repositories',
            'POST',
            [],
        );

        $request->setLaravelSession(
            $session,
        );

        $this->expectException(
            TokenMismatchException::class,
        );

        $middleware->handle(
            $request,
            static fn (Request $request) => response()->json([
                'status' => 'unexpected-success',
            ]),
        );
    }

    public function test_state_changing_session_request_with_valid_csrf_token_is_accepted(): void
    {
        $middleware = new class(
            $this->app,
            $this->app['encrypter'],
        ) extends VerifyCsrfToken {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };

        $session = $this->app['session']->driver();

        $session->start();

        $session->put(
            '_token',
            'expected-csrf-token',
        );

        $request = Request::create(
            '/api/v1/repositories',
            'POST',
            [
                '_token' => 'expected-csrf-token',
            ],
        );

        $request->setLaravelSession(
            $session,
        );

        $response = $middleware->handle(
            $request,
            static fn (Request $request) => response()->json([
                'status' => 'accepted',
            ]),
        );

        $this->assertSame(
            200,
            $response->getStatusCode(),
        );

        $this->assertSame(
            'accepted',
            $response->getData(
                true,
            )['status'],
        );
    }
}