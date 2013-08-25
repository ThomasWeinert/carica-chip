<?php

namespace Carica\Chip {

  use Carica\Firmata;
  use Carica\Io;
  use Carica\Io\Event;

  class Servo {

    use Event\Loop\Aggregation;

    private $_board = NULL;
    private $_pin = 0;
    private $_range = 180;
    private $_invert = FALSE;

    /**
     * the time in milliseconds needed to move the servo by one degree
     * @var float
     */
    private $_timePerDegree = 23;

    public function __construct(Firmata\Board $board, $pin, $range = 180) {
      $this->_board = $board;
      $this->_pin = (int)$pin;
      if (abs($range) > 255) {
        throw new \InvalidArgumentException('Invalid servo range: '.(int)$range);
      } elseif ($range < 0) {
        $this->_invert = TRUE;
      }
      $this->_range = abs($range);
    }

    /**
     * Return the current position
     *
     * @return integer
     */
    public function getPosition() {
      $position = $this->_board->pins[$this->_pin]->analog;
      return ($this->_invert)  ? $this->_range - $position : $position;
    }

    /**
     * Move to minimum position (0 degrees)
     *
     * @return \Carica\Io\Deferred\Promise
     */
    public function min() {
      return $this->moveTo(0);
    }

    /**
     * Move to maximum position (range or 255)
     *
     * @return \Carica\Io\Deferred\Promise
     */
    public function max() {
      return $this->moveTo($this->_range);
    }

    /**
     * Center the servo
     *
     * @return \Carica\Io\Deferred\Promise
     */
    public function center() {
      return $this->moveTo(round($this->_range / 2));
    }

    /**
     * Move the servo to a given position
     *
     * @param integer $position
     * @return \Carica\Io\Deferred\Promise
     */
    public function moveTo($position) {
      $this->_board->pins[$this->_pin]->mode = Firmata\Board::PIN_STATE_SERVO;
      $offset = abs($this->getPosition() - $position);
      $defer = new Io\Deferred();
      $position = ($this->_invert)  ? $this->_range - $position : $position;
      $this->_board->pins[$this->_pin]->analog = $position;
      $this->loop()->setTimeout(
        function () use ($defer, $position) {
          $defer->resolve($position);
        },
        round($offset * $this->_timePerDegree)
      );
      return $defer->promise();
    }

    /**
     * Validate that the given position is within the range of the servo
     *
     * @throws \OutOfRangeException
     * @return boolean
     */
    private function validatePosition($position) {
      if ($position < 0 || $position > $this->_range) {
        throw new \OutOfRangeException(
          sprintf(
            'Position %d is outside servo range 0 to %d.',
            $position,
            $this->_range
          )
        );
      }
      return TRUE;
    }
  }
}