<?php
$board = require(__DIR__.'/../bootstrap.php');

$max = new Carica\Chip\Max7219\Matrix(
  new \Carica\Gpio\ShiftOut(
    $board->pins[38],
    $board->pins[40],
    $board->pins[36]
  )
);
$displayCount = 1;
for ($i = 0; $i < $displayCount; $i++) {
  $max->addDisplay($i * 8, 0);
}
$image = imagecreatefrompng(__DIR__.'/elephpant-animation.png');
$max->scrollX($image, -17, 250)->brightness(0.5)->on();

Carica\Io\Event\Loop\Factory::run();