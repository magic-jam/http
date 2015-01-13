<?php
namespace mj\http;

class Exception extends \Exception
{
    private $status;
    
    public function __construct($status, $message = '') {
        parent::__construct($message);
        $this->status = (int) $status;
    }
    
    public function getHTTPStatus() {
        return $this->status;
    }
    
    public function getHTTPStatusString() {
        return Constants::text_for_status($this->status);
    }    
}
