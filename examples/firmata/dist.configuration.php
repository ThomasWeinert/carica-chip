<?php

/**
 * The connection mode
 *
 * serial - serial connection
 * tcp - tcp connection (network shield or serproxy)
 *
 * @var string
 */
const CARICA_FIRMATA_MODE = 'tcp';

/**
 * serial connection options *
 */
const CARICA_FIRMATA_SERIAL_DEVICE = '/dev/tty0';

/**
 * tcp connection options *
 */
const CARICA_FIRMATA_TCP_SERVER = '127.0.0.1';
const CARICA_FIRMATA_TCP_PORT = 5330;

const FIRMATA_PINS = [
  'PIN_LED' => 13,
  'RGB_LED_RED' => 9,
  'RGB_LED_GREEN' => 10,
  'RGB_LED_BLUE' => 11
];

