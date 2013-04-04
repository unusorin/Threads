<?php
/**
 * src/unusorin/Threads/ThreadPackage.php
 * @author Sorin Badea <sorin.badea91@gmail.com>
 */
namespace unusorin\Threads;
/**
 * Class ThreadPackage
 * @package unusorin\Threads
 */
class ThreadPackage extends \stdClass
{
    public $pid;
    public $clientMessage;
    public $clientRead;
    public $threadMessage;
    public $threadRead;
    public $startedAt;

    /**
     * class constructor
     * @param $pid
     */
    public function __construct($pid)
    {
        $this->pid = $pid;
        $this->startedAt = time();
    }
}