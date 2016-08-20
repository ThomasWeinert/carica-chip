<?php
use Carica\Gpio\Board;

require(__DIR__.'/../../vendor/autoload.php');

if (!file_exists(__DIR__.'/configuration.php')) {
  echo "Please create a 'configuration.php'. You can copy and rename 'dist.configuration.php'.";
} else {
  include(__DIR__.'/configuration.php');
}

$factory = new Board\Factory();
$configuration = new Board\Configuration(
  [
    'board' => GPIO_BOARD,
    'commands' => GPIO_COMMANDS
  ]
);

return $factory->createBoard($configuration);

