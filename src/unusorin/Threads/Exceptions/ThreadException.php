<?php
/**
 * src/unusorin/Threads/Exceptions/ThreadException.php
 * @author Sorin Badea <sorin.badea91@gmail.com>
 */
namespace unusorin\Threads\Exceptions;
/**
 * Class ThreadException
 * @package unusorin\Threads\Exceptions
 */
class ThreadException extends \Exception
{
    const COULD_NOT_FORK = 1;
    const NOT_FOUND_THREAD_FILE = 2;

    /**
     * Exception contructor
     * @param string $message
     * @param int $status
     */
    public function __construct($message, $status)
    {
        //call parent contructor
        parent::__construct($message, $status);
    }
}
