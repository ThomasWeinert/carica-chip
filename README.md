Carica Chip
===========

[![Build Status](https://travis-ci.org/ThomasWeinert/carica-chip.svg?branch=master)](https://travis-ci.org/ThomasWeinert/carica-chip)
[![HHVM Status](http://hhvm.h4cc.de/badge/carica/chip.png)](http://hhvm.h4cc.de/package/carica/chip)
[![License](https://poser.pugx.org/carica/chip/license.svg)](https://packagist.org/packages/carica/chip)
[![Total Downloads](https://poser.pugx.org/carica/chip/downloads.svg)](https://packagist.org/packages/carica/chip)
[![Latest Stable Version](https://poser.pugx.org/carica/chip/v/stable.svg)](https://packagist.org/packages/carica/chip)
[![Latest Unstable Version](https://poser.pugx.org/carica/chip/v/unstable.svg)](https://packagist.org/packages/carica/chip)

License: [The MIT License](http://www.opensource.org/licenses/mit-license.php)

Copyright: 2013-2015 Thomas Weinert <thomas@weinert.info>

Carica Chip provides abstraction for hardware access. It implements classes for physical
devices connected to an Arduino (or compatible microcontroller) running Firmata.

***It's a learning project not a product. Use it at your own risk.***

[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/ThomasWeinert/carica-chip)

Basics
------

The repository provides an (hopefully growing) number of classes representing devices like
an led, and potentiometer and so on.

It is inspired by the [Johnny Five project](https://github.com/rwaldron/johnny-five/).

Dependencies
------------

Carica Chip uses Carica Io a non blocking I/O library for PHP. At least PHP 5.4 is needed.
Carica Io defines several intefaces for hardware devices (Pin, ShiftOut, I2C). An implementation
of these interfaces is required for Carica Chip. 

Currently here is only one implementation - [Carica Firmata](https://github.com/ThomasWeinert/carica-firmata).
This is a [Firmata](http://firmata.org/wiki/Main_Page) client implentation for PHP.

On Windows, [Serproxy](http://www.lspace.nildram.co.uk/freeware.html) should be used to map serial
ports to tcp.

Installation
------------

Carica Chip is avaiable on [Packagist](https://packagist.org/packages/carica/chip). Use Composer to add it as an
dependency into your own projects.

You can use the skeleton project, too:

```
$ composer create-project carica/chip-skeleton path --stability=dev
```

Examples
--------

Examples using Carica Firmata can be found in a 
[separate repository](https://github.com/ThomasWeinert/carica-chip-examples-firmata).
