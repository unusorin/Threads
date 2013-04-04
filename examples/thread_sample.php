<?php
include_once('../src/unusorin/Threads/ThreadSignals.php');
include_once('../src/unusorin/Threads/Thread.php');
include_once('../src/unusorin/Threads/ThreadsManager.php');
include_once('../src/unusorin/Threads/ThreadController.php');
include_once('../src/unusorin/Threads/ThreadPackage.php');

use unusorin\Threads\Thread;

class SomeThread extends Thread
{
    public function toRun()
    {
        if ($this->receivedMessage()) {
            file_put_contents('log.txt', "Received message:" . $this->receiveMessage() . "\n");
            $this->sendMessage('Message received');
        } else {
            file_put_contents('log.txt', "No message received\n");
        }
    }
}

$thread = new SomeThread();
$thread->run();