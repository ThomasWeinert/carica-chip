<?php

namespace Carica\Chip\Rgb {

  use Carica\Io;
  use Carica\Firmata\Pin;

  class Led
    implements
      Io\Event\HasLoop {

    use Io\Event\Loop\Aggregation;

    /**
     * @var Pin
     */
    private $_pinRed = NULL;
    /**
     * @var Pin
     */
    private $_pinGreen = NULL;
    /**
     * @var Pin
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

    /**
     * @var array|NULL
     */
    private $_color = NULL;

    /**
     * @var bool
     */
    private $_isActive = FALSE;

    public function __construct(Pin $pinRed, Pin $pinGreen, Pin $pinBlue) {
      $pinRed->mode = Pin::MODE_PWM;
      $pinGreen->mode = Pin::MODE_PWM;
      $pinBlue->mode = Pin::MODE_PWM;
      if ($pinRed->value + $pinGreen->value + $pinBlue->value > 0) {
        $this->_color = [
          $pinRed->analog,
          $pinGreen->analog,
          $pinBlue->analog
        ];
        $this->_isActive = TRUE;
      }
      $this->_pinRed = $pinRed;
      $this->_pinGreen = $pinGreen;
      $this->_pinBlue = $pinBlue;
    }

    /**
     * Set the color from an rgb array or and hexadecimal color string
     *
     * @param string|array:integer|array:float $color
     */
    public function color($color = NULL) {
      if (isset($color)) {
        $this->stop();
        $this->_color = $this->normalizeColor($color);
        if ($this->isOn()) {
          $this->update($this->_color);
        }
      }
      return $this;
    }

    /**
     * @return bool
     */
    public function isOn() {
      return $this->_isActive;
    }

    /**
     * Switch the led on, update the pin values.
     */
    public function on() {
      $this->stop();
      if (!isset($this->_color)) {
        $this->_color = [1.0, 1.0, 1.0];
      }
      $this->_isActive = TRUE;
      $this->update($this->_color);
      return $this;
    }

    /**
     * Switch the led off, update the pin values.
     */
    public function off() {
      $this->stop();
      if (!isset($this->_color)) {
        $this->_color = [1.0, 1.0, 1.0];
      }
      $this->_isActive = TRUE;
      $this->update([0, 0, 0]);
      return $this;
    }

    /**
     * Fade the current color to the target color in the given seconds.
     *
     * @param array:int|array:float|string $color
     * @param int $milliseconds
     *
     * @return Io\Deferred\Promise
     */
    public function fadeTo($color, $milliseconds = 3000) {
      $this->stop();
      $color = $this->normalizeColor($color);
      if ($milliseconds < 1000) {
        $milliseconds = 1000;
      }
      if (!isset($this->_color)) {
        $this->_color = [0, 0, 0];
      }
      $this->_active = TRUE;
      $current = $this->_color;
      $steps = floor($milliseconds / 1000 * $this->_resolution);
      $sizes = [
        ($color[0] - $current[0]) / $steps,
        ($color[1] - $current[1]) / $steps,
        ($color[2] - $current[2]) / $steps
      ];
      $step = 0;
      $this->_defer = $defer = new Io\Deferred();
      $timer = $this->loop()->setInterval(
        function () use (&$step, $steps, $current, $color, $sizes, $defer) {
          if ($step < $steps) {
            $this->_color = [
              $current[0] + $sizes[0] * $step,
              $current[1] + $sizes[1] * $step,
              $current[2] + $sizes[2] * $step
            ];
            $this->update($this->_color);
            ++$step;
          } else {
            $this->update($color);
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
    public function stop() {
      if (isset($this->_defer)) {
        $this->_defer->reject();
        $this->_defer = NULL;
      }
      return $this;
    }

    /**
     * Send color data to the board
     *
     * @param array $color
     */
    private function update($color) {
      $this->_pinRed->analog = $color[0];
      $this->_pinGreen->analog = $color[1];
      $this->_pinBlue->analog = $color[2];
    }

    /**
     * Set the internal color value, the color can be an hexdecimal string in CSS style or and
     * array. If it is an array, integer values are considered using a range between 0 and 255.
     * Float values use a range from 0.0 to 1.0.
     *
     * @param string|array $color
     * @throws \UnexpectedValueException
     * @return array:float
     */
    private function normalizeColor($color) {
      if (is_string($color)) {
        if (0 === strpos($color, '#')) {
          $color = substr($color, 1);
        }
        $length = strlen($color);
        if ($length == 3) {
          $color = [
            $color[0],
            $color[1],
            $color[2]
          ];
        } elseif ($length == 6) {
          $color = [
            hexdec($color[0].$color[1]),
            hexdec($color[2].$color[3]),
            hexdec($color[4].$color[5])
          ];
        } else {
          $color = [];
        }
      }
      if (is_array($color)) {
        return [
          $this->readColorValue($color, ['red', 'r', 0], 0.0),
          $this->readColorValue($color, ['green', 'g', 1], 0.0),
          $this->readColorValue($color, ['blue', 'b', 2], 0.0),
        ];
      }
      throw new \UnexpectedValueException('Ïnvalid color value.');
    }

    /**
     * @param array $array
     * @param array $keys
     * @param int $default
     * @return float
     */
    private function readColorValue(array $array, array $keys, $default = 0.0) {
      $value = $default;
      foreach ($keys as $key) {
        if (isset($array[$key])) {
          $value = $array[$key];
          break;
        }
      }
      if (is_string($value)) {
        return hexdec(
          (strlen($value) < 2) ? str_repeat($value, 2) : $value
        ) / 255;
      } elseif (is_integer($value)) {
        return $value / 255;
      } else {
        return (float)$value;
      }
    }
  }
}