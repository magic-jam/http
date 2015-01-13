<?php
namespace mj\http;

class Query implements \ArrayAccess, \IteratorAggregate
{
    private $params;
    
    public function __construct($query = array()) {
        if (is_array($query)) {
            $this->params = $query;
        } else {
            $this->params = array();
            parse_str($query, $this->params);
        }
    }
    
    public function toString($withQuestionMark = false) {
        $query = http_build_query($this->params);
        if ($withQuestionMark && strlen($query)) {
            return '?' . $query;
        } else {
            return $query;
        }
    }
    
    public function toStringWithTrailingAssignment($key, $withQuestionMark = false) {
        $query = clone $this;
        unset($query[$key]);
        $query[$key] = '';
        return $query->toString($withQuestionMark);
    }
    
    //
    // ArrayAccess/IteratorAggregate
    
    public function offsetExists($offset) { return isset($this->params[$offset]); }
    public function offsetGet($offset) { return $this->params[$offset]; }
    public function offsetSet($offset, $value) { $this->params[$offset] = $value; }
    public function offsetUnset($offset) { unset($this->params[$offset]); }
    
    public function getIterator() { return new \ArrayIterator($this->params); }
}