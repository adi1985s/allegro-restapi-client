<?php

/**
 * An object used for fetching, saving and deleting resource objects.
 *
 * @author Ireneusz Kierkowski <ircykk@gmail.com>
 * @copyright 2016 Ireneusz Kierkowski <https://github.com/ircykk>
 * @license http://www.opensource.org/licenses/MIT MIT License
 *
 */
class AllegroRestApi
{
    const REQUEST_GET = 'GET';
    const REQUEST_POST = 'POST';
    const REQUEST_PUT = 'PUT';
    const REQUEST_DELETE = 'DELETE';
    
    /**
     * Whether to use sandbox API or regular API
     * @var bool
     */
    protected $sandbox;

    /**
     * Endpoint for the API
     * @var string
     */
    protected $endponit;

    /**
     * Auth endpoint for thr API
     * @var string
     */
    protected $auth_endponit;

    /**
     * Response language iso code
     * @var string
     */
    protected $langIso = 'EN';

    /**
     * cURL resource handle
     * @var object
     */
    protected $handle;

    /**
     * cURL result
     * @var object
     */
    protected $result;

    /**
     * Constructor
     *
     * @param bool $sandbox         Whether to use sandbox API or regular API
     */
	function __construct($sandbox = false)
	{
        if (!defined('CLIENT_ID') || !CLIENT_ID) {
            throw new Exception('No "Client ID" provided');
        } else if (!defined('CLIENT_SECRET') || !CLIENT_SECRET) {
            throw new Exception('No "Client secret" provided');
        } else if (!defined('API_KEY') || !API_KEY) {
            throw new Exception('No "API Key" provided');
        }

        $this->sandbox = (bool)$sandbox;

        // Set endpoints
        $this->endponit = 'https://'.($this->sandbox ? 'sandbox.' : null).'allegroapi.io/';
        $this->auth_endponit = 'https://ssl.allegro.pl'.($this->sandbox ? '.webapisandbox.pl' : null).'/auth/oauth/';
	}

    /**
     * Makes a request to the API and returns the decoded response body ready
     * for use.
     *
     * Can also return the response object itself if $returnResponse is true
     *
     * @param string      $url            URI to request
     * @param string      $method         HTTP method
     * @param bool        $returnResponse Whether to return the response object
     */
	protected function request(
        $url, 
        $header = array(), 
        $method = self::REQUEST_POST,
        $params = array(),
        $decode = true
    ) {

        $header[] = 'Accept-Language: '.$this->langIso;
  
        $this->handle = curl_init();
        curl_setopt($this->handle, CURLOPT_URL,            $url);
        curl_setopt($this->handle, CURLOPT_HEADER,         0);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->handle, CURLOPT_POST,           1);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS,     $params);
        curl_setopt($this->handle, CURLOPT_HTTPHEADER,     $header);
        // Execute the call
        $this->result = curl_exec($this->handle);

        // Check if decoding of result is required
        if ($decode === true) {
            $this->result = json_decode($this->result);
        }

        $statusCode = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
        $this->handleStatusCode($statusCode);

        return $this->result;
	}

    /**
     * Checks the status code of a response and throws an exception if required
     *
     * @param statusCode http response code
     *
     */
    protected function handleStatusCode($statusCode)
    {
        switch ($statusCode) {
            case 200:
                // OK
            case 201:
                // Created
            case 202:
                // Accepted
            case 204:
                // No Content
                break;
            case 400:
                // Bad Request
                throw new RestApiException(
                    $this->result->error_description,
                    $statusCode
                );
            case 401:
                // Unauthorized
                throw new RestApiException(
                    $this->result->error_description,
                    $statusCode
                );
            case 404:
                // Not Found
                throw new RestApiException(
                    $this->result->error_description,
                    $statusCode
                );
            case 406:
                // Not Acceptable
                throw new RestApiException(
                    $this->result->error_description,
                    $statusCode
                );
            case 422:
                // Unprocessable Entity
            case 503:
                // Service Unavailable
                throw new RestApiException('Service Unavailable', $statusCode);
            default:
                throw new RestApiException('Received unexpected response', $statusCode);
        }
    }

    /**
     * Generate url for authentication
     *
     * @param $resource     API resource
     * @param $params       GET params
     *
     */
    public function getAuthUrl($resource, $params = array()) 
    {
        if($resource)
            return $this->auth_endponit.$resource.'?'.http_build_query($params);
        return false;
    }

    /**
     * Return information about API type
     *
     */
    public function isSandbox()
    {
        return $this->sandbox;
    }
}