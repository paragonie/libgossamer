<?php

if (class_exists('WP_Http')) {
    return;
}

/**
 * Class WP_Http
 *
 * @method bool block_request(string $uri)
 * @method array request(string $url, string|array $args)
 * @method string|false _get_first_available_transport(array $args, string|null $url = null)
 * @method static void buildCookieHeader(array &$r)
 * @method static object browser_redirect_compatibility($location, $headers, $data, &$options, $original)
 * @method static string chunkTransferDecode(string $body)
 * @method static array|false|object handle_redirects(string $url, array $args, array $response)
 * @method static int|bool is_ip_address(string $maybe_ip)
 * @method static string make_absolute_url(string $maybe_relative_path, string $url)
 * @method static WP_Error normalize_cookies(array $cookies)
 * @method static array|bool parse_url(string $url)
 * @method static array processHeaders(string|array $strResponse, string $url = '')
 * @method static array processResponse(string $strResponse)
 * @method static void validate_redirects($location)
 */
class WP_Http {
    /*
     * Send an HTTP request to a URI.
     *
     * Please note: The only URI that are supported in the HTTP Transport implementation
     * are the HTTP and HTTPS protocols.
     *
     * @since 2.7.0
     *
     * @param string       $url  The request URL.
     * @param string|array $args {
     *     Optional. Array or string of HTTP request arguments.
     *
     *     @type string       $method              Request method. Accepts 'GET', 'POST', 'HEAD', 'PUT', 'DELETE',
     *                                             'TRACE', 'OPTIONS', or 'PATCH'.
     *                                             Some transports technically allow others, but should not be
     *                                             assumed. Default 'GET'.
     *     @type int          $timeout             How long the connection should stay open in seconds. Default 5.
     *     @type int          $redirection         Number of allowed redirects. Not supported by all transports
     *                                             Default 5.
     *     @type string       $httpversion         Version of the HTTP protocol to use. Accepts '1.0' and '1.1'.
     *                                             Default '1.0'.
     *     @type string       $user-agent          User-agent value sent.
     *                                             Default 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ).
     *     @type bool         $reject_unsafe_urls  Whether to pass URLs through wp_http_validate_url().
     *                                             Default false.
     *     @type bool         $blocking            Whether the calling code requires the result of the request.
     *                                             If set to false, the request will be sent to the remote server,
     *                                             and processing returned to the calling code immediately, the caller
     *                                             will know if the request succeeded or failed, but will not receive
     *                                             any response from the remote server. Default true.
     *     @type string|array $headers             Array or string of headers to send with the request.
     *                                             Default empty array.
     *     @type array        $cookies             List of cookies to send with the request. Default empty array.
     *     @type string|array $body                Body to send with the request. Default null.
     *     @type bool         $compress            Whether to compress the $body when sending the request.
     *                                             Default false.
     *     @type bool         $decompress          Whether to decompress a compressed response. If set to false and
     *                                             compressed content is returned in the response anyway, it will
     *                                             need to be separately decompressed. Default true.
     *     @type bool         $sslverify           Whether to verify SSL for the request. Default true.
     *     @type string       $sslcertificates     Absolute path to an SSL certificate .crt file.
     *                                             Default ABSPATH . WPINC . '/certificates/ca-bundle.crt'.
     *     @type bool         $stream              Whether to stream to a file. If set to true and no filename was
     *                                             given, it will be droped it in the WP temp dir and its name will
     *                                             be set using the basename of the URL. Default false.
     *     @type string       $filename            Filename of the file to write to when streaming. $stream must be
     *                                             set to true. Default null.
     *     @type int          $limit_response_size Size in bytes to limit the response to. Default null.
     *
     * }
     * @return array|WP_Error Array containing 'headers', 'body', 'response', 'cookies', 'filename'.
     *                        A WP_Error instance upon error.
     */

    public function get(string $url, $args = [])
    {
        return [
            'headers' => ['Content-Type' => 'text/plain', 'foo' => ['bar', 'baz']],
            'body' => 'stub',
            'response' => ['code' => 200, 'message' => 'OK'],
            'cookies' => [],
            'filename' => ''
        ];
    }
    public function head(string $url, $args = [])
    {
        return [
            'headers' => ['Content-Type' => 'text/plain', 'foo' => ['bar', 'baz']],
            'body' => '',
            'response' => ['code' => 200, 'message' => 'OK'],
            'cookies' => [],
            'filename' => ''
        ];
    }
    public function post(string $url, $args = [])
    {
        return [
            'headers' => ['Content-Type' => 'text/plain', 'foo' => ['bar', 'baz']],
            'body' => 'stub',
            'response' => ['code' => 200, 'message' => 'OK'],
            'cookies' => [],
            'filename' => ''
        ];
    }
}
