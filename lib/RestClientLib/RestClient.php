<?php

namespace App\RestClientLib;

/**
 * Class RestClient
 *
 * Class for executing RESTful service calls using a fluent interface.
 *
 * @package MikeBrant\RestClientLib
 */
class RestClient
{
    /**
      * Flag to determine if basic authentication is to be used.
     * 
     * @var boolean
     */
    protected $useBasicAuth = false;
    
    /**
     * User Name for HTTP Basic Auth
     * 
     * @var string
     */
    protected $basicAuthUsername = null;
    
    /**
     * Password for HTTP Basic Auth
     *
     * @var string
     */
    protected $basicAuthPassword = null;
    
    /**
     * Flag to determine if SSL is used
     * 
     * @var boolean
     */
    protected $useSsl = false;
   
    /**
     * Flag to determine is we are to run in test mode where host's SSL cert is not verified
     * 
     * @var boolean
     */
    protected $useSslTestMode = false;
    
    /**
     * Integer value representing number of seconds to set for curl timeout option. Defaults to 30 seconds.
     * 
     * @var integer
     */
    protected $timeout = 30;
    
    /**
     * Variable to store remote host name
     * 
     * @var string
     */
    protected $remoteHost = null;
    
    /**
     * Variable to hold setting to determine if redirects are followed
     * 
     * @var boolean
     */
    protected $followRedirects = false;
    
    /**
     * Variable to hold value for maximum number of redirects to follow for cases when redirect are being followed.
     * Default value of 0 will allow for following of unlimited redirects.
     * 
     * @var integer
     */
    protected $maxRedirects = 0;
    
    /**
     * Variable which can hold a URI base for all actions
     * 
     * @var string
     */
    protected $uriBase = '/';
    
    /**
     * Stores curl handle
     *
     * @var mixed
     */
    private $curl = null;
    
    /**
     * Array containing headers to be used for request
     * 
     * @var array
     */
    private $headers = array();
    
    /**
     * Variable to store the request header as sent
     * 
     * @var string
     */
    
    /**
     * Constructor method. Currently there is no instantiation logic.
     *
     * @return void
     */
    public function __construct() {}
    
    /**
     * Method to execute GET on server
     * 
     * @param string $action
     * @return CurlHttpResponse
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function get($action)
    {
        $this->validateAction($action);
        $this->curlSetup();
        $this->setRequestUrl($action);
        curl_setopt($this->curl, CURLOPT_HTTPGET, true);
        return $this->curlExec();
    }

    /**
     * Method to execute HEAD on server
     * 
     * @param string $action
     * @return CurlHttpResponse
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function head($action)
    {
        $this->validateAction($action);
        $this->curlSetup();
        $this->setRequestUrl($action);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
        curl_setopt($this->curl, CURLOPT_NOBODY, true);
        return $this->curlExec();
    }
    
    /**
     * Sets host name of remote server
     * 
     * @param string $host
     * @return RestClient
     * @throws \InvalidArgumentException
     */
    public function setRemoteHost($host)
    {
        if(empty($host)) {
            throw new \InvalidArgumentException('Host name not provided.');
        } else if(!is_string($host)) {
            throw new \InvalidArgumentException('Non-string host name provided.');
        }
        
        // remove any http(s):// at beginning of host name
        $httpsPattern = '#https://#i';
        $httpPattern = '#http://#i';
        if (1 === preg_match($httpsPattern, $host)) {
            // this needs to be SSL request
            $this->setUseSsl(true);
            $host = str_ireplace('https://', '', $host);
        } else if (1 === preg_match($httpPattern, $host)) {
            $host = str_ireplace('http://', '', $host);
        }
        
        // remove trailing slash in host name
        $host = rtrim($host, '/');
        
        // look for common SSL port values in host name to see if SSL is needed
        $portPatterns = array(
            '/:443$/',
            '/:8443$/',
        );
        foreach ($portPatterns as $pattern) {
            if (1 === preg_match($pattern, $host)) {
                $this->setUseSsl(true);
            }
        }
        
        $this->remoteHost = $host;
        
        return $this;
    }
    
