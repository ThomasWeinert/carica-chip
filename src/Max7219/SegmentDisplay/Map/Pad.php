<?php

namespace Carica\Chip\Max7219\SegmentDisplay\Map {

  use Carica\Chip\Max7219\SegmentDisplay\Map;

  class Pad implements Map {

    /**
     * @var \Iterator
     */
    private $_iterator = null;

    /**
     * @var array|\Traversable
     */
    private $_bytes = [];

    /**
     * @var int
     */
    private $_padByte = 0x00;

    /**
     * @var int
     */
    private $_padType = STR_PAD_LEFT;

    public function __construct(
      $bytes, $length, $padByte = 0x00, $padType = STR_PAD_BOTH
    ) {
      $this->_length = $length;
      $this->_bytes = $bytes;
      $this->_padByte = $padByte;
      $this->_padType = $padType;
    }

    /**
     * @return \Traversable
     */
    public function getIterator() {
      if (!isset($this->_iterator)) {
        $bufferLength = count($this->_bytes);
        $padByte = (int)$this->_padByte;
        if ($this->_length > $bufferLength) {
          $bytes = is_array($this->_bytes)
            ? $this->_bytes : iterator_to_array($this->_bytes);
          $padLength = $this->_length - $bufferLength;
          switch ($this->_padType) {
          case STR_PAD_LEFT :
            return $this->_iterator = new \ArrayIterator(
              array_merge(array_fill(0, $padLength, $padByte), $bytes)
            );
          case STR_PAD_RIGHT :
            return $this->_iterator = new \ArrayIterator(
              array_merge($bytes, array_fill(0, $padLength, $padByte))
            );
          default :
            return $this->_iterator = new \ArrayIterator(
              array_merge(
                array_fill(0, floor($padLength / 2), $padByte),
                $bytes,
                array_fill(0, ceil($padLength / 2), $padByte)
              )
            );
          }
        } else {
          return $this->_iterator = is_array($this->_bytes)
            ? new \ArrayIterator($this->_bytes) : $this->_bytes;
        }
      }
      return $this->_iterator;
    }

    /**
     * @return int
     */
    public function count() {
      return count($this->getIterator());
    }
  }
}