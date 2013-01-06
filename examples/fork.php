<?php
include_once "../thread.class.php";

Thread::fork(
    function () {
        file_put_contents("test.txt", "");
        sleep(10);
        file_put_contents("test.txt", "testing this");
    }
);