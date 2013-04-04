<?php
/**
 * src/unusorin/Threads/ThreadSignals.php
 * @author Sorin Badea <sorin.badea91@gmail.com>
 */
namespace unusorin\Threads;

/**
 * Class ThreadSignals
 * @package unusorin\Threads
 */
final class ThreadSignals
{
    const KILL   = 9;
    const STOP   = SIGUSR1;
    const RESUME = SIGUSR2;

}