    /**
     * Sets URI base for the instance
     * 
     * @param string $uriBase
     * @return RestClient
     * @throws \InvalidArgumentException
     */
    public function setUriBase($uriBase)
    {
        if(empty($uriBase)) {
            throw new \InvalidArgumentException('URI base not provided.');
        } else if(!is_string($uriBase)) {
            throw new \InvalidArgumentException('Non-string URI base provided.');
        }
        
        // make sure we always have forward slash at beginning and end of uriBase
        $uriBase = '/' . ltrim($uriBase, '/');
        $uriBase = rtrim($uriBase, '/') . '/';
        $this->uriBase = $uriBase;
        
        return $this;
    }
    
    /**
     * Sets whether SSL is to be used
     * 
     * @param boolean $value
     * @return RestClient
     * @throws \InvalidArgumentException
     */
    public function setUseSsl($value)
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException('Non-boolean value passed as parameter.');
        }
        $this->useSsl = $value;
        
        return $this;
    }
    
    /**
     * Sets whether SSL Test Mode is to be used
     * 
     * @param boolean $value
     * @return RestClient
     * @throws \InvalidArgumentException
     */
    public function setUseSslTestMode($value)
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException('Non-boolean value passed as parameter.');
        }
        $this->useSslTestMode = $value;
        
        return $this;
    }
    /**
     * Sets basic authentication credentials
     * 
     * @param string $user
     * @param string $password
     * @return RestClient
     * @throws \InvalidArgumentException
     */
    public function setBasicAuthCredentials($user, $password)
    {
        if (empty($user)) {
            throw new \InvalidArgumentException('User name not provided when trying to set basic authentication credentials.');
        }
        if (empty($password)) {
            throw new \InvalidArgumentException('Password not provided when trying to set basic authentication credentials.');
        }
        
        $this->useBasicAuth = true;
        $this->basicAuthUsername = $user;
        $this->basicAuthPassword = $password;
        
        return $this;
    }

    /**
     * Sets HTTP headers from an associative array where key is header name and value is the header value
     * 
     * @param array $headers
     * @return RestClient
     */
    public function setHeaders(array $headers)
    {
        if(empty($headers)) {
            throw new \InvalidArgumentException('Empty array passed when triyng to set headers');
        }
        $this->headers = $headers;
        
        return $this;  
    }
    
    /**
     * Sets maximum timeout for curl requests
     * 
     * @param integer $seconds
     * @return RestClient
     * @throws \InvalidArgumentException
     */
    public function setTimeout($seconds)
    {
        if(!is_integer($seconds) || $seconds < 0) {
            throw new \InvalidArgumentException('A non-negative integer value must be passed when trying to set timeout');
        }
        $this->timeout = $seconds;
        
        return $this;
    }
    
    /**
     * Sets flag on whether to follow 3XX redirects.
     * 
     * @param boolean $follow
     * @return RestClient
     * @throws \InvalidArgumentException
     */
    public function setFollowRedirects($follow)
    {
        if(!is_bool($follow)) {
            throw new \InvalidArgumentException('Non-boolean value passed as parameter.');
        }
        $this->followRedirects = $follow;
        
        return $this;
    }
    
    /**
     * Sets maximum number of redirects to follow. A value of 0 represents no redirect limit. Also sets followRedirects property to true .
     * 
     * @param integer $redirects
     * @return RestClient
     * @throws \InvalidArgumentException
     */
    public function setMaxRedirects($redirects)
    {
        if(!is_integer($redirects) || $redirects < 0) {
            throw new \InvalidArgumentException('A non-negative integer value must be passed when trying to set max redirects.');
        }
        $this->maxRedirects = $redirects;
        $this->setFollowRedirects(true);
        
        return $this;
    }
    
    /**
     * Get remote host setting
     * 
     * @return string
     */
    public function getRemoteHost()
    {
        return $this->remoteHost;
    }
    
    /**
     * Get URI Base setting
     * 
     * @return string
     */
    public function getUriBase()
    {
        return $this->uriBase;
    }
    
    /**
     * Get boolean setting indicating whether SSL is to be used
     * 
     * @return boolean
     */
    public function isUsingSsl()
    {
        return $this->useSsl;
    }
    
    /**
     * Get boolean setting indicating whether SSL test mode is enabled
     * 
     * @return boolean
     */
    public function isUsingSslTestMode()
    {
        return $this->useSslTestMode;
    }
    
    /**
     * Get timeout setting
     * 
     * @return integer
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
    
    /**
     * Get follow redirects setting
     * 
     * @return boolean
     */
    public function isFollowingRedirects()
    {
        return $this->followRedirects;
    }
    
    /**
     * Get max redirects setting
     * 
     * @return integer
     */
    public function getMaxRedirects()
    {
        return $this->maxRedirects;
    }
    
    /**
     * Method to set up curl handle on client
     * 
     * @return void
     * @throws \Exception
     */
    private function curlSetup()
    {
        $this->curl = $this->curlInit();
    }
    
    /**
     * Method to initilize and return a curl handle
     * 
     * @return resource
     * @throws \Exception
     */
    protected function curlInit()
    {
        // initialize curl
        $curl = curl_init();
        if($curl === false) {
            throw new \Exception('curl failed to initialize.');
        }
        // set timeout
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        
        // set basic HTTP authentication settings
        if (true === $this->useBasicAuth) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $this->basicAuthUsername . ':' . $this->basicAuthPassword);
        }
        
        // set headers
        if (!empty($this->headers)) {
            $headers = array();
            foreach ($this->headers as $key=>$val) {
                $headers[] = $key . ': ' . $val;
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        
        // if not in production environment, we want to ignore SSL validation
        if (true === $this->useSsl && true === $this->useSslTestMode) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        
        // set option to add request header information to curl_getinfo output
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        
        // set option to return content body
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        // set redirect options
        if (true === $this->followRedirects) {
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            if ($this->maxRedirects > 0) {
                curl_setopt($curl, CURLOPT_MAXREDIRS, $this->maxRedirects);
            }
        }
        
        return $curl;
    }
    
    /**
     * Method to to teardown curl fixtures at end of request
     * 
     * @return void
     */
    private function curlTeardown()
    {
        $this->curlClose($this->curl);
        $this->curl = null;
    }
    
    /**
     * Method to close curl handle
     *
     * @param resource $curl curl handle
     * @return void
     */
    protected function curlClose($curl)
    {
        curl_close($curl);
    }
    
    /**
     * Method to execute curl call
     * 
     * @return CurlHttpResponse
     * @throws \Exception
     */
    private function curlExec()
    {
        $curlResult = curl_exec($this->curl);
        if($curlResult === false) {
            // our curl call failed for some reason
            $curlError = curl_error($this->curl);
            $this->curlTeardown();
            throw new \Exception('curl call failed with message: "' . $curlError. '"');
        }
        
        // return CurlHttpResponse
        try {
            $response = new CurlHttpResponse($curlResult, curl_getinfo($this->curl));
        } catch (\InvalidArgumentException $e) {
            throw new \Exception(
                'Unable to instantiate CurlHttpResponse. Message: "' . $e->getMessage() . '"',
                $e->getCode(),
                $e
            );
        } finally {
            $this->curlTeardown();
        }
        
        return $response;
    }
    
    /**
     * Method to set the url on curl handle based on passed action
     * 
     * @param string $action
     * @return void
     */
    protected function setRequestUrl($action)
    {
        $url = $this->buildUrl($action);
        curl_setopt($this->curl, CURLOPT_URL, $url);
    }
    
    /**
     * Method to build URL based on class settings and passed action
     * 
     * @param string $action
     * @return string
     */
    protected function buildUrl($action)
    {
        $url = 'http://';
        if (true === $this->useSsl) {
            $url = 'https://';
        }
        $url = $url . $this->remoteHost . $this->uriBase . $action;
        return $url;
    }
    
    /**
     * Method to set data to be sent along with POST/PUT requests
     * 
     * @param mixed $data
     * @return void
     */
    protected function setRequestData($data)
    {
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
    }
    
    /**
     * Method to provide common validation for action parameters
     * 
     * @param string $action
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateAction($action)
    {
        if(!is_string($action)) {
            throw new \InvalidArgumentException('A non-string value was passed for action parameter');
        }
    }
    
    /**
     * Method to provide common validation for data parameters
     * 
     * @param mixed $data
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateData($data)
    {
        if(empty($data)) {
            throw new \InvalidArgumentException('An empty value was passed for data parameter');
        }
    }
}