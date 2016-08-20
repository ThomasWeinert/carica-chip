<?php

/**
 * The board class name.
 * The board defines the pin header configuration.
 */
define('GPIO_BOARD', \Carica\Gpio\Boards\RaspberryPi\Rev2::class);

/**
 * The commands class name.
 * The commands define the used method to configure the pins. You
 * can use sysfs or gpio-tools (WiringPi).
 */
define('GPIO_COMMANDS', \Carica\Gpio\Sysfs\Commands::class);
