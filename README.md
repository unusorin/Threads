Threads implementation for PHP

Usage:
The thread can be easily started from bash with 

    php FileWithThreadInitialisations.php 

or from php with

    Thread::Start("FileWithThreadInitialisations.php")

This implementation also supports inter process signals to pause/play a thread and inter process comunications
with shared memory blocks.

Also you can use Thread::fork to execute some code as a thread