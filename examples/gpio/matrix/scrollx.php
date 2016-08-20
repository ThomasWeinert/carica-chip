<?php
/** @var \Carica\Gpio\Board $board */
$board = require(__DIR__.'/../bootstrap.php');

use \Carica\Chip\Max7219;

$max = new Max7219\Matrix(
  $board->createShiftOut(38, 40, 36)
);
$displayCount = 6;
for ($i = 0; $i < $displayCount; $i++) {
  $max->addDisplay($i * 8, 0, Max7219\Matrix\Display::ROTATION_CCW_270);
}
$image = imagecreatefrompng(__DIR__.'/elephpant-animation.png');
$max->scrollX($image, -17, 100)->brightness(0.5)->on();

Carica\Io\Event\Loop\Factory::run();