<?php
include_once "../thread.class.php";
$thread = new Thread(9673);

$thread->sendData(array("today" => time()));
$thread->sendSignal(Thread::signalContinue);