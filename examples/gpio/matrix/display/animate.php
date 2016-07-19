<?php
$board = require(__DIR__.'/../../bootstrap.php');

$loop = Carica\Io\Event\Loop\Factory::get();

$max = new Carica\Chip\Max7219\Matrix\Display(
  new \Carica\Gpio\ShiftOut(
    $board->pins[38],
    $board->pins[40],
    $board->pins[36]
  )
);
$loop->setInterval(
  function () use ($max) {
    static $i = 0;
    $max->setRow($i, FALSE, FALSE);
    if (++$i > 7) {
      $i = 0;
    }
    $max->setRow($i, TRUE, TRUE);
  },
  50
);
$max->brightness(0)->on();

Carica\Io\Event\Loop\Factory::run();