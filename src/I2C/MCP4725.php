<?php

namespace Carica\Chip\I2C {

  use Carica\Io\Device\I2C;
  use Carica\Io\Device\Pin;
  use Carica\Io\Event\Emitter;

  /**
   * A MCP4725 DAC
   */
  class MCP4725 {

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
     * @param float $analog voltage fraction value (0 to 1)
     * @param bool $persistent Write value to eeprom
     */
    public function setAnalog($analog, $persistent = FALSE) {
      if ($analog >= 1) {
        $value = 4095;
      } elseif ($analog <= 0) {
        $value = 0;
      } else {
        $value = (int)floor(4095 * $analog); 
      }
      $useAddressTwo = ($this->_useAddressTwo instanceof Pin)
        ? $this->_useAddressTwo->getDigital() : (bool)$this->_useAddressTwo;
      $address = $useAddressTwo ? self::ADDRESS_TWO : self::ADDRESS_ONE;
      $this->_i2c->write(
        $address,
        $persistent 
          ? [0x60, (int)($value / 16), ($value % 16 << 4)] // write eeprom
          : [($value >> 8) & 0x0F, $value & 0xFF] // fast mode
      );
    }
  }
}