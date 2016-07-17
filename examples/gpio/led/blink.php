<?php
$board = require(__DIR__.'/../bootstrap.php');

$led = new Carica\Chip\Led($board->pins[13]);
$led->strobe()->on();

Carica\Io\Event\Loop\Factory::run();