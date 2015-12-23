<?hh // decl
namespace GuzzleHttp\Psr7;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Returns the string representation of an HTTP message.
 *
 * @param MessageInterface $message Message to convert to a string.
 *
 * @return string
 */
function str(MessageInterface $message): string
{
}

/**
 * Returns a UriInterface for the given value.
 *
 * This function accepts a string or {@see Psr\Http\Message\UriInterface} and
 * returns a UriInterface for the given value. If the value is already a
 * `UriInterface`, it is returned as-is.
 *
 * @param string|UriInterface $uri
 *
 * @return UriInterface
 * @throws \InvalidArgumentException
 */
function uri_for(mixed $uri): UriInterface
{
    if ($uri instanceof UriInterface) {
        return $uri;
    } elseif (is_string($uri)) {
        return new Uri($uri);
    }

    throw new \InvalidArgumentException('URI must be a string or UriInterface');
}

/**
 * Create a new stream based on the input type.
 *
 * Options is an associative array that can contain the following keys:
 * - metadata: Array of custom metadata.
 * - size: Size of the stream.
 *
 * @param resource|string|StreamInterface $resource Entity body data
 * @param array                           $options  Additional options
 *
 * @return Stream
 * @throws \InvalidArgumentException if the $resource arg is not valid.
 */
function stream_for(mixed $resource = '', array<string,mixed> $options = []): Stream
{
}

/**
 * Parse an array of header values containing ";" separated data into an
 * array of associative arrays representing the header key value pair
 * data of the header. When a parameter does not contain a value, but just
 * contains a key, this function will inject a key with a '' string value.
 *
 * @param string|array $header Header to parse into components.
 *
 * @return array Returns the parsed header values.
 */
function parse_header(mixed $header): array<array<string,string>>
{
}

/**
 * Converts an array of header values that may contain comma separated
 * headers into an array of headers with no comma separated values.
 *
 * @param string|array $header Header to normalize.
 *
 * @return array Returns the normalized header field values.
 */
function normalize_header($header): array<array<string>>
{
}

/**
 * Clone and modify a request with the given changes.
 *
 * The changes can be one of:
 * - method: (string) Changes the HTTP method.
 * - set_headers: (array) Sets the given headers.
 * - remove_headers: (array) Remove the given headers.
 * - body: (mixed) Sets the given body.
 * - uri: (UriInterface) Set the URI.
 * - query: (string) Set the query string value of the URI.
 * - version: (string) Set the protocol version.
 *
 * @param RequestInterface $request Request to clone and modify.
 * @param array            $changes Changes to apply.
 *
 * @return RequestInterface
 */
function modify_request(RequestInterface $request, array $changes)
{
    if (!$changes) {
        return $request;
    }

    $headers = $request->getHeaders();

    if (!isset($changes['uri'])) {
        $uri = $request->getUri();
    } else {
        // Remove the host header if one is on the URI
        if ($host = $changes['uri']->getHost()) {
            $changes['set_headers']['Host'] = $host;
        }
        $uri = $changes['uri'];
    }

    if (!empty($changes['remove_headers'])) {
        $headers = _caseless_remove($changes['remove_headers'], $headers);
    }

    if (!empty($changes['set_headers'])) {
        $headers = _caseless_remove(array_keys($changes['set_headers']), $headers);
        $headers = $changes['set_headers'] + $headers;
    }

    if (isset($changes['query'])) {
        $uri = $uri->withQuery($changes['query']);
    }

    return new Request(
        isset($changes['method']) ? $changes['method'] : $request->getMethod(),
        $uri,
        $headers,
        isset($changes['body']) ? $changes['body'] : $request->getBody(),
        isset($changes['version'])
            ? $changes['version']
            : $request->getProtocolVersion()
    );
}

