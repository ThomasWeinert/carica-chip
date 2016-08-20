<?php
/** @var \Carica\Gpio\Board $board */
$board = require(__DIR__.'/../../bootstrap.php');
$board->pins->exportAll();

$max = new Carica\Chip\Max7219\Matrix\Display(
  $board->createShiftOut(38, 40, 36)
);
for ($i = 0; $i < 8; $i++) {
  $max[$i][7 - $i] = TRUE;
}
$max->brightness(0)->on();