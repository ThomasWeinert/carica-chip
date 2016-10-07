<?php

namespace Carica\Chip\I2C {

  use Carica\Io\Device\I2C;
  use Carica\Io\Deferred;
  use Carica\Io\Event\Emitter;

  /**
   * A MPL115A2 I2C Barometric Pressure = Temperature Sensor
   */
  class MPL115A2 {
    
    use \Carica\Io\Event\Loop\Aggregation; 
    
    const ADDRESS = 0x60;
    const REGISTER_PRESSURE_MSB = 0x00;
    const REGISTER_PRESSURE_LSB = 0x01;
    const REGISTER_TEMP_MSB = 0x02;
    const REGISTER_TEMP_LSB = 0x03;
    const REGISTER_A0_COEFF_MSB = 0x04;
    const REGISTER_A0_COEFF_LSB = 0x05;
    const REGISTER_B1_COEFF_MSB = 0x06;
    const REGISTER_B1_COEFF_LSB = 0x07;
    const REGISTER_B2_COEFF_MSB = 0x08;
    const REGISTER_B2_COEFF_LSB = 0x09;
    const REGISTER_C12_COEFF_MSB = 0x0A;
    const REGISTER_C12_COEFF_LSB = 0x0B;
    const REGISTER_STARTCONVERSION = 0x12;
    
    /**
     * @var I2C $_i2c
     */
    private $_i2c = NULL;

    /**
     * @var null|array
     */
    private $_coefficients = NULL;

    /**
     * Create object, store pin and attach events
     *
     * @param I2C $i2c
     */
    public function __construct(I2C $i2c) {
      $this->_i2c = $i2c;
    }
    
    private function readCoefficients() {
      $this->_i2c->write([self::REGISTER_A0_COEFF_MSB]);
      return
        $this
          ->_i2c
          ->read(8)
          ->done(
            function($bytes) {
              $A0 = (($bytes[0] << 8) | $bytes[1]);
              $B1 = 0xFFFF0000 | (($bytes[2] << 8) | $bytes[3]);
              $B2 = 0xFFFF0000 | (($bytes[4] << 8) | $bytes[5]);
              $C12 = (((($bytes[6] << 8) | $bytes[7])) >> 2);
              return $this->_coefficients = [
                'a0' => $A0 / 8.0,
                'b1' => $B1 / 8192.0,
                'b2' => $B2 / 16384.0,
                'c12' => $C12 / 4194304.0
              ];
            }
          )
        ->fail(
          function() {
            return 'Initialization failed';
          }
        );
    }
    
    public function getTemperature() {
      return $this
        ->read()
        ->then(
          function($pressure, $temperature) {
            return $temperature;
          }
        );
    }
    
    public function getPressure() {
      return $this
        ->read()
        ->then(
          function($pressure) {
            return $pressure;
          }
        );
    }
    
    public function read() {
      $defer = new Deferred();
      Deferred::when(
        $this->_coefficients ? $this->_coefficients : $this->readCoefficients()
      )->done(
        function() use ($defer) {
          $this->_i2c->write([self::REGISTER_STARTCONVERSION, 0x00]);
          $this->loop()->setTimeout(
            function() use ($defer) {
              $this->_i2c->write([self::REGISTER_PRESSURE_MSB]);
              $this
                ->_i2c
                ->read(4)
                ->done(
                  function($bytes) use ($defer) {
                    $pressure = (($bytes[0] << 8) | $bytes[1]) >> 6;
                    $temperature = (($bytes[2] << 8) | $bytes[3]) >> 6;

                    $c = $this->_coefficients;
                    $pressureComp =
                      $c['a0'] +
                      ($c['b1'] + $c['c12'] * $temperature) * $pressure +
                      $c['b2'] * $temperature;

                    // Return pressure and temperature as floating point values
                    $defer->resolve(
                      ((65.0 / 1023.0) * $pressureComp) + 50.0,
                      ($temperature - 498.0) / -5.35 +25.0
                    );
                  }
                )
              ->fail(
                function() use ($defer) {
                  $defer->reject('Data read failed');
                }
              );
            },
            5
          );
        }
      );
      $this->loop()->setTimeout(
        function() use ($defer) {
          $defer->reject('Timeout');
        },
        5000
      );
      return $defer;
    }
  }
}