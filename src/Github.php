<?php

namespace Songshenzong\Git;

use HttpX\Tea\Tea;
use GuzzleHttp\Client;
use HttpX\Tea\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Github
 *
 * @package Songshenzong\Git
 */
class Github
{
    protected static $organizationRepositories;

    /**
     * @param string $token
     * @param string $username
     * @param string $repo
     *
     * @return Response
     */
    public static function delete($token, $username, $repo)
    {
        $request = new Request(
            'DELETE',
            "https://api.github.com/repos/$username/$repo",
            [
                'Authorization' => "token $token"
            ]
        );

        return Tea::doPsrRequest(
            $request,
            [
                'connect_timeout' => 35,
                'timeout'         => 30,
                'http_errors'     => false,
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
        $request = new Request(
            'GET',
            "https://api.github.com/repos/$username/$repo",
            [
                'Authorization' => "token $token"
            ]
        );

        $response = Tea::doPsrRequest(
            $request,
            [
                'connect_timeout' => 35,
                'timeout'         => 30,
                'http_errors'     => false,
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
     * @throws GuzzleException
     */
    public static function listOrganizationRepositories($token, $orgs, $page = 1)
    {
        if (!isset(self::$organizationRepositories[$orgs][$page])) {
            $response = (new Client())->request(
                'GET',
                "https://api.github.com/orgs/$orgs/repos?per_page=100&page=$page",
                [
                    'connect_timeout' => 35,
                    'timeout'         => 30,
                    'http_errors'     => false,
                    'headers'         => [
                        'Authorization' => "token $token"
                    ],
                ]
            );

            self::$organizationRepositories[$orgs][$page] = json_decode($response->getBody()->getContents(), true);
        }

        return self::$organizationRepositories[$orgs][$page];
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
}
