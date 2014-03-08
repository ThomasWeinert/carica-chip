<?php

namespace Carica\Chip {

  use Carica\Firmata;
  use Carica\Io\Event;

  class Led {

    use Event\Loop\Aggregation;

    /**
     * @var Firmata\Pin
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
    private $_brightness = 1;

    /**
     * @var int
     */
    private $_direction = 1;

    /**
     * Create led object and initalize pin mode
     *
     * @param Firmata\Pin $pin
     */
    public function __construct(Firmata\Pin $pin) {
      $this->_pin = $pin;
      if ($pin->supports(Firmata\Board::PIN_MODE_PWM)) {
        $this->_pin->mode = Firmata\Board::PIN_MODE_PWM;
        if ($brightness = $this->_pin->analog) {
          $this->_brightness = $brightness;
          $this->_on = $this->_brightness > 0;
        } else {
          $this->_on = FALSE;
          $this->_brightness = 1;
        }
        $this->_supportsPwm = TRUE;
      } else {
        $this->_pin->mode = Firmata\Board::PIN_MODE_OUTPUT;
        $this->_on = $this->_pin->digital;
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
          $this->_pin->analog = $this->_brightness;
        } else {
          $this->_pin->digital = TRUE;
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
        $this->_pin->analog = 0;
      } else {
        $this->_pin->digital = FALSE;
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
        $this->_brightness = $brightness / 255;
      } else {
        $this->_brightness = $brightness;
      }
      $this->_pin->analog = $this->_brightness;
      $this->_on = $this->_brightness > 0;
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
              if ($this->_supportsPwm) {
                var_dump($this->_brightness, $this->_pin->analog);
                $this->_pin->analog = $this->_pin->analog > 0 ? 0 : $this->_brightness;
              } else {
                $this->_pin->digital = !$this->_pin->digital;
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
     * @param int $milliseconds
     * @return self
     */
    public function pulse($duration = 1000) {
      if (isset($this->_timer)) {
        $this->loop()->remove($this->_timer);
      }
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
            $this->_pin->analog = $this->_brightness;
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
      }
      return $this;
    }
  }
}