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
        $board->pins[3],
        $board->pins[5],
        $board->pins[6]
      );
      $route = new Http\Route();
      $route->match(
        '/rgb',
        function (Http\Request $request) use ($led) {
          $color = isset($request->query['color'])
            ? $request->query['color'] : '#000';
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
    }
  )
  ->fail(
    function ($error) {
      echo $error."\n";
    }
  );

Carica\Io\Event\Loop\Factory::run();