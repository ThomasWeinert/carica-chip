<?php
$board = require(__DIR__.'/../bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $colors = array(
        '#F00', '#0F0', '#00F'
      );
      $led = new Carica\Chip\Rgb\Led(
        $board->pins[FIRMATA_PINS['RGB_LED_RED']],
        $board->pins[FIRMATA_PINS['RGB_LED_GREEN']],
        $board->pins[FIRMATA_PINS['RGB_LED_BLUE']]
      );
      $led->color('#000');
      $next = function() use ($led, $colors, &$next) {
        static $index = 0;
        if (isset($colors[$index])) {
          $color = $colors[$index];
          $led->fade($color, 1000)->done($next);
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
