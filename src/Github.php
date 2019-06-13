<?php

namespace Songshenzong\Git;

use HttpX\Tea\Tea;
use HttpX\Tea\Response;
use GuzzleHttp\Psr7\Request;
use Songshenzong\Support\Arrays;

/**
 * Class Github
 *
 * @package Songshenzong\Git
 */
class Github
{
    /**
     * @var array
     */
    protected static $organizationRepositories;

    /**
     * @param string $token
     * @param string $orgs
     * @param array  $repo
     *
     * @return string
     */
    public static function createInOrganization($token, $orgs, array $repo)
    {
        $uri = "https://api.github.com/orgs/$orgs/repos";

        return self::request(
            'POST',
            $uri,
            [
                'headers' => [
                    'Authorization' => "token $token"
                ],
                'body'    => json_encode($repo)
            ]
        );
    }

    /**
     * @param string $token
     * @param string $username
     * @param string $repo
     *
     * @return Response
     */
    public static function delete($token, $username, $repo)
    {
        $uri = "https://api.github.com/repos/$username/$repo";

        return self::request(
            'DELETE',
            $uri,
            [
                'headers' => [
                    'Authorization' => "token $token"
                ],
            ]
        );
    }

    /**
     * @param string $token
     * @param string $username
     * @param string $repo
     *
     * @return bool
     */
    public static function exists($token, $username, $repo)
    {
        $uri = "https://api.github.com/repos/$username/$repo";

        $response = self::request(
            'GET',
            $uri,
            [
                'headers' => [
                    'Authorization' => "token $token"
                ],
            ]
        );

        return $response->getStatusCode() === 200;
    }

    /**
     * List organization repositories
     *
     * @param string $token
     * @param string $orgs
     * @param int    $page
     *
     * @return array
     */
    public static function listOrganizationRepositories($token, $orgs, $page = 1)
    {
        if (!isset(self::$organizationRepositories[$orgs][$page])) {
            self::$organizationRepositories[$orgs][$page] = self::request(
                'GET',
                "https://api.github.com/orgs/$orgs/repos?per_page=100&page=$page",
                [
                    'headers' => [
                        'Authorization' => "token $token"
                    ],
                ]
            );
        }

        return self::$organizationRepositories[$orgs][$page];
    }

    /**
     * @param string $token
     * @param string $orgs
     * @param string $repo
     *
     * @return bool
     */
    public static function existsInOrganization($token, $orgs, $repo)
    {
        $lists1 = self::listOrganizationRepositories($token, $orgs);
        $lists2 = self::listOrganizationRepositories($token, $orgs, 2);
        $lists  = Arrays::merge([$lists1, $lists2]);
        foreach ($lists as $list) {
            if (strtolower($list['name']) === strtolower($repo)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $signature
     * @param string $secret
     * @param string $body
     *
     * @return bool
     */
    public static function checkSignature($signature, $secret, $body)
    {
        list($algo, $hash) = explode('=', $signature, 2);

        return $hash === hash_hmac($algo, $body, $secret);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $config
     *
     * @return Response
     */
    private static function request($method, $uri, $config = [])
    {
        if (!isset($config['connect_timeout'])) {
            $config['connect_timeout'] = 35;
        }

        if (!isset($config['timeout'])) {
            $config['timeout'] = 30;
        }

        if (!isset($config['http_errors'])) {
            $config['http_errors'] = false;
        }

        $request = new Request($method, $uri);

        return Tea::doPsrRequest($request, $config);
    }
}
