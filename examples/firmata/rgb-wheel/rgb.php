<?php
/** @var Carica\Firmata\Board $board */
$board = require(__DIR__.'/../bootstrap.php');

use Carica\Io;
use Carica\Firmata;
use Carica\Io\Network\Http;

$board
  ->activate()
  ->done(
    function () use ($board) {
      $led = new Carica\Chip\Rgb\Led(
        $board->pins[FIRMATA_PINS['RGB_LED_RED']],
        $board->pins[FIRMATA_PINS['RGB_LED_GREEN']],
        $board->pins[FIRMATA_PINS['RGB_LED_BLUE']]
      );
      $route = new Http\Route();
      $route->match(
        '/rgb',
        function (Http\Request $request) use ($led) {
          $color = $request->query['color'] ?? '#000';
          $led->color($color)->on();
          $response = $request->createResponse();
          $response->content = new Http\Response\Content\Text(
            'Color: '.$color
          );
          return $response;
        }
      );
      $route->startsWith('/files', new Http\Route\Directory(__DIR__));
      $route->match('/', new Http\Route\File(__DIR__.'/index.html'));

      $server = new Http\Server($route);
      $server->listen(8080);
      echo "Server started on http://localhost:8080.\n";
    }
  )
  ->fail(
    function ($error) {
      echo $error."\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();
