<?php //-->
/**
 * This file is part of The Socialite Project
 * (c) 2017-2019 Christan Blanquera.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Utility;

use Cradle\Data\DataTrait;
use Cradle\Event\EventTrait;
use Cradle\Helper\InstanceTrait;
use Cradle\Helper\LoopTrait;
use Cradle\Helper\ConditionalTrait;
use Cradle\Profiler\InspectorTrait;
use Cradle\Profiler\LoggerTrait;

use Cradle\Curl\CurlHandler as Curl;

/**
 * An edenized rest pattern
 *
 * Usage:
 * $results = Rest::i('http://info.test.dev', true)
 *     //sets GET or POST parameters
 *     ->setFoo(['bar' => 'zoo'])
 *     //--> /friends
 *     ->friends()
 *     //--> /friends/user/comment/1/like
 *     ->createUserComment(1, 'like');
 *
 * Usage:
 * $results = Rest::i('http://info.test.dev', true)
 *     //sets GET or POST parameters
 *     ->setFoo(['bar' => 'zoo'])
 *     //--> /friends
 *     ->friends()
 *     //--> /friends/user/comment/1/like
 *     ->createUserComment(1, 'like');
 *
 * @vendor   Cradle
 * @package  Framework
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Rest
{
    use DataTrait,
        EventTrait,
        InstanceTrait,
        LoopTrait,
        ConditionalTrait,
        InspectorTrait,
        LoggerTrait;

    /**
     * @const string ENCODE_JSON
     */
    const ENCODE_JSON = 'json';

    /**
     * @const string ENCODE_QUERY
     */
    const ENCODE_QUERY = 'query';

    /**
     * @const string ENCODE_XML
     */
    const ENCODE_XML = 'xml';

    /**
     * @const string ENCODE_RAW
     */
    const ENCODE_RAW = 'raw';

    /**
     * @const string FAIL_HOST
     */
    const FAIL_HOST = 'Host is not defined';

    /**
     * @const string FAIL_DATA
     */
    const FAIL_DATA = '%s does not exist';

    /**
     * @const string FAIL_REQUIRED
     */
    const FAIL_REQUIRED = '%s is required';

    /**
     * @const string FAIL_VALID
     */
    const FAIL_VALID = '%s does not have a valid value';

    /**
     * @const string METHOD_GET
     */
    const METHOD_GET = 'GET';

    /**
     * @const string METHOD_POST
     */
    const METHOD_POST = 'POST';

    /**
     * @const string METHOD_PUT
     */
    const METHOD_PUT = 'PUT';

    /**
     * @const string METHOD_DELETE
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * @var string|null $host
     */
    protected $host = null;

    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * @var array $meta
     */
    protected $meta = [];

    /**
     * @var array $paths
     */
    protected $paths = [];

    /**
     * @var array $headers
     */
    protected $headers = [];

    /**
     * @var bool $host
     */
    protected $metaOnly = false;

    /**
     * @var array $headers
     */
    protected $routes = [];

    /**
     * Processes set, post, put, delete, get, etc.
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        //if it starts with set
        if (strpos($name, 'set') === 0) {
            //determine the separator
            $separator = '_';

            if (isset($args[1]) && is_scalar($args[1])) {
                $separator = (string) $args[1];
            }

            //get the key
            $key = $this->getKey('set', $name, $separator);

            //just set it, we will validate on send
            $this->data[$key] = $args[0];

            return $this;
        }

        //get the path equivilent
        $path = $this->getKey('', $name, '/');

        //based on custom routes
        if (isset($this->routes[$path])) {
            $meta = $this->routes[$path];
            if (is_string($meta)) {
                $meta = $this->routes[$meta];
            }

            $path = $this->getRoute($meta['route'], $args);

            return $this->send($meta['method'], $path, $meta);
        }

        //default actions test
        switch (true) {
            case strpos($name, 'get') === 0:
                $path = $this->getPath('get', $name, $args);
                return $this->send(self::METHOD_GET, $path);
            case strpos($name, 'create') === 0:
                $path = $this->getPath('create', $name, $args);
                return $this->send(self::METHOD_POST, $path);
            case strpos($name, 'post') === 0:
                $path = $this->getPath('post', $name, $args);
                return $this->send(self::METHOD_POST, $path);
            case strpos($name, 'update') === 0:
                $path = $this->getPath('update', $name, $args);
                return $this->send(self::METHOD_POST, $path);
            case strpos($name, 'put') === 0:
                $path = $this->getPath('put', $name, $args);
                return $this->send(self::METHOD_PUT, $path);
            case strpos($name, 'remove') === 0:
                $path = $this->getPath('remove', $name, $args);
                return $this->send(self::METHOD_DELETE, $path);
            case strpos($name, 'delete') === 0:
                $path = $this->getPath('delete', $name, $args);
                return $this->send(self::METHOD_DELETE, $path);
        }

        //if it's a factory method match
        if (count($args) === 0) {
            //add this to the path
            $this->paths[] = $this->getKey('', $name, '/');
            return $this;
        }

        //let the parent handle the rest
        return parent::__call($name, $args);
    }

    /**
     * Sets up the host and if we are in tet mode
     *
     * @param string $host
     * @param bool   $metaOnly
     */
    public function __construct(
        string $host,
        bool $metaOnly = false,
        array $meta = []
    ) {
        $this->host = $host;
        $this->metaOnly = $metaOnly;

        if (!isset($meta['agent'])) {
            $meta['agent'] = null;
        }

        if (!isset($meta['request'])) {
            $meta['request'] = self::ENCODE_QUERY;
        }

        if (!isset($meta['response'])) {
            $meta['response'] = self::ENCODE_JSON;
        }

        $this->meta = $meta;
    }

    /**
     * Add headers into this request
     *
     * @param string|array $key
     * @param string|null  $value
     *
     * @return Rest
     */
    public function addHeader($key, $value = null)
    {
        //if it's an array
        if (is_array($key)) {
            //warning this overwrites existing headers
            $this->headers = $key;
            return $this;
        }

        //if the value is null
        if (is_null($value)) {
            $this->headers[] = $key;
            return $this;
        }

        //else it should be key value
        $this->headers[] = $key . ': ' . $value;
        return $this;
    }

    /**
     * Add custom routing
     * this is normally for classes
     * wishing to extend this class
     * and testing
     *
     * @param string       $path
     * @param string|array $meta
     *
     * @return Rest
     */
    public function addRoute(string $path, $meta)
    {
        $this->routes[$path] = $meta;

        return $this;
    }

    /**
     * Sends off this request to cURL
     *
     * @param string $method
     * @param string $path
     * @param array  $meta
     *
     * @return mixed
     */
    public function send(string $method, string $path, array $meta = [])
    {
        //get the meta data for this url call
        $meta = $this->getMetaData($method, $path, $meta);

        //if in meta mode
        if ($this->metaOnly) {
            //return the meta
            return $meta;
        }

        //extract the meta data
        $url = $meta['url'];
        $data = $meta['post'];
        $agent = $meta['agent'];
        $encode = $meta['encode'];
        $headers = $meta['headers'];

        // send it into curl
        $request = Curl::i()
            ->setUrl($url)
            // sets connection timeout to 10 sec.
            ->setConnectTimeout(10)
            // sets the follow location to true
            ->setFollowLocation(true)
            // set page timeout to 60 sec
            ->setTimeout(60)
            // verifying Host must be boolean
            ->verifyHost(false)
            // verifying Peer must be boolean
            ->verifyPeer(false)
            //if the agent is set
            ->when($agent, function () use ($agent) {
                // set USER_AGENT
                $this->setUserAgent($agent);
            })
            //if there are headers
            ->when(!empty($headers), function () use ($headers) {
                // set headers
                $this->setHeaders($headers);
            })
            //set the custom request
            ->when(
                $method == self::METHOD_PUT || $method == self::METHOD_DELETE,
                function () use ($method) {
                    $this->setCustomRequest($method);
                }
            )
            //when post or put
            ->when(
                $method == self::METHOD_POST || $method == self::METHOD_PUT,
                function () use ($data) {
                    if (empty($data)) {
                        return;
                    }

                    //set the post data
                    $this->setPostFields($data);
                }
            );

        //how should we return the data ?
        switch ($encode) {
            case self::ENCODE_QUERY:
                $response = $request->getQueryResponse(); // get the query response
                break;
            case self::ENCODE_JSON:
                $response = $request->getJsonResponse(); // get the json response
                break;
            case self::ENCODE_XML:
                $response = $request->getSimpleXmlResponse(); // get the xml response
                break;
            case self::ENCODE_RAW:
            default:
                $response = $request->getResponse(); // get the raw response
                break;
        }

        return $response;
    }

    /**
     * Add data into this request
     *
     * @param string|array $key
     * @param string|null  $value
     *
     * @return Rest
     */
    public function set($key, $value = null)
    {
        //if it's an array
        if (is_array($key)) {
            //warning this overwrites existing headers
            $this->data = $key;
            return $this;
        }

        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Returns any problems with the request
     *
     * @param string $method
     * @param string $path
     * @param array  $meta
     * @param array  $data
     *
     * @return string|false
     */
    private function getErrors(string $method, string $path, array $meta, array $data)
    {
        //validate data
        if (!isset($meta['data'])
            || empty($meta['data'])
        ) {
            return false;
        }

        // NOTE: APIs update more frequent
        // than we can update these libraries
        // In this case we should allow for
        // random variables
        // -----------------------------------
        //if they made up a key value
        //foreach ($data as $key => $value) {
        //    if (!isset($meta['data'][$key])) {
        //        //disallow random key/values
        //        return sprintf(self::FAIL_DATA, $key);
        //    }
        //}

        foreach ($meta['data'] as $key => $valid) {
            //normalize validations
            if (!is_array($valid)) {
                $valid = array($valid);
            }

            //loop through validations
            foreach ($valid as $validation) {
                //same here
                if (is_string($validation)) {
                    $validation = array($validation);
                }

                //if it's required
                //and it's not set
                if ($validation[0] === 'required'
                && !isset($data[$key])) {
                    return sprintf(self::FAIL_REQUIRED, $key);
                }

                //else we should check if the field is valid
                if (isset($data[$key]) && !$this->isFieldValid($data[$key], $validation)) {
                    $error = self::FAIL_VALID;

                    if (isset($meta['error'][$key])) {
                        $error = $meta['error'][$key];
                    }

                    return $error;
                }
            }
        }

        return false;
    }

    /**
     * Used by magic methods, this is used to
     * parse out the method name and return
     * the translated meaning
     *
     * @param string $action
     * @param string $method
     * @param string $separator
     *
     * @return string
     */
    private function getKey(string $action, string $method, string $separator = '_')
    {
        //setSomeSample -> post/Some/Sample
        $key = preg_replace("/([A-Z0-9])/", $separator."$1", $method);
        //set/Some/Sample -> /some/sample
        return trim(strtolower(substr($key, strlen($action))), $separator);
    }

    /**
     * Used mainly for testing or passing out the call
     * for further processing
     *
     * @param string $method
     * @param string $path
     * @param array  $meta
     *
     * @return array
     */
    private function getMetaData($method, $path, $meta)
    {
        //if no host
        if (!$this->host) {
            throw new Exception(self::FAIL_HOST);
        }

        $meta = array_merge($this->meta, $meta);

        //variable list
        $host = $this->host;
        $data = $this->data;
        $agent = $meta['agent'];
        $method = strtoupper($method);
        $headers = $this->headers;
        $requestEncode = $meta['request'];
        $responseEncode = $meta['response'];

        //check for errors
        $error = $this->getErrors(
            $method,
            $path,
            $meta,
            $data
        );

        //if there are errors
        if ($error) {
            throw new \Exception($error);
        }

        //if the method is a put or post
        if ($method === 'POST' || $method === 'PUT') {
            //get the url query and post
            list($query, $data) = $this->getQueryAndPost($meta, $data);

            //figure out how to encode it
            switch ($requestEncode) {
                case self::ENCODE_JSON:
                    $data = json_encode($data);
                    break;
                case self::ENCODE_QUERY:
                default:
                    $data = http_build_query($data);
                    break;
            }
        //it's a get or delete
        } else {
            //let the query be the data
            $query = $data;
        }

        //form the url
        $url = $host . $path;

        //if we have a query
        if (!empty($query)) {
            //add it on to the url
            $url .= '?' . http_build_query($query);
        }

        //we are done
        return array(
            'url' => $url,
            'post' => $data,
            'agent' => $agent,
            'method' => $method,
            'encode' => $responseEncode,
            'headers' => $headers);
    }

    /**
     * Returns the compiled path
     *
     * @param string $action
     * @param string $method
     * @param array  $args
     *
     * @return string
     */
    private function getPath($action, $method, $args)
    {
        //first get the key
        $key = $this->getKey($action, $method, '/');

        //add a trailing seperator
        $path = '/' . $key;

        //if there are paths
        if (!empty($this->paths)) {
            //prefix the paths to the path
            $path = '/' . implode('/', $this->paths) . $path;
        }

        //if there are arguments
        if (!empty($args)) {
            //add that too
            $path .= '/' . implode('/', $args);
        }

        return str_replace('//', '/', $path);
    }

    /**
     * Figures out which data is the url query and the post
     *
     * @param string $meta
     * @param array  $data
     *
     * @return array
     */
    private function getQueryAndPost(array $meta, array $data)
    {
        $query = [];
        $fields = [];

        //if we have a query list
        if (isset($meta['query'])) {
            //use that as the fields
            $fields = $meta['query'];
        }

        //loop through the data sent
        foreach ($data as $key => $value) {
            //if it's a field
            if (in_array($key, $fields)) {
                //add that to the query
                $query[$key] = $value;
                //and unset the data
                unset($data[$key]);
            }
        }

        //return both the query and data
        return array($query, $data);
    }

    /**
     * Returns the actual route based on the path pattern
     *
     * @param string $path
     * @param array $args
     *
     * @return string
     */
    private function getRoute(string $path, array $args)
    {
        //replace *'s with each arg
        //if there are more args, add it to the end
        foreach ($args as $arg) {
            if (strpos($path, '*') !== false) {
                $path = preg_replace('/\*/', $arg, $path, 1);
                continue;
            }

            //We don't allow extra args
            //$path .= '/' . $arg;
        }

        return str_replace('//', '/', $path);
    }

    /**
     * Tests a field against many kinds of validations
     *
     * @param string $value
     * @param array  $validation
     *
     * @return bool
     */
    private function isFieldValid($value, $validation)
    {
        switch ($validation[0]) {
            case 'empty':
                return !empty($value);
            case 'one':
                return empty($value) || in_array($value, $validation[1]);
            case 'number':
                return empty($value) || is_numeric($value);
            case 'int':
                return empty($value) || is_int($value);
            case 'float':
                return empty($value) || is_float($value);
            case 'bool':
                return empty($value) || is_bool($value);
            case 'small':
                return empty($value) || (0 <= $value && $value <= 9);
            case 'date':
                return empty($value) || Validator::isDate($value);
            case 'time':
                return empty($value) || Validator::isTime($value);
            case 'email':
                return empty($value) || Validator::isEmail($value);
            case 'json':
                return empty($value) || Validator::isJson($value);
            case 'url':
                return empty($value) || Validator::isUrl($value);
            case 'html':
                return empty($value) || Validator::isHtml($value);
            case 'cc':
                return empty($value) || Validator::isCreditCard($value);
            case 'hex':
                return empty($value) || Validator::isHex($value);
            case 'alphanum':
                return empty($value) || Validator::alphaNumeric($value);
            case 'alphanum-':
                return empty($value) || Validator::alphaNumericHyphen($value);
            case 'alphanum_':
                return empty($value) || Validator::alphaNumericUnderScore($value);
            case 'slug':
            case 'alphanum-_':
                return empty($value) || Validator::alphaNumericLine($value);
            case 'regex':
                return empty($value) || preg_match($validation[1], $value);
            case 'gt':
                return empty($value) || $value > $validation[1];
            case 'gte':
                return empty($value) || $value >= $validation[1];
            case 'lt':
                return empty($value) || $value < $validation[1];
            case 'lte':
                return empty($value) || $value <= $validation[1];
            case 'sgt':
                return empty($value) || strlen($value) > $validation[1];
            case 'sgte':
                return empty($value) || strlen($value) > $validation[1];
            case 'slt':
                return empty($value) || strlen($value) > $validation[1];
            case 'slte':
                return empty($value) || strlen($value) > $validation[1];
        }

        return true;
    }
}
