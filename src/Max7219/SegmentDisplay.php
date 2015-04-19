<?php

namespace Carica\Chip\Max7219 {

  use Carica\Chip\Max7219\SegmentDisplay\Segment;
  use Carica\Io\Device\ShiftOut;
  use Carica\Io\Event;

  class SegmentDisplay
    extends \Carica\Chip\Max7219
    implements \ArrayAccess, Event\HasLoop {

    use Event\Loop\Aggregation;

    /**
     * @var Segment[]
     */
    private $_segments = [];

    /**
     * Timer for animations
     * @var object
     */
    private $_timer = NULL;

    /**
     * @param ShiftOut $shiftOut
     * @param int $length
     */
    public function __construct(ShiftOut $shiftOut, $length = 8) {
      parent::__construct($shiftOut);
      $this->setLength($length);
      $this->off();
      $this->brightness(0.01);
    }

    /**
     * Set the length and initialize the segment objects
     *
     * @param $length
     */
    private function setLength($length) {
      $this->sendCommand(self::MODE_SCAN_LIMIT, $length - 1);
      $this->_segments = [];
      for ($i = 0; $i < $length; $i++) {
        $this->_segments[] = $segment = new Segment();
        $segment->onChange(
          function (Segment $segment) use ($i) {
            $this->sendCommand($i + 1, $segment->getValue());
          }
        );
      }
    }

    /**
     * @param \Traversable|int[] $bytes
     * @return $this
     */
    public function show($bytes) {
      $this->stop(TRUE);
      foreach ($bytes as $index => $byte) {
        $this->_segments[$index]->setValue($byte);
      }
      return $this;
    }

    /**
     * @param \Traversable|int[] $bytes
     * @param int $speed animation speed in milliseconds
     * @return $this
     */
    public function scroll($bytes, $speed = 200) {
      $this->stop(TRUE);
      $bytes = iterator_to_array($bytes);
      $this->_timer = $this->loop()->setInterval(
        $next = function() use ($bytes) {
          static $offset = 0;
          $length = count($bytes);
          if (--$offset < 0) {
            $offset = $length - 1;
          }
          $buffer = array_slice($bytes, $offset, 8);
          $bufferLength = count($buffer);
          while ($bufferLength < 8) {
            array_splice(
              $buffer, $bufferLength, 0, array_slice($bytes, 0, 8 - $bufferLength)
            );
            $bufferLength = count($buffer);
          }
          foreach ($buffer as $index => $byte) {
            if (isset($this->_segments[$index])) {
              $this->_segments[$index]->setValue($byte);
            }
          }
        },
        $speed
      );
      $next();
      return $this;
    }

    /**
     * Stop the current animation
     *
     * @param bool $clear
     */
    public function stop($clear = FALSE) {
      if (isset($this->_timer)) {
        $this->loop()->remove($this->_timer);
      }
      if ($clear) {
        $this->clear();
      }
    }

    /**
     * Return true if a segment with that index exists
     *
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset) {
      return array_key_exists($offset, $this->_segments);
    }

    /**
     * Get the segment object for a give offset
     *
     * @param int $offset
     * @return \Carica\Chip\Max7219\SegmentDisplay\Segment
     */
    public function offsetGet($offset) {
      return $this->_segments[$offset];
    }

    /**
     * Allow to change the value of an segemnt or assign it from another segment
     * @param int $offset
     * @param Segment $value
     * @return void
     */
    public function offsetSet($offset, $value) {
      if (!$this->_segments[$offset]) {
        return;
      }
      if ($value instanceof Segment) {
        $this->_segments[$offset]->setValue($value->getValue());
      } else {
        $this->_segments[$offset]->setValue((int)$value);
      }
    }

    /**
     * Reset the segment value to 0
     *
     * @param int $offset
     * @return void
     */
    public function offsetUnset($offset) {
      if (!$this->_segments[$offset]) {
        return;
      }
      $this->_segments[$offset]->setValue(0);
    }
  }
}