<?php
namespace mj\http;

class Headers implements \ArrayAccess, \IteratorAggregate
{
    private $headers = array();

    public function has($key) {
        return isset($this->headers[$key]);
    }
    
    public function get($key, $default = null) {
        return isset($this->headers[$key]) ? $this->headers[$key][0] : $default;
    }

    public function getAll($key) {
        return isset($this->headers[$key]) ? $this->headers[$key] : array();
    }
    
    public function add($key, $value) {
        $this->headers[$key][] = $value;
    }

    public function set($key, $value) {
        $this->remove($key);
        $this->add($key, $value);
    }
    
    public function remove($key) {
        if (isset($this->headers[$key])) unset($this->headers[$key]);
    }
    
    //
    // ArrayAccess
    
    public function offsetExists($key) { return $this->has($key); }
    public function offsetGet($key) { return $this->get($key); }
    public function offsetSet($key, $value) { return $this->set($key, $value); }
    public function offsetUnset($key) { return $this->remove($key); }
    
    //
    // IteratorAggregate
    
    // TODO(jwf): fix this, shouldn't generate strings
    public function getIterator() {
        $out = array();
        foreach ($this->headers as $key => $headers) {
            foreach ($headers as $header) {
                $out[] = "$key: $header\n";
            }
        }
        return new \ArrayIterator($out);
    }
}