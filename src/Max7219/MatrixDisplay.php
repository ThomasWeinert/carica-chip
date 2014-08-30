<?php

namespace Carica\Chip\Max7219 {

  use Carica\Chip\Max7219\MatrixDisplay\Row;
  use Carica\Firmata;
  use Carica\Io\Event;

  class MatrixDisplay
    extends \Carica\Chip\Max7219
    implements \ArrayAccess {

    private $_rows = [];

    public function __construct(
      Firmata\Board $board, $dataPin, $clockPin, $latchPin
    ) {
      parent::__construct(
        $board, $dataPin, $clockPin, $latchPin
      );
      $this->setUp();
    }

    private function setUp() {
      for ($i = 0; $i < 8; $i++) {
        $this->_rows[] = $row = new Row();
        $row->onChange(
          function (Row $row) use ($i) {
            $this->transfer($i + 1, $row->getValue());
          }
        );
      }
    }
    public function offsetExists($offset) {
      return ($offset >= 0 && $offset < 8);
    }

    public function offsetGet($offset) {
      if ($this->offsetExists($offset)) {
        return $this->_rows[$offset];
      }
      return FALSE;
    }

    public function offsetSet($offset, $value) {
      throw new \LogicException('Not a valid row');
    }

    public function offsetUnset($offset) {
      throw new \LogicException('Not a valid row');
    }
  }
}