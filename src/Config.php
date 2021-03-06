<?php

namespace Psalm\Shepherd;

abstract class Config
{
    /** @var ?string */
    public $gh_enterprise_url;

    /** @var null|Config\Custom|Config\OAuthApp */
    private static $config;

    /**
     * @var array{dsn: string, user: string, password: string}
     */
    public $mysql;

    /** @return Config\Custom|Config\OAuthApp */
    public static function getInstance() : Config
    {
        if (self::$config) {
            return self::$config;
        }

        $config_path = __DIR__ . '/../config.json';

        if (!file_exists($config_path)) {
            throw new \UnexpectedValueException('Missing config');
        }

        /**
         * @var array{
         *     oauth_app?: array{
         *         client_id: string,
         *         client_secret: string
         *     },
         *     custom?: array{
         *         personal_token: string
         *     },
         *     host?: string,
         *     mysql: array{dsn: string, user: string, password: string}
         * }
         */
        $config = json_decode(file_get_contents($config_path), true);

        if (isset($config['custom']['personal_token'])) {
            return self::$config = new Config\Custom(
                $config['custom']['personal_token'],
                $config['custom']['webhook_secret'] ?? null,
                $config['gh_enterprise_url'] ?? null,
                $config['mysql']
            );
        }

        if (isset($config['oauth_app']['client_id']) && isset($config['oauth_app']['client_secret'])) {
            return self::$config = new Config\OAuthApp(
                $config['oauth_app']['client_id'],
                $config['oauth_app']['client_secret'],
                $config['gh_enterprise_url'] ?? null,
                $config['oauth_app']['public_access_oauth_token'] ?? null,
                $config['mysql']
            );
        }

        throw new \UnexpectedValueException('Invalid config');
    }
}