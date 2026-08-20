<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class DeploymentSecurityTest extends TestCase
{
    private string $projectRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectRoot = dirname(
            base_path(),
        );
    }

    public function test_production_environment_template_uses_secure_defaults(): void
    {
        $environment = $this->readProjectFile(
            'deployment/env/production.env.example',
        );

        $this->assertStringContainsString(
            'APP_ENV=production',
            $environment,
        );

        $this->assertStringContainsString(
            'APP_DEBUG=false',
            $environment,
        );

        $this->assertStringContainsString(
            'APP_URL=https://',
            $environment,
        );

        $this->assertStringContainsString(
            'SESSION_SECURE_COOKIE=true',
            $environment,
        );

        $this->assertStringContainsString(
            'SESSION_HTTP_ONLY=true',
            $environment,
        );

        $this->assertStringContainsString(
            'SESSION_SAME_SITE=lax',
            $environment,
        );

        $this->assertStringContainsString(
            'GITFORGE_GIT_BINARY=/usr/bin/git',
            $environment,
        );

        $this->assertStringContainsString(
            'GITFORGE_REPOSITORY_ROOT=/var/lib/gitforge',
            $environment,
        );

        $this->assertStringContainsString(
            'APP_KEY=',
            $environment,
        );

        $this->assertStringContainsString(
            'DB_PASSWORD=',
            $environment,
        );

        $this->assertStringNotContainsString(
            'APP_DEBUG=true',
            $environment,
        );
    }

    public function test_nginx_profile_enforces_https_and_separates_public_storage(): void
    {
        $nginx = $this->readProjectFile(
            'deployment/nginx/gitforge.conf',
        );

        $this->assertStringContainsString(
            'return 301 https://$host$request_uri;',
            $nginx,
        );

        $this->assertStringContainsString(
            'listen 443 ssl;',
            $nginx,
        );

        $this->assertStringContainsString(
            'root /var/www/gitforge/frontend/dist;',
            $nginx,
        );

        $this->assertStringContainsString(
            '/var/www/gitforge/backend/public/index.php',
            $nginx,
        );

        $this->assertStringContainsString(
            'location ^~ /api/',
            $nginx,
        );

        $this->assertStringContainsString(
            'git-upload-pack',
            $nginx,
        );

        $this->assertStringContainsString(
            'git-receive-pack',
            $nginx,
        );

        $this->assertStringContainsString(
            'fastcgi_request_buffering off;',
            $nginx,
        );

        $this->assertStringContainsString(
            'fastcgi_buffering off;',
            $nginx,
        );

        $this->assertStringContainsString(
            'server_tokens off;',
            $nginx,
        );

        $this->assertStringNotContainsString(
            '/var/lib/gitforge',
            $nginx,
        );

        $this->assertStringNotContainsString(
            'root /var/www/gitforge/backend;',
            $nginx,
        );
    }

    public function test_php_fpm_profile_uses_least_privilege_runtime_identity(): void
    {
        $phpFpm = $this->readProjectFile(
            'deployment/php-fpm/gitforge.conf',
        );

        $this->assertStringContainsString(
            'user = gitforge',
            $phpFpm,
        );

        $this->assertStringContainsString(
            'group = gitforge',
            $phpFpm,
        );

        $this->assertStringContainsString(
            'listen.mode = 0660',
            $phpFpm,
        );

        $this->assertStringContainsString(
            'clear_env = yes',
            $phpFpm,
        );

        $this->assertStringContainsString(
            'php_admin_flag[display_errors] = off',
            $phpFpm,
        );

        $this->assertStringContainsString(
            'security.limit_extensions = .php',
            $phpFpm,
        );

        $this->assertStringNotContainsString(
            'user = root',
            $phpFpm,
        );

        $this->assertStringNotContainsString(
            'group = root',
            $phpFpm,
        );
    }

    public function test_deployment_guide_preserves_security_and_recovery_boundaries(): void
    {
        $guide = $this->readProjectFile(
            'deployment/README.md',
        );

        $this->assertStringContainsString(
            'APP_DEBUG is false',
            $guide,
        );

        $this->assertStringContainsString(
            'Repository storage remains outside all public web roots',
            $guide,
        );

        $this->assertStringContainsString(
            'Normal runtime execution does not require root',
            $guide,
        );

        $this->assertStringContainsString(
            'PostgreSQL metadata',
            $guide,
        );

        $this->assertStringContainsString(
            'persistent Git repository storage',
            $guide,
        );

        $normalizedGuide = preg_replace(
            '/\s+/',
            ' ',
            $guide,
        );

        $this->assertIsString(
            $normalizedGuide,
        );

        $this->assertStringContainsString(
            'must not silently recreate it',
            $normalizedGuide,
        );

        $this->assertStringContainsString(
            'must not automatically adopt it',
            $normalizedGuide,
        );
    }

    private function readProjectFile(
        string $relativePath,
    ): string {
        $path = $this->projectRoot
            .DIRECTORY_SEPARATOR
            .str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                $relativePath,
            );

        $this->assertFileExists(
            $path,
        );

        $content = file_get_contents(
            $path,
        );

        $this->assertIsString(
            $content,
        );

        return $content;
    }
}