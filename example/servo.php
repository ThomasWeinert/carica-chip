<?php
$board = require(__DIR__.'/bootstrap.php');

$loop = Carica\Io\Event\Loop\Factory::get();

$board
  ->activate()
  ->done(
    function () use ($board, $loop) {
      $positions = array(
        0, 45, 90, 180
      );
      $servo = new Carica\Chip\Servo($board->pins[7], -180);
      $index = 0;
      $loop->setInterval(
        $next = function () use ($servo, $positions, &$index) {
          if (isset($positions[$index])) {
            $position = $positions[$index];
            $servo->moveTo($position);
            echo $position, " Grad , ", $servo->getPosition(), " Grad\n";
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

$loop->run();