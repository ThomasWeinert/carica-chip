<?php
$board = require(__DIR__.'/../bootstrap.php');

use Carica\Io\Network\Http;

$board
  ->activate()
  ->done(
    function () use ($board) {
      $servo = new Carica\Chip\servo($board->pins[3]);

      $route = new Http\Route();
      $route->match(
        '/position/{position}',
        function (Http\Request $request, array $parameters) use ($servo) {
          $servo->moveTo((int)$parameters['position']);
          $response = $request->createResponse();
          $response->content = new Http\Response\Content\String(
            $servo->getPosition(), 'text/plain; charset=utf-8'
          );
          return $response;
        }
      );
      $route->match(
        '/',
        function (Http\Request $request) {
          $response = $request->createResponse();
          $response->content = new Http\Response\Content\File(
            __DIR__.'/index.html', 'text/html; charset=utf-8'
          );
          return $response;
        }
      );
      $route->startsWith(
        '/files',
        new \Carica\Io\Network\Http\Route\Directory(__DIR__)
      );
      $route->match(
        '/',
        new \Carica\Io\Network\Http\Route\File(__DIR__.'/index.html')
      );

      $server = new Carica\Io\Network\Http\Server($route);
      $server->listen();
    }
  );

Carica\Io\Event\Loop\Factory::run();