/**
 * Safely opens a PHP stream resource using a filename.
 *
 * When fopen fails, PHP normally raises a warning. This function adds an
 * error handler that checks for errors and throws an exception instead.
 *
 * @param string $filename File to open
 * @param string $mode     Mode used to open the file
 *
 * @return resource
 * @throws \RuntimeException if the file cannot be opened
 */
function try_fopen(string $filename, string $mode): resource
{
}

/**
 * Copy the contents of a stream into a string until the given number of
 * bytes have been read.
 *
 * @param StreamInterface $stream Stream to read
 * @param int             $maxLen Maximum number of bytes to read. Pass -1
 *                                to read the entire stream.
 * @return string
 * @throws \RuntimeException on error.
 */
function copy_to_string(StreamInterface $stream, int $maxLen = -1): string
{
}

/**
 * Copy the contents of a stream into another stream until the given number
 * of bytes have been read.
 *
 * @param StreamInterface $source Stream to read from
 * @param StreamInterface $dest   Stream to write to
 * @param int             $maxLen Maximum number of bytes to read. Pass -1
 *                                to read the entire stream.
 *
 * @throws \RuntimeException on error.
 */
function copy_to_stream(
    StreamInterface $source,
    StreamInterface $dest,
    int $maxLen = -1
): void
{
}

/**
 * Calculate a hash of a Stream
 *
 * @param StreamInterface $stream    Stream to calculate the hash for
 * @param string          $algo      Hash algorithm (e.g. md5, crc32, etc)
 * @param bool            $rawOutput Whether or not to use raw output
 *
 * @return string Returns the hash of the stream
 * @throws \RuntimeException on error.
 */
function hash(
    StreamInterface $stream,
    string $algo,
    bool $rawOutput = false
): string
{
}

/**
 * Read a line from the stream up to the maximum allowed buffer length
 *
 * @param StreamInterface $stream    Stream to read from
 * @param int             $maxLength Maximum buffer length
 *
 * @return string|bool
 */
function readline(StreamInterface $stream, ?int $maxLength = null): string
{
}

/**
 * Parses a request message string into a request object.
 *
 * @param string $message Request message string.
 *
 * @return Request
 */
function parse_request(string $message): Request
{
}

/**
 * Parses a response message string into a response object.
 *
 * @param string $message Response message string.
 *
 * @return Response
 */
function parse_response(string $message): Response
{
}

/**
 * Parse a query string into an associative array.
 *
 * If multiple values are found for the same key, the value of that key
 * value pair will become an array. This function does not parse nested
 * PHP style arrays into an associative array (e.g., foo[a]=1&foo[b]=2 will
 * be parsed into ['foo[a]' => '1', 'foo[b]' => '2']).
 *
 * @param string      $str         Query string to parse
 * @param bool|string $urlEncoding How the query string is encoded
 *
 * @return array
 */
function parse_query(string $str, mixed $urlEncoding = true)
{
}

/**
 * Build a query string from an array of key value pairs.
 *
 * This function can use the return value of parseQuery() to build a query
 * string. This function does not modify the provided keys when an array is
 * encountered (like http_build_query would).
 *
 * @param array     $params   Query string parameters.
 * @param int|false $encoding Set to false to not encode, PHP_QUERY_RFC3986
 *                            to encode using RFC3986, or PHP_QUERY_RFC1738
 *                            to encode using RFC1738.
 * @return string
 */
function build_query(array<arraykey,mixed> $params, mixed $encoding = PHP_QUERY_RFC3986)
{
}

/**
 * Determines the mimetype of a file by looking at its extension.
 *
 * @param $filename
 *
 * @return null|string
 */
function mimetype_from_filename(string $filename): ?string
{
}

/**
 * Maps a file extensions to a mimetype.
 *
 * @param $extension string The file extension.
 *
 * @return string|null
 * @link http://svn.apache.org/repos/asf/httpd/httpd/branches/1.3.x/conf/mime.types
 */
function mimetype_from_extension(string $extension): ?string
{
}
