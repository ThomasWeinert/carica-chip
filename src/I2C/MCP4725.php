<?php

namespace Carica\Chip\I2C {

  use Carica\Io\Device\I2C;
  use Carica\Io\Device\Pin;
  use Carica\Io\Event\Emitter;

  /**
   * A MCP4725 DAC
   *
   * The class implements Carica\Io\Device\Pin and allows it to be treated as an PWM pin.
   * Be aware that it is an analog voltage output. You will need some circuit to
   * convert the voltage into a PWM signal.
   */
  class MCP4725 implements Pin {

    use Emitter\Aggregation;

    const ADDRESS_ONE = 0x62;
    const ADDRESS_TWO = 0x63;
    
    /**
     * @var I2C $_i2c
     */
    private $_i2c = NULL;

    /**
     * @var Pin|boolean $_useAddressTwo
     */
    private $_useAddressTwo = FALSE;

    /**
     * @var float
     */
    private $_value = 0.0;


    /**
     * Create object, store pin and attach events
     *
     * @param I2C $i2c
     * @param Pin|bool $useAddressTwo
     */
    public function __construct(I2C $i2c, $useAddressTwo = FALSE) {
      $this->_i2c = $i2c;
      $this->_useAddressTwo = $useAddressTwo;
    }

    /**
     * @param float $percent voltage fraction value (0 to 1)
     * @param bool $persistent Write value to eeprom
     */
    public function setAnalog($percent, $persistent = FALSE) {
      if ($percent >= 1.0) {
        $percent = 1.0;
      } elseif ($percent <= 0.0) {
        $percent = 1.0;
      }
      $value = (int)floor(4095 * $percent);
      $useAddressTwo = ($this->_useAddressTwo instanceof Pin)
        ? $this->_useAddressTwo->getDigital() : (bool)$this->_useAddressTwo;
      $address = $useAddressTwo ? self::ADDRESS_TWO : self::ADDRESS_ONE;
      $this->_i2c->write(
        $address,
        $persistent 
          ? [0x60, (int)($value / 16), ($value % 16 << 4)] // write eeprom
          : [($value >> 8) & 0x0F, $value & 0xFF] // fast mode
      );
      if ($this->_value != $percent) {
        $this->_value = $percent;
        $this->emitEvent('change', $this);
      }
    }

    /**
     * Return the last set value
     *
     * @return float
     */
    public function getAnalog() {
      return $this->_value;
    }

    /**
     * Get the mode - will always return Pin::MODE_PWM
     *
     * @return int
     */
    public function getMode() {
      return Pin::MODE_PWM;
    }

    /**
     * Set the mode, only Pin::MODE_PWM allowed.
     *
     * @throws \InvalidArgumentException
     * @param int $mode
     */
    public function setMode($mode) {
      if ($mode != Pin::MODE_PWM) {
        throw new \InvalidArgumentException(
          'The MCP4725 dac can only be used as a PWM pin.'
        );
      }
    }

    /**
     * Pin only supports Pin::MODE_PWM
     *
     * @param int $mode
     * @return bool
     */
    public function supports($mode) {
      return $mode == Pin::MODE_PWM;
    }

    /**
     * Return the value as boolean. True if it greater than 0
     *
     * @return bool
     */
    public function getDigital() {
      return $this->_value > 0.0;
    }

    /**
     * Set the value as boolean. True if it greater than 0
     *
     * @param bool $isHigh
     */
    public function setDigital($isHigh) {
      $this->setAnalog($isHigh ? 1.0 : 0.0);
    }

    /**
     * Add an onChange event, triggered by setAnalog()/setDigital() if
     * the value is changed.
     *
     * @param callable $callback
     */
    public function onChange(callable $callback)  {
      $this->events()->on('change', $callback);
    }
  }
}