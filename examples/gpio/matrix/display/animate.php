<?php
/** @var \Carica\Gpio\Board $board */
$board = require(__DIR__.'/../../bootstrap.php');
$board->pins->exportAll();

$loop = Carica\Io\Event\Loop\Factory::get();

$max = new Carica\Chip\Max7219\Matrix\Display(
  $board->createShiftOut(38, 40, 36)
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