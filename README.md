Carica Chip
===========

License: [The MIT License](http://www.opensource.org/licenses/mit-license.php)

Copyright: 2013 Thomas Weinert <thomas@weinert.info>

Carica Chip provice abstraction for hardware access. It implements classes for physical
devices connected to an Arduino (or compatible microcontroller) running Firmata.

***It's a learning project not a product. Use it at your own risk.***

Basics
------

The repository provides an (hopefully growing) number of classes representing devices like
an led, and potentiometer and so on.

It is inspired by the [Johnny Five project](https://github.com/rwaldron/johnny-five/).

Dependencies
------------

Carica Chip uses Carica Io a non blocking I/O library for PHP. At least PHP 5.4 is needed. The communication is done by Carica Firmata a Firmata client implentation for PHP.

On Windows, [Serproxy](http://www.lspace.nildram.co.uk/freeware.html) should be used to map serial
ports to tcp.

Installation
------------

Carica Chip is avaiable on [Packagist](https://packagist.org/packages/carica/chip). Use Composer to add it as an
dependency into your own projects.

You can use the skeleton project, too:

#!php

composer create-project carica/chip-skeleton path --stability=dev