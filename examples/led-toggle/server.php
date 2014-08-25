<?php
$board = require(__DIR__.'/../bootstrap.php');

use Carica\Io\Network\Http;

$board
  ->activate()
  ->done(
    function () use ($board) {
      $led = new Carica\Chip\Led($board->pins[13]);

      $route = new Http\Route();
      $route->match(
        '/switch/{state}',
        function (Http\Request $request, array $parameters) use ($led) {
          if ($parameters['state'] == 'on') {
            $led->on();
            $message = 'ON';
          } else {
            $led->off();
            $message = 'OFF';
          }
          $response = $request->createResponse();
          $response->content = new Http\Response\Content\String(
            $message, 'text/plain; charset=utf-8'
          );
          return $response;
        }
      );
      $route->match('/', new \Carica\Io\Network\Http\Route\File(__DIR__.'/index.html'));

      $server = new Carica\Io\Network\Http\Server($route);
      $server->listen();
    }
  );

Carica\Io\Event\Loop\Factory::run();