<?php

namespace dokuwiki\plugin\oauth;

use dokuwiki\HTTP\DokuHTTPClient;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Uri\UriInterface;

function endsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    if( !$length ) {
      return true;
    }
    return substr( $haystack, -$length ) === $needle;
}

/**
 * Implements the client interface using DokuWiki's HTTPClient
 */
class HTTPClient implements ClientInterface
{
    /** @inheritDoc */
    public function retrieveResponse(
        UriInterface $endpoint,
        $requestBody,
        array $extraHeaders = [],
        $method = 'POST'
    ) {
        $http = new DokuHTTPClient();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint->getAbsoluteUri());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (endsWith($endpoint->getAbsoluteUri(), '/connect/token')) {
                $requestBody['grant_type'] = 'client_credentials';
                $extraHeaders[] = 'Content-Type: application/x-www-form-urlencoded';
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestBody, "", "&", PHP_QUERY_RFC3986));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($http->headers, $extraHeaders));
        $response = curl_exec($ch);
        if ($response === true || $response === false) {
            return "";
        } else {
            return $response;
        }
    }
}
