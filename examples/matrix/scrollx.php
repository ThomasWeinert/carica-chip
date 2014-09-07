<?php
$board = require(__DIR__.'/../bootstrap.php');

$loop = Carica\Io\Event\Loop\Factory::get();

$board
  ->activate()
  ->done(
    function () use ($board, $loop) {
      $max = new Carica\Chip\Max7219\Matrix(
        $board,
        11,// white, data
        12, // blue, clock
        8 // green, latch
      );
      $displayCount = 4;
      for ($i = 0; $i < $displayCount; $i++) {
        $max->addDisplay($i * 8, 0);
      }
      $image = imagecreatefrompng(__DIR__.'/elephpant-animation.png');
      $max->scrollX($image, -17, 150)->brightness(0.5)->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();