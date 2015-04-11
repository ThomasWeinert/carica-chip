<?php

namespace Carica\Chip\I2C {

  use Carica\Firmata\Rest\Pin;
  use Carica\Io\Event\Emitter;
  use Carica\Firmata\Board;

  /**
   * A MCP4725 DAC
   */
  class MCP4725 {

    use Emitter\Aggregation;

    const ADDRESS_ONE = 0x62;
    const ADDRESS_TWO = 0x63;
    
    /**
     * @var Board $_board
     */
    private $_board = NULL;

    /**
     * @var Pin|boolean $_useAddressTwo
     */
    private $_useAddressTwo = FALSE; 

    /**
     * Create object, store pin and attach events
     *
     * @param Board $board
     * @param Pin|boolean $useAddressTwo
     */
    public function __construct(Board $board, $useAddressTwo = FALSE) {
      $this->_board = $board;
      $this->_useAddressTwo = $useAddressTwo;
    }

    /**
     * @param float $value
     */
    public function setValue($analog) {
      if ($analog >= 1) {
        $value = 4095;
      } elseif ($analog <= 0) {
        $value = 0;
      } else {
        $value = (int)floor(4095 * $analog); 
      }
      $useAddressTwo = ($this->_useAddressTwo instanceof Pin)
        ? $this->_useAddressTwo->digital : (bool)$this->_useAddressTwo;
      $address = $useAddressTwo ? self::ADDRESS_TWO : self::ADDRESS_ONE;
      $this->_board->sendI2CWriteRequest(
        $address, [($value >> 8) & 0x0F, $value & 0xFF]
      );
    }
  }
}