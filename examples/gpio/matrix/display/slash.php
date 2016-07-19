<?php
$board = require(__DIR__.'/../../bootstrap.php');

$max = new Carica\Chip\Max7219\Matrix\Display(
  new \Carica\Gpio\ShiftOut(
    $board->pins[38],
    $board->pins[40],
    $board->pins[36]
  )
);
for ($i = 0; $i < 8; $i++) {
  $max[$i][7 - $i] = TRUE;
}
$max->brightness(0)->on();