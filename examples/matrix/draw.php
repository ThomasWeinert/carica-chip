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
      $max->addDisplay(0,0);
      $max->addDisplay(1,0);
      $max->addDisplay(2,0);
      $image = imagecreatefrompng(__DIR__.'/elephpant.png');
      $max->draw($image);
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();