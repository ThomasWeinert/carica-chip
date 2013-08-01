<?php
$board = require(__DIR__.'/bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $positions = array(
        0, 45, 90, 180
      );
      $servo = new Carica\Chip\Servo($board, 7, -180);
      $index = 0;
      $loop = Carica\Io\Event\Loop\Factory::get();
      $loop->setInterval(
        $next = function () use ($servo, $positions, &$index) {
          if (isset($positions[$index])) {
            $position = $positions[$index];
            $servo->setDegrees($position);
            echo $position, " Grad , ", $servo->getDegrees(), " Grad\n";
          }
          if (++$index >= count($positions)) {
            $index = 0;
          }
        },
        2000
      );
      $next();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();