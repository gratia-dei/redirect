<?php

class Redirect
{
    private const TESTING_HOST = null;

    private const SECURE_PROTOCOL = 'https';

    private const HTTP_CODE_MOVED_PERMANENTLY = 301;
    private const HTTP_CODE_MOVED_TEMPORARILY = 302;

    private const DOMAIN_GRATIA_DEI = 'gratiadei.org';
    private const DOMAIN_MY_PATRONS = 'mypatrons.org';
    private const DOMAIN_PATRONS_SPACE = 'patrons.space';
    private const DOMAIN_GITHUB_PREFIX = 'github.com/gratia-dei/';

    private const DEFAULT_PROTOCOL = self::SECURE_PROTOCOL;
    private const DEFAULT_SUBDOMAIN = '';
    private const DEFAULT_DOMAIN = self::DOMAIN_GRATIA_DEI;
    private const DEFAULT_PATH = '/';
    private const DEFAULT_HTTP_CODE = self::HTTP_CODE_MOVED_TEMPORARILY;

    private const CONFIG_KEY_PROTOCOL = 'protocol';
    private const CONFIG_KEY_SUBDOMAIN = 'subdomain';
    private const CONFIG_KEY_DOMAIN = 'domain';
    private const CONFIG_KEY_PATH = 'path';
    private const CONFIG_KEY_HTTP_CODE = 'http_code';

    private const DOMAINS_REDIRECT_CONFIG = [
        'gratiadei.org' => [
            self::CONFIG_KEY_PROTOCOL => self::SECURE_PROTOCOL,
            self::CONFIG_KEY_DOMAIN => self::DOMAIN_GITHUB_PREFIX . 'gratia-dei',
            self::CONFIG_KEY_PATH => '',
            self::CONFIG_KEY_HTTP_CODE => self::HTTP_CODE_MOVED_PERMANENTLY,
        ],
        'moipatroni.pl' => [
            self::CONFIG_KEY_PROTOCOL => self::SECURE_PROTOCOL,
            self::CONFIG_KEY_SUBDOMAIN => 'pl',
            self::CONFIG_KEY_DOMAIN => self::DOMAIN_MY_PATRONS,
            self::CONFIG_KEY_HTTP_CODE => self::HTTP_CODE_MOVED_PERMANENTLY,
        ],
        'mypatrons.org' => [
            self::CONFIG_KEY_PROTOCOL => self::SECURE_PROTOCOL,
            self::CONFIG_KEY_DOMAIN => self::DOMAIN_GITHUB_PREFIX . 'my-patrons',
            self::CONFIG_KEY_PATH => '',
            self::CONFIG_KEY_HTTP_CODE => self::HTTP_CODE_MOVED_PERMANENTLY,
        ],
        'mypatrons.pl' => [
            self::CONFIG_KEY_PROTOCOL => self::SECURE_PROTOCOL,
            self::CONFIG_KEY_SUBDOMAIN => 'pl',
            self::CONFIG_KEY_DOMAIN => self::DOMAIN_MY_PATRONS,
            self::CONFIG_KEY_HTTP_CODE => self::HTTP_CODE_MOVED_PERMANENTLY,
        ],
        'patrons.space' => [
            self::CONFIG_KEY_PROTOCOL => self::SECURE_PROTOCOL,
            self::CONFIG_KEY_DOMAIN => self::DOMAIN_GITHUB_PREFIX . 'patrons-space',
            self::CONFIG_KEY_PATH => '',
            self::CONFIG_KEY_HTTP_CODE => self::HTTP_CODE_MOVED_PERMANENTLY,
        ],
    ];

    public function run(): void
    {
        $host = $this->getHost();

        $protocol = $this->getProtocol();
        $subdomain = $this->getSubdomainFromHost($host);
        $domain = $this->getDomainFromHost($host);
        $path = $this->getPath();

        $redirectProtocol = self::DOMAINS_REDIRECT_CONFIG[$domain][self::CONFIG_KEY_PROTOCOL] ?? self::DEFAULT_PROTOCOL;
        $redirectSubdomain = self::DOMAINS_REDIRECT_CONFIG[$domain][self::CONFIG_KEY_SUBDOMAIN] ?? self::DEFAULT_SUBDOMAIN;
        $redirectDomain = self::DOMAINS_REDIRECT_CONFIG[$domain][self::CONFIG_KEY_DOMAIN] ?? self::DEFAULT_DOMAIN;
        $redirectPath = self::DOMAINS_REDIRECT_CONFIG[$domain][self::CONFIG_KEY_PATH] ?? $path;
        $redirectHttpCode = self::DOMAINS_REDIRECT_CONFIG[$domain][self::CONFIG_KEY_HTTP_CODE] ?? self::DEFAULT_HTTP_CODE;

        $this->redirect($redirectProtocol, $redirectSubdomain . '.', $redirectDomain, $redirectPath, $redirectHttpCode);
    }

    private function getHost(): string
    {
        if (!is_null(self::TESTING_HOST)) {
            return self::TESTING_HOST;
        }

        return $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '';
    }

    private function getProtocol(): string
    {
        return $_SERVER['REQUEST_SCHEME'] ?? self::DEFAULT_PROTOCOL;
    }

    private function getSubdomainFromHost(string $host): string
    {
        $domain = $this->getDomainFromHost($host);

        return mb_substr($host, 0, -mb_strlen($domain) - 1);
    }

    private function getDomainFromHost(string $host): string
    {
        //3 parts domains (e.g. abc.com.pl) is to be implemented if will be needed for any new case
        $parts = explode('.', $host);

        return implode('.', array_slice($parts, -2));
    }

    private function getPath(): string
    {
        return $_SERVER['REQUEST_URI'] ?? self::DEFAULT_PATH;
    }

    private function redirect(string $protocol, string $subdomain, string $domain, string $path, int $httpCode): void
    {
        header('Location: ' . $protocol . '://' . ltrim($subdomain, '.') . $domain . $path, true, $httpCode);
    }
}

(new Redirect)->run();
