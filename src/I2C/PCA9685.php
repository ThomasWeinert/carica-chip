<?php

namespace Carica\Chip\I2C {

  use Carica\Chip\I2C\PCA9685\Pin;
  use Carica\Io\Device\I2C;

  class PCA9685 implements \ArrayAccess, \Countable, \IteratorAggregate {

    const ADDRESS = 0x40;
    const MODE1 = 0x00;
    const PRESCALE = 0xFE;

    const LED_ON_LOW = 0x06;

    /**
     * @var I2C $_i2c
     */
    private $_i2c;

    private $_pins = [
      0 => NULL,
      1 => NULL,
      2 => NULL,
      3 => NULL,
      4 => NULL,
      5 => NULL,
      6 => NULL,
      7 => NULL,
      8 => NULL,
      9 => NULL,
      10 => NULL,
      11 => NULL,
      12 => NULL,
      13 => NULL,
      14 => NULL,
      15 => NULL
    ];

    public function __construct(I2C $i2c) {
      $this->_i2c = $i2c;
      $this->_address = self::ADDRESS;
    }

    public function reset() {
      $this->_i2c->write($this->_address, self::MODE1, 0x00);
    }

    public function setPwmFreqency($frequency) {
    }

    public function writeAnalog($pinNumber, $value) {
      if ($value >= 4095) {
        $this->writePwm($pinNumber, $value, 0);
      } else if ($value <= 0) {
        $this->writePwm($pinNumber, 0, $value);
      }
      $this->writePwm($pinNumber, $value, 4095 - $value);
    }

    private function writePwm($pinNumber, $on, $off) {
      $this->_i2c->write(
        $this->_address,
        [
          self::LED_ON_LOW + (4 * $pinNumber),
          $on,
          $on >> 8,
          $off,
          $off >> 8
        ]
      );
    }

    public function offsetExists($offset) {
      return array_key_exists($offset, $this->_pins);
    }

    public function offsetGet($offset) {
      if (!array_key_exists($offset, $this->_pins)) {
        throw new \OutOfBoundsException('Invalid pin index (0-15).');
      }
      if (NULL === $this->_pins[$offset]) {
        $this->_pins[$offset] = new Pin($this, $offset);
      }
      return $this->_pins[$offset];
    }

    public function offsetSet($offset, $value) {
      throw new \LogicException('Pin objects can not be set.');
    }

    public function offsetUnset($offset) {
      throw new \LogicException('Pin objects can not be unset.');
    }

    public function count() {
      return count($this->_pins);
    }

    public function getIterator() {
      return new \ArrayIterator($this->_pins);
    }
  }
}

namespace Carica\Chip\I2C\PCA9685 {

  use Carica\Chip\I2C\PCA9685;
  use Carica\Io\Event\Emitter;

  class Pin implements \Carica\Io\Device\Pin {

    use Emitter\Aggregation;

    private $_controller = NULL;
    private $_pinNumber = 0;
    private $_value = 0.0;


    public function __construct(PCA9685 $controller, $pinNumber) {
      $this->_controller = $controller;
      $this->_pinNumber = $pinNumber;
      $this->_value = 0.0;
    }

    public function setAnalog($percent) {
      if ($percent > 1.0) {
        $percent = 1.0;
      } elseif ($percent < 0.0) {
        $percent = 0.0;
      }
      if ($this->_value != $percent) {
        $this->_value = $percent;
        $this->_controller->writeAnalog($this->_pinNumber, $this->_value * 4095);
        $this->emitEvent('change', $this);
      }
    }

    public function getAnalog() {
      return $this->_value;
    }

    public function setDigital($isHigh) {
      $this->setAnalog($isHigh ? 1.0 : 0.0);
    }

    public function getDigital() {
      return $this->_value > 0.0;
    }

    public function setMode($mode) {
      return ($mode !== self::MODE_PWM);
    }

    public function getMode() {
      return self::MODE_PWM;
    }

    public function supports($mode) {
      return ($mode !== self::MODE_PWM);
    }

    public function onChange(callable $listener) {
      $this->events()->on('change', $listener);
    }
  }
}