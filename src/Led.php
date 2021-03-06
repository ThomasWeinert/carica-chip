<?php

namespace Carica\Chip {

  use Carica\Io;
  use Carica\Io\Deferred;
  use Carica\Io\Event;
  use Carica\Io\Device\Pin;

  /**
   * A class for an single color led. You can switch it on and off, set the
   *  brightness, let it strobe, pulse or fade.
   */
  class Led
    implements
      Io\Event\HasLoop {

    use Event\Loop\Aggregation;

    /**
     * @var Pin
     */
    private $_pin = NULL;
    /**
     * @var object
     */
    private $_timer = NULL;
    /**
     * @var bool
     */
    private $_supportsPwm = FALSE;

    /**
     * @var bool
     */
    private $_on = FALSE;

    /**
     * @var float
     */
    private $_brightness = 1.0;

    /**
     * @var int
     */
    private $_direction = 1.0;

    /**
     * @var Deferred;
     */
    private $_defer;

    /**
     * Create led object and initalize pin mode. If the pin supports PWM
     * it will be used.
     *
     * @param Pin $pin
     */
    public function __construct(Pin $pin) {
      $this->_pin = $pin;
      if ($pin->supports(Pin::MODE_PWM)) {
        $this->_pin->setMode(Pin::MODE_PWM);
        if ($brightness = $this->_pin->getAnalog()) {
          $this->_brightness = $brightness;
          $this->_on = $this->_brightness > 0;
        } else {
          $this->_on = FALSE;
          $this->_brightness = 1;
        }
        $this->_supportsPwm = TRUE;
      } else {
        $this->_pin->setMode(Pin::MODE_OUTPUT);
        $this->_on = $this->_pin->getDigital();
      }
    }

    /**
     * Returns true if the pin is dimmable, aka support PWM
     *
     * @return bool
     */
    public function isDimmable() {
      return $this->_supportsPwm;
    }

    /**
     * Returns true if the pin is on, be aware that if the pin is
     * dimmable, the led might be on but you might not be able to notice
     * the difference.
     *
     * @return bool
     */
    public function isOn() {
      return $this->_on;
    }

    /**
     * Turn the led on. Dimmable leds will use the last brightness value.
     * @return self
     */
    public function on() {
      if (!$this->_timer) {
        if ($this->_supportsPwm) {
          $this->_pin->setAnalog($this->_brightness);
        } else {
          $this->_pin->setDigital(TRUE);
        }
      }
      $this->_on = TRUE;
      return $this;
    }

    /**
     * Turn the led off
     * @return self
     */
    public function off() {
      if ($this->_supportsPwm) {
        $this->_pin->setAnalog(0);
      } else {
        $this->_pin->setDigital(FALSE);
      }
      $this->_on = FALSE;
      return $this;
    }

    /**
     * Turn the led on or off depending on the current state.
     * @return self
     */
    public function toggle() {
      if ($this->isOn()) {
        $this->off();
      } else {
        $this->on();
      }
      return $this;
    }

    /**
     * Turn the led on to a specific brightness. If the value is a float
     * it will be used as percent (0 to 1). If it is an integer it is
     * a analog pin value between 0 and 255.
     *
     * @param int $brightness
     * @return self
     */
    public function brightness($brightness = 0) {
      if (!$this->_supportsPwm) {
        return $this;
      }
      if (!is_float($brightness)) {
        $this->_brightness = (float)$brightness / 255;
      } else {
        $this->_brightness = (float)$brightness;
      }
      $this->_pin->setAnalog($this->_brightness);
      $this->_on = $this->_brightness > 0;
      return $this;
    }

    /**
     * Fade the led to the provided brightness. If the brightness is an int it
     * is considered a byte value (0 to 255). If it is a float it is considered
     * between 0 and 1.
     *
     * If no brightness is last brightness change is continued until the led
     * is fully on or off.
     *
     * @param int|float $brightness
     * @param int $duration
     * @return Deferred\Promise
     */
    public function fade($brightness = NULL, $duration = 1000) {
      if (!$this->_supportsPwm) {
        return $this;
      }
      if (NULL === $brightness) {
        $to = $this->_direction > 0 ? 1.0 : 0.0;
      } elseif (is_float($brightness)) {
        $to = $brightness;
      } else {
        $to = (float)$brightness / 255;
      }
      $this->_brightness = $this->_pin->getAnalog();
      $steps = round(abs($to - $this->_brightness) * 255);
      $this->_defer = $defer = new Deferred();
      if ($steps > 0) {
        $step = 1 / 255 * ($to > $this->_brightness ? 1 : -1);
        $this->_timer = $timer = $this->loop()->setInterval(
          function () use ($to, $step, $defer) {
            $valueAt = ($this->_brightness += $step);
            $this->_pin->setAnalog($valueAt);
            if ($step > 0) {
              if ($valueAt >= $to || $valueAt >= 1) {
                $defer->resolve();
              }
            } else {
              if ($valueAt <= $to || $valueAt <= 0) {
                $defer->resolve();
              }
            }
          },
          $duration / $steps
        );
        $this->_defer->always(
          function() use ($timer) {
            $this->loop()->remove($timer);
          }
        );
      } else {
        $this->_defer->resolve();
      }
      return $this->_defer->promise();
    }

    /**
     * Fade the led from off to the defined brightness.
     *
     * @param int $duration
     * @return Deferred\Promise
     */
    public function fadeIn($duration = 1000) {
      $to = $this->_brightness;
      $this->brightness(0.0);
      return $this->fade($to, $duration);
    }

    /**
     * Fade the led from the current brightness to fully off.
     *
     * @param int $duration
     * @return Deferred\Promise
     */
    public function fadeOut($duration = 1000) {
      return $this->fade(0.0, $duration);
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
              if ($this->_supportsPwm) {
                $this->_pin->setAnalog($this->_pin->getAnalog() > 0 ? 0 : $this->_brightness);
              } else {
                $this->_pin->setDigital(!$this->_pin->getDigital());
              }
            }
          },
          $duration
        );
      return $this;
    }

    /**
     * Pulse the led between the off and the current brightness.
     * If here is no brightness stored, set it to full.
     *
     * @param int $duration
     * @return self
     */
    public function pulse($duration = 1000) {
      $this->stop();
      $steps = 255;
      $to = round($duration / ($steps * 2));
      $step = 1 / $steps;
      $this->_timer = $this->loop()->setInterval(
        function() use ($step) {
          if ($this->isOn()) {
            if ($this->_brightness <= 0) {
              $this->_direction = 1;
            } elseif ($this->_brightness >= 1) {
              $this->_direction = -1;
            }
            $this->_brightness += ($step * $this->_direction);
            $this->_pin->setAnalog($this->_brightness);
          }
        },
        $to
      );
      return $this;
    }

    /**
     * stops the current animation (pulse, blink, ...)
     * @return self
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
  }
}