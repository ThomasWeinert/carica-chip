<?php

namespace Carica\Chip {

  use Carica\Firmata;
  use Carica\Io;

  /**
   * A DC motor control for up to 3 pins (h bridge). Single pin will allow only forward, backward will
   * trigger an exception. If you provide the direction pins, backward will be possible.
   *
   * @package Carica\Chip
   */
  class Motor {

    use Io\Event\Loop\Aggregation;

    /**
     * @var Firmata\Pin
     */
    private $_speedPin = NULL;

    /**
     * @var Firmata\Pin|NULL
     */
    private $_directionPin = NULL;

    /**
     * @var Firmata\Pin|NULL
     */
    private $_reverseDirectionPin = NULL;

    /**
     * @var float $threshold minimum power needed for starting the motor
     */
    private $_threshold = 0.75;

    /**
     * @param Firmata\Pin $speedPin PWM capable pin for speed
     * @param Firmata\Pin $directionPin direction pin
     * @param Firmata\Pin $reverseDirectionPin reverse direction pin, for H bridge setup
     */
    public function __construct(
      Firmata\Pin $speedPin,
      Firmata\Pin $directionPin = NULL,
      Firmata\Pin $reverseDirectionPin = NULL) {
      $this->_speedPin = $speedPin;
      $this->_directionPin = $directionPin;
      $this->_reverseDirectionPin = $reverseDirectionPin;
    }

    /**
     * Positive values mean forward, negative values backward. Zero stops the motor.
     *
     * @param float|integer $speed the speed as a, float value between -1 and 1
     */
    public function speed($speed) {
      if ($speed > 0) {
        $this->forward($speed);
      } elseif ($speed < 0) {
        $this->backward(abs($speed));
      } else {
        $this->stop();
      }
    }

    /**
     * Activate forward rotation with the given speed.
     *
     * @param float|integer $speed the speed as a, float value between 0 and 1
     */
    public function forward($speed) {
      $this->setPinModes();
      if (NULL !== $this->_directionPin) {
        $this->_directionPin->digital = FALSE;
        if (NULL !== $this->_reverseDirectionPin) {
          $this->_reverseDirectionPin->digital = !$this->_directionPin->digital;
        }
      }
      $this->setPower($speed);
    }

    /**
     * Activate baclward rotation with the given speed.
     *
     * @param float|integer $speed the speed as a, float value between 0 and 1
     *
     * @throws \LogicException
     */
    public function backward($speed) {
      $this->setPinModes();
      if (NULL == $this->_directionPin) {
        throw new \LogicException('No direction pin(s) provided.');
      }
      $this->_directionPin->digital = TRUE;
      if (NULL !== $this->_reverseDirectionPin) {
        $this->_reverseDirectionPin->digital = !$this->_directionPin->digital;
      }
      $this->setPower($speed);
    }

    /**
     * Stop the motor.
     */
    public function stop() {
      $this->setPinModes();
      $this->_speedPin->analog = 0;
      if (NULL !== $this->_directionPin) {
        $this->_directionPin->digital = FALSE;
        if (NULL !== $this->_reverseDirectionPin) {
          $this->_directionPin->digital = FALSE;
        }
      }
    }

    /**
     * Set the modes of all provided pins.
     */
    private function setPinModes() {
      $this->_speedPin->mode = Firmata\Board::PIN_MODE_PWM;
      if (NULL !== $this->_directionPin) {
        $this->_directionPin->mode = Firmata\Board::PIN_MODE_OUTPUT;
      }
      if (NULL !== $this->_reverseDirectionPin) {
        $this->_directionPin->mode = Firmata\Board::PIN_MODE_OUTPUT;
      }
    }

    private function setPower($speed) {
      if ($speed < $this->_threshold) {
        $this->_speedPin->analog = $this->_threshold;
        $this->loop()->setTimeout(
          function() use ($speed) {
            $this->_speedPin->analog = $speed;
          },
          10
        );
      } else {
        $this->_speedPin->analog = $speed;
      }
    }
  }
}
