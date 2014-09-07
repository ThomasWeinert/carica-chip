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
      $displayCount = 1;
      for ($i = 0; $i < $displayCount; $i++) {
        $max->addDisplay($i, 0);
      }
      $image = imagecreatefrompng(__DIR__.'/elephpant.png');
      $max->scrollX($image)->brightness(0)->on();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();