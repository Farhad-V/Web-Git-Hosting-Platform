<?php

namespace App\Http\Middleware;

use App\Exceptions\GitAuthenticationException;
use App\Services\Git\GitCredentialAuthenticator;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateGitHttp
{
    public function __construct(
        private readonly GitCredentialAuthenticator $credentials,
    ) {}

    /**
     * Authenticate a Git client using username and API token.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        try {
            $user = $this->credentials->authenticate($request);
        } catch (GitAuthenticationException) {
            return response(
                content: '',
                status: Response::HTTP_UNAUTHORIZED,
                headers: [
                    'WWW-Authenticate' => (
                        'Basic realm="Git Hosting Platform", charset="UTF-8"'
                    ),
                    'Cache-Control' => 'no-store',
                ],
            );
        }

        Auth::setUser($user);

        $request->setUserResolver(
            static fn () => $user,
        );

        return $next($request);
    }
}
