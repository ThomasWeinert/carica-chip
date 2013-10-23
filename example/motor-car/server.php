<?php
$board = require(__DIR__.'/../bootstrap.php');

use Carica\Io\Network\Http;

$board
  ->activate()
  ->done(
    function () use ($board) {
      $motorLeft = new Carica\Chip\Motor($board->pins[9], $board->pins[2], $board->pins[3]);
      $motorRight = new Carica\Chip\Motor($board->pins[10], $board->pins[7], $board->pins[8]);

      $route = new Http\Route();
      $route->match(
        '/speed',
          function (Http\Request $request) use ($motorLeft, $motorRight) {
            $motorLeft->speed($request->query['left']);
            $motorRight->speed($request->query['right']);
            $response = $request->createResponse();
            $response->content = new Http\Response\Content\String(
              'ok', 'text/plain; charset=utf-8'
            );
            return $response;
          }
      );
      $route->match(
        '/stop',
          function (Http\Request $request) use ($motorLeft, $motorRight) {
            $motorLeft->stop();
            $motorRight->stop();
            $response = $request->createResponse();
            $response->content = new Http\Response\Content\String(
              'stop', 'text/plain; charset=utf-8'
            );
            return $response;
          }
      );
      $route->startsWith(
        '/files',
        new \Carica\Io\Network\Http\Route\File(__DIR__)
      );

      $server = new Carica\Io\Network\Http\Server($route);
      $server->listen();
    }
  );

Carica\Io\Event\Loop\Factory::run();