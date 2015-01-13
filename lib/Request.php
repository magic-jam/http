<?php
namespace mj\http;

class Request implements \ArrayAccess, \IteratorAggregate
{
    // public static function rewire(&$array) {
    //     foreach (array_keys($array) as $k) {
    //         if ($k[0] == '@') {
    //             $array[substr($k, 1)] = \Date::from_request($array[$k]);
    //             unset($array[$k]);
    //         } elseif ($k[0] == '$') {
    //             $array[substr($k, 1)] = \Money::from_request($array[$k]);
    //             unset($array[$k]);
    //         } elseif (is_array($array[$k])) {
    //             self::rewire($array[$k]);
    //         }
    //     }
    // }
    
    public static function build_request_from_input() {
        $r = new self;
        
        if (isset($_SERVER['AUTH_TYPE']))       $r->authType = $_SERVER['AUTH_TYPE'];
        if (isset($_SERVER['PHP_AUTH_USER']))   $r->username = $_SERVER['PHP_AUTH_USER'];
        if (isset($_SERVER['PHP_AUTH_PW']))     $r->password = $_SERVER['PHP_AUTH_PW'];
        
        $host = $_SERVER['HTTP_HOST'];
        if ($p = strpos($host, ':')) $host = substr($host, 0, $p);
        
        $path = $_SERVER['REQUEST_URI'];
        if ($p = strpos($path, '?')) $path = substr($path, 0, $p);
        
        $r->host            = $host;
        $r->port            = (int) $_SERVER['SERVER_PORT'];
        $r->path            = $path;
        $r->query           = new Query($_GET);
        $r->queryString    	= $_SERVER['QUERY_STRING'];
        $r->requestURI     	= $_SERVER['REQUEST_URI'];
        
        $r->method          = strtolower($_SERVER['REQUEST_METHOD']);
        $r->isSecure       	= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $r->requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'];
        }
        
        $r->timestamp       = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
        
        $r->clientIP       	= $_SERVER['REMOTE_ADDR'];
        $r->clientPort     	= $_SERVER['REMOTE_PORT'];
        
        $r->params          = $_POST + $_GET; // POST takes precedence
        $r->cookiesArray   	= $_COOKIE;
        
        // self::rewire($r->params);
        
        return $r;
    }
    
    private $params             = array();
    
    private $cookies            = null;
    private $cookiesArray      	= array();
    
    private $authType          	= null;
    private $username           = '';
    private $password           = '';
    
    private $url                = null;
    
    private $host;
    private $port;
    private $path;
    private $query              = null;
    private $queryString       	= null;
    private $requestURI;
    
    private $method;
    private $isSecure;
    private $requestedWith     	= null;
    
    private $timestamp;
    private $time               = null;
    
    private $clientIP;
    private $clientPort;
    
    public function url($force_port = false) {
        if ($this->url === null) {
            $url = $this->isSecure ? 'https://' : 'http://';
            if ($this->username) {
                $url .= $this->username;
                if ($this->password) {
                    $url .= ':' . $this->password;
                }
                $url .= '@';
            }
            $url .= $this->host_and_port($force_port);
            $url .= $this->path;
            if (strlen($this->queryString)) {
                $url .= '?' . $this->queryString;
            }
            $this->url = $url;
        }
        return $this->url;
    }
    
    public function authType() { return $this->authType; }
    public function username() { return $this->username; }
    public function password() { return $this->password; }
    
    public function host() { return $this->host; }
    public function port() { return $this->port; }
    public function canonicalPort() { return $this->isSecure ? 443 : 80; }
    public function path() { return $this->path; }
    public function query() { return $this->query; }
    public function queryString() { return $this->queryString; }
    public function requestURI() { return $this->requestURI; }
    
    public function hostWithPort($forcePort = false) {
        return $this->host . (($this->port != $this->canonicalPort() || $forcePort) ? (':' . $this->port) : '');
    }
    
    public function method() { return $this->method; }
    public function isSecure() { return $this->isSecure; }
    
    public function isGet() { return $this->method == 'get'; }
    public function isPost() { return $this->method == 'post'; }
    public function isPut() { return $this->method == 'put'; }
    public function isDelete() { return $this->method == 'delete'; }
    public function isHead() { return $this->method == 'head'; }
    
    public function isXHR() { return $this->requestedWith == 'xmlhttprequest'; }
    
    public function timestamp() { return $this->timestamp; }

    // TODO(jwf): pick date/time representation
    // public function time() {
    //     if ($this->time === null) $this->time = new \Date_Time($this->timestamp);
    //     return $this->time;
    // }
    
    public function clientIP() { return $this->clientIP; }
    public function clientPort() { return $this->clientPort; }
    
    /**
     * Returns a reference to the request parameters array.
     * Remember also to *assign* by reference if you wish to mutate the source
     * array.
     *
     * @return a reference to the request parameters array.
     */
    public function &params() { return $this->params; }
    
    /**
     * Returns this request's Cookies object.
     *
     * @return Cookies object
     */
    public function cookies() {
        if ($this->cookies === null) {
            $this->cookies = new Cookies($this->cookiesArray);
        }
        return $this->cookies;
    }
    
    /**
     * Returns true if the Cookies object has been initialised.
     *
     * @return true if Cookies have been initialised, false otherwise.
     */
    public function areCookiesInitialised() {
        return $this->cookies !== null;
    }
    
    //
    // A bit hacky - exists so we can merge route parameters
    
    public function mergeParams(array $stuff) {
        foreach ($stuff as $k => $v) $this->params[$k] = $v;
    }
    
    //
    // ArrayAccess/IteratorAggregate
    
    public function offsetExists($offset) { return isset($this->params[$offset]); }
    public function offsetGet($offset) { return $this->params[$offset]; }
    public function offsetSet($offset, $value) { $this->params[$offset] = $value; }
    public function offsetUnset($offset) { unset($this->params[$offset]); }
    
    public function getIterator() { return new \ArrayIterator($this->params); }
}