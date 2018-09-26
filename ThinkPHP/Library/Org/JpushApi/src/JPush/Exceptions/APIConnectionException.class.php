<?php
namespace Org\JpushApi\src\Exceptions\JPush;

class APIConnectionException extends JPushException {

    function __toString() {
        return "\n" . __CLASS__ . " -- {$this->message} \n";
    }
}
