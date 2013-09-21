<?php
$board = require(__DIR__.'/bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $colors = array(
        '#F00', '#0F0', '#00F'
      );
      $led = new Carica\Chip\Led\Rgb(
        $board->pins[20],
        $board->pins[21],
        $board->pins[22]
      );
      $led->setColor('#000');
      $index = 0;
      $next = function() use ($led, $colors, &$index, &$next) {
        if (isset($colors[$index])) {
          $color = $colors[$index];
          $led->fadeTo($color)->done($next);
        }
        if (++$index >= count($colors)) {
          $index = 0;
        }
      };
      $next();
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();