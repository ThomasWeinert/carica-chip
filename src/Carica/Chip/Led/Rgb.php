<?php

namespace Carica\Chip\Led {

  use Carica\Firmata;
  use Carica\Io;
  use Carica\Io\Event;

  class Rgb {

    use Event\Loop\Aggregation;

    private $_board = NULL;
    private $_pinRed = 0;
    private $_pinGreen = 0;
    private $_pinBlue = 0;

    /**
     * changes per second
     * @var integer
     */
    private $_resolution = 20;
    private $_defer = NULL;

    public function __construct(Firmata\Board $board, $pinRed, $pinGreen, $pinBlue) {
      $this->_board = $board;
      $this->_pinRed = (int)$pinRed;
      $this->_pinGreen = (int)$pinGreen;
      $this->_pinBlue = (int)$pinBlue;
    }

    private function send(array $color) {
      var_dump($color);
      $this->_board->pins[$this->_pinRed]->mode = Firmata\PIN_STATE_PWM;
      $this->_board->pins[$this->_pinGreen]->mode = Firmata\PIN_STATE_PWM;
      $this->_board->pins[$this->_pinBlue]->mode = Firmata\PIN_STATE_PWM;
      $this->_board->pins[$this->_pinRed]->analog = (int)$color[0];
      $this->_board->pins[$this->_pinGreen]->analog = (int)$color[1];
      $this->_board->pins[$this->_pinBlue]->analog = (int)$color[2];
    }

    /**
     * Set the color from an rgb array or and hexadecimal color string
     *
     * @param string|array:integer $color
     */
    public function setColor($color) {
      $this->cancel();
      if (is_string($color)) {
        $color = $this->stringToRgb($color);
      }
      if (is_array($color) && count($color) == 3) {
        $this->send($color);
      }
    }

    /**
     * Return the current color value as an rgb array
     *
     * @return array:integer
     */
    public function getColor() {
      return array(
        $this->_board->pins[$this->_pinRed]->analog,
        $this->_board->pins[$this->_pinGreen]->analog,
        $this->_board->pins[$this->_pinBlue]->analog
      );
    }

    /**
     * Fade the current color to the target color in the given seconds.
     *
     * @param array:integer|string $color
     * @param number $seconds
     * @return Carica\io\Deferred\Promise
     */
    public function fadeTo($color, $milliseconds = 3000) {
      if (is_string($color)) {
        $color = $this->stringToRgb($color);
      }
      if ($milliseconds < 1000) {
        $milliseconds = 1000;
      }
      $steps = floor($milliseconds / 1000 * $this->_resolution);
      $current = $this->getColor();
      $sizes = [
        ($color[0] - $current[0]) / $steps,
        ($color[1] - $current[1]) / $steps,
        ($color[2] - $current[2]) / $steps
      ];
      $this->cancel();
      $step = 0;
      $this->_defer = $defer = new Io\Deferred();
      $timer = $this->loop()->setInterval(
        function () use (&$step, $steps, $current, $color, $sizes, $defer) {
          if ($step < $steps) {
            $newColor = [
              round($current[0] + $sizes[0] * $step),
              round($current[1] + $sizes[1] * $step),
              round($current[2] + $sizes[2] * $step)
            ];
            $this->send($newColor);
            ++$step;
          } else {
            $this->send($color);
            $defer->resolve();
          }
        },
        $this->_resolution
      );
      $defer->always(
        function() use ($timer) {
          $this->loop()->remove($timer);
        }
      );
      return $defer->promise();
    }

    /**
     * Cancel the current animation - reject the deferred object.
     */
    public function cancel() {
      if (isset($this->_defer)) {
        $this->_defer->reject();
        $this->_defer = NULL;
      }
    }

    /**
     * Convert an hexadecimal color string to an rgb array
     * @param string $colorString
     * @return array:integer|NULL
     */
    private function stringToRgb($colorString) {
      if (0 === strpos($colorString, '#')) {
        $colorString = substr($colorString, 1);
      }
      $length = strlen($colorString);
      if ($length == 3) {
        return [
          hexdec(str_repeat($colorString[0], 2)),
          hexdec(str_repeat($colorString[1], 2)),
          hexdec(str_repeat($colorString[2], 2))
        ];
      } elseif ($length == 6) {
        return [
          hexdec($colorString[0].$colorString[1]),
          hexdec($colorString[2].$colorString[3]),
          hexdec($colorString[4].$colorString[5])
        ];
      } else {
        return NULL;
      }
    }
  }
}