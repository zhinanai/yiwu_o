<?php
namespace Org\JpushApi\src\Exceptions\JPush;

class JPushException extends \Exception {

    function __construct($message) {
        parent::__construct($message);
    }
}
