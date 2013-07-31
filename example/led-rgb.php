<?php
$board = require(__DIR__.'/bootstrap.php');

$board
  ->activate()
  ->done(
    function () use ($board) {
      $led = new Carica\Chip\Led\Rgb($board, 10, 11, 9);
      $led->setColor('#000');
      $led
        ->fadeTo('#F00')
        ->done(
          function () use ($led) {
            $led
              ->fadeTo('#0F0')
              ->done(
                function () use ($led) {
                  $led
                    ->fadeTo('#00F');
                }
              );
          }
        );
    }
  )
  ->fail(
    function ($error) {
      echo $error, "\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();