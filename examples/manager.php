<?php
include_once('../src/unusorin/Threads/ThreadSignals.php');
include_once('../src/unusorin/Threads/Thread.php');
include_once('../src/unusorin/Threads/ThreadsManager.php');
include_once('../src/unusorin/Threads/ThreadController.php');
include_once('../src/unusorin/Threads/ThreadPackage.php');


$manager = new \unusorin\Threads\ThreadsManager();

$threads = $manager->getRunningThreads();
foreach ($threads as $name => $pid) {
    $thread = $manager->getThreadByName($name);
    break;
}

$thread->sendMessage('Hello my thread. How are you ?');
$thread->resume();