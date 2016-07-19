<?php

namespace Carica\Chip\I2C {

  use Carica\Io\Device\I2C;
  use Carica\Io\Deferred;
  use Carica\Io\Event\Emitter;

  /**
   * A MPL115A2 I2C Barometric Pressure = Temperature Sensor
   */
  class ADS1015 {
    
    /**  
     * I2C ADDRESS/BITS
     * 1001 000 (ADDR = GND)
     */
    const ADDRESS = 0x48;
        // 
    /**
     * CONVERSION DELAY (in mS)
     */
    const CONVERSIONDELAY = 1;

    const REG_POINTER_MASK = 0x03;
    const REG_POINTER_CONVERT = 0x00;
    const REG_POINTER_CONFIG = 0x01;
    const REG_POINTER_LOWTHRESH = 0x02;
    const REG_POINTER_HITHRESH = 0x03;

    const REG_CONFIG_OS_MASK = 0x8000;
    const REG_CONFIG_OS_SINGLE = 0x8000; // Write: Set to start a single-conversion
    const REG_CONFIG_OS_BUSY = 0x0000; // Read: Bit = 0 when conversion is in progress
    const REG_CONFIG_OS_NOTBUSY = 0x8000; // Read: Bit = 1 when device is not performing a conversion

    const REG_CONFIG_MUX_MASK = 0x7000;
    const REG_CONFIG_MUX_DIFF_0_1 = 0x0000; // Differential P = AIN0, N = AIN1 (default)
    const REG_CONFIG_MUX_DIFF_0_3 = 0x1000; // Differential P = AIN0, N = AIN3
    const REG_CONFIG_MUX_DIFF_1_3 = 0x2000; // Differential P = AIN1, N = AIN3
    const REG_CONFIG_MUX_DIFF_2_3 = 0x3000; // Differential P = AIN2, N = AIN3
    const REG_CONFIG_MUX_SINGLE_0 = 0x4000; // Single-ended AIN0
    const REG_CONFIG_MUX_SINGLE_1 = 0x5000; // Single-ended AIN1
    const REG_CONFIG_MUX_SINGLE_2 = 0x6000; // Single-ended AIN2
    const REG_CONFIG_MUX_SINGLE_3 = 0x7000; // Single-ended AIN3

    const REG_CONFIG_PGA_MASK = 0x0E00;
    const REG_CONFIG_PGA_6_144V = 0x0000; // +/-6.144V range = Gain 2/3
    const REG_CONFIG_PGA_4_096V = 0x0200; // +/-4.096V range = Gain 1
    const REG_CONFIG_PGA_2_048V = 0x0400; // +/-2.048V range = Gain 2 (default)
    const REG_CONFIG_PGA_1_024V = 0x0600; // +/-1.024V range = Gain 4
    const REG_CONFIG_PGA_0_512V = 0x0800; // +/-0.512V range = Gain 8
    const REG_CONFIG_PGA_0_256V = 0x0A00; // +/-0.256V range = Gain 16

    const REG_CONFIG_MODE_MASK = 0x0100;
    const REG_CONFIG_MODE_CONTIN = 0x0000; // Continuous conversion mode
    const REG_CONFIG_MODE_SINGLE = 0x0100; // Power-down single-shot mode (default)

    const REG_CONFIG_DR_MASK = 0x00E0;
    const REG_CONFIG_DR_128SPS = 0x0000; // 128 samples per second
    const REG_CONFIG_DR_250SPS = 0x0020; // 250 samples per second
    const REG_CONFIG_DR_490SPS = 0x0040; // 490 samples per second
    const REG_CONFIG_DR_920SPS = 0x0060; // 920 samples per second
    const REG_CONFIG_DR_1600SPS = 0x0080; // 1600 samples per second (default)
    const REG_CONFIG_DR_2400SPS = 0x00A0; // 2400 samples per second
    const REG_CONFIG_DR_3300SPS = 0x00C0; // 3300 samples per second

    const REG_CONFIG_CMODE_MASK = 0x0010;
    const REG_CONFIG_CMODE_TRAD = 0x0000; // Traditional comparator with hysteresis (default)
    const REG_CONFIG_CMODE_WINDOW = 0x0010; // Window comparator

    const REG_CONFIG_CPOL_MASK = 0x0008;
    const REG_CONFIG_CPOL_ACTVLOW = 0x0000; // ALERT/RDY pin is low when active (default)
    const REG_CONFIG_CPOL_ACTVHI = 0x0008; // ALERT/RDY pin is high when active

    const REG_CONFIG_CLAT_MASK = 0x0004; // Determines if ALERT/RDY pin latches once asserted
    const REG_CONFIG_CLAT_NONLAT = 0x0000; // Non-latching comparator (default)
    const REG_CONFIG_CLAT_LATCH = 0x0004; // Latching comparator

    const REG_CONFIG_CQUE_MASK = 0x0003;
    const REG_CONFIG_CQUE_1CONV = 0x0000; // Assert ALERT/RDY after one conversions
    const REG_CONFIG_CQUE_2CONV = 0x0001; // Assert ALERT/RDY after two conversions
    const REG_CONFIG_CQUE_4CONV = 0x0002; // Assert ALERT/RDY after four conversions
    const REG_CONFIG_CQUE_NONE = 0x0003; // Disable the comparator and put ALERT/RDY in high state (default)

    /**
     * @var I2C
     */
    private $_i2c;

    /**
     * @var int
     */
    private $_address = self::ADDRESS;

    public function __construct(I2C $i2c) {
      $this->_i2c = $i2c;
    }

    private function writeRegister(register, value) {
      $this->_i2c->write(
        $this->_address,
        [
          register,
          value >> 8,
          value & 0xFF
        ]
      );
    }

    private function readRegister(register) {
      $this->_i2c->write($this->_address, self::REG_POINTER_CONVERT);
      return
        $this->_i2c->read($this->_address, 2)->then(
          function(array $bytes) {
            return ($bytes[0] << 8) | $bytes[1];
          }
        );
    }
  }
}
