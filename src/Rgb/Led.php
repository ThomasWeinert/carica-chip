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
     * @var Io\Deferred
     */
    private $_defer = NULL;

    /**
     * @var object
     */
    private $_timer = NULL;

    /**
     * @var array|NULL
     */
    private $_color = NULL;

    /**
     * @var bool
     */
    private $_isActive = FALSE;

    /**
     * @var array|FALSE color or FALSE
     */
    private $_status = FALSE;

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
     * @return $this
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
      $this->_isActive = TRUE;
      $this->update($this->_status = $this->getColor(1.0));
      return $this;
    }

    /**
     * Switch the led off, update the pin values.
     */
    public function off() {
      $this->_isActive = FALSE;
      $this->update([0, 0, 0]);
      return $this;
    }

    /**
     * Blink the led in the provided interval
     *
     * @param int $duration milliseconds
     * @return self
     */
    public function strobe($duration = 1000) {
      $this->stop();
      $this->_timer = $this
        ->loop()
        ->setInterval(
          function () {
            if ($this->isOn()) {
              if ($this->_status) {
                $this->_status = FALSE;
                $this->update([0,0,0]);
              } else {
                $this->update($this->_status = $this->getColor());
              }
            }
          },
          $duration
        );
      return $this;
    }

    public function pulse($duration = 1000) {
      $this->stop();
      $color = $this->getColor();
      $interval = round($duration / 510);
      if ($interval < 10) {
        $interval = 10;
      }
      $stepCount = round($duration / 2 / $interval);
      $steps = [
        $color[0] / $stepCount,
        $color[1] / $stepCount,
        $color[2] / $stepCount
      ];
      $this->_timer = $this->loop()->setInterval(
        function() use ($color, $steps) {
          static $direction = 1;
          if ($this->isOn()) {
            $counter = 0;
            if (!$this->_status) {
              $this->_status = [0,0,0];
            }
            for ($i = 0; $i < 3; ++$i) {
              $value = $this->_status[$i] + ($steps[$i] * $direction);
              if ($value >= $color[$i]) {
                $this->_status[$i] = $color[$i];
                $counter++;
              } elseif ($value <= 0) {
                $this->_status[$i] = 0.0;
                $counter++;
              } else {
                $this->_status[$i] = $value;
              }
            }
            $this->update($this->_status);
            if ($counter >= 3) {
              $direction *= -1;
            }
          }
        },
        $interval
      );
      return $this;
    }

    /**
     * Fade the current color to the target color in the given seconds.
     *
     * @param array:int|array:float|string $color
     * @param int $duration
     *
     * @return Io\Deferred\Promise
     */
    public function fadeTo($color, $duration = 3000) {
      $this->stop();
      $color = $this->normalizeColor($color);
      if ($duration < 1000) {
        $duration = 1000;
      }
      $this->_active = TRUE;
      $current = $this->getColor();
      $steps = floor($duration / 1000 * $this->_resolution);
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
      if (isset($this->_timer)) {
        $this->loop()->remove($this->_timer);
        $this->_timer = NULL;
      }
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
     * Set the internal color value, the color can be an hexdecimal string in CSS style a number
     * or an array of numbers.
     *
     * If it is a single number, it is used for all color parts.
     *
     * If it is an array, integer values are considered using a range between 0 and 255.
     * Float values use a range from 0.0 to 1.0.
     *
     * @param string|int|float|array $color
     * @throws \UnexpectedValueException
     * @return array:float
     */
    private function normalizeColor($color) {
      if (is_int($color) || is_float($color)) {
        $color = [$color, $color, $color];
      } elseif (is_string($color)) {
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
     * @param float|int $default
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

    /**
     * @param float $default
     * @return array|NULL
     */
    private function getColor($default = 1.0) {
      if (!isset($this->_color)) {
        return [$default, $default, $default];
      } else {
        return $this->_color;
      }
    }
  }
}