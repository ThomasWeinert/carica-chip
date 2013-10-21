<?php

namespace Carica\Chip\Led {

  use Carica\Firmata;
  use Carica\Io;

  class Rgb
    implements
      Io\Event\HasLoop {

    use Io\Event\Loop\Aggregation;

    /**
     * @var Firmata\Pin
     */
    private $_pinRed = NULL;
    /**
     * @var Firmata\Pin
     */
    private $_pinGreen = NULL;
    /**
     * @var Firmata\Pin
     */
    private $_pinBlue = NULL;

    /**
     * changes per second
     * @var integer
     */
    private $_resolution = 20;

    /**
     * @var Io\Deferred $_defer
     */
    private $_defer = NULL;

    public function __construct(Firmata\Pin $pinRed, Firmata\Pin $pinGreen, Firmata\Pin $pinBlue) {
      $this->_pinRed = $pinRed;
      $this->_pinGreen = $pinGreen;
      $this->_pinBlue = $pinBlue;
    }

    /**
     * Send data to the board
     *
     * @param array $color
     */
    private function send(array $color) {
      $this->_pinRed->mode = Firmata\Board::PIN_MODE_PWM;
      $this->_pinGreen->mode = Firmata\Board::PIN_MODE_PWM;
      $this->_pinBlue->mode = Firmata\Board::PIN_MODE_PWM;
      $this->_pinRed->analog = $color[0] / 255;
      $this->_pinGreen->analog = $color[1] / 255;
      $this->_pinBlue->analog = $color[2] / 255;
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
        round($this->_pinRed->analog * 255),
        round($this->_pinGreen->analog * 255),
        round($this->_pinBlue->analog * 255)
      );
    }

    /**
     * Fade the current color to the target color in the given seconds.
     *
     * @param array(integer)|string $color
     * @param int $milliseconds
     *
     * @return Io\Deferred\Promise
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