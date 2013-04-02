<?php
include_once('src/unusorin/Threads/ThreadSignals.php');
include_once('src/unusorin/Threads/Thread.php');


class Ceva extends \unusorin\Threads\Thread
{
    public function toRun()
    {
        file_put_contents("ceva.txt", time());
        sleep(1);
    }
}

$ceva = new Ceva();

$ceva->run();