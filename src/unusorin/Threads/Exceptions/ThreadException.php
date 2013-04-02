<?php
/**
 * src/unusorin/Threads/Exceptions/ThreadException.php
 * @author Sorin Badea <sorin.badea91@gmail.com>
 */
namespace unusorin\Threads\Exceptions;

class ThreadException extends \Exception
{
    const COULD_NOT_FORK = 1;


    public function __construct($message, $status)
    {
        parent::__construct($message, $status);
    }
}
