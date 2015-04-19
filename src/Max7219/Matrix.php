<?php

namespace Carica\Chip\Max7219 {

  use Carica\Io\Event;
  use Carica\Chip\Max7219;

  /**
   * A matrix build from several MAX7219 canvas displays, connected to the same pins.
   */
  class Matrix extends Max7219
    implements Event\HasLoop {

    use Event\Loop\Aggregation;

    private $_displays = [];

    /**
     * @var resource image resource for internal canvas
     */
    private $_buffer = null;
    /**
     * @var int internal canvas width
     */
    private $_width = 8;
    /**
     * @var int internal canvas height
     */
    private $_height = 8;
    /**
     * @var int index of color signaling off (black)
     */
    private $_colorOff;

    /**
     * @var object animations timer from event loop
     */
    private $_timer = null;

    /**
     * Add a display by the coordinates of the upper left led.
     *
     * Displays can have margins between them or overlap. The internal canvas is resized
     * depending on the displays.
     *
     * @param int $left
     * @param int $top
     * @param int $rotation
     * @return $this
     */
    public function addDisplay($left, $top, $rotation = Matrix\Display::ROTATION_NONE) {
      $display = new Matrix\Display($this->getShiftOut(), $rotation);
      $this->_displays[] = [$display, $left, $top];
      $this->_width = max($this->_width, $left + 8);
      $this->_height = max($this->_height, $top + 8);
      $this->_buffer = NULL;
      parent::setIndex(self::INDEX_ALL, count($this->_displays));
      return $this;
    }

    /**
     * @return $this
     */
    public function clear() {
      $this->stop();
      imagefilledrectangle(
        $this->getBuffer(), 0, 0, $this->_width, $this->_height, $this->_colorOff
      );
      $this->updateDisplays();
      return $this;
    }

    /**
     * Draw an image on the displays
     *
     * @param resource $image
     * @param int $targetX
     * @param int $targetY
     * @param int $sourceX
     * @param int $sourceY
     * @param int $sourceWidth
     * @param int $sourceHeight
     * @return $this
     */
    public function draw(
      $image,
      $targetX = 0, $targetY = 0,
      $sourceX = 0, $sourceY = 0, $sourceWidth = 0, $sourceHeight = 0
    ) {
      $this->stop();
      $buffer = $this->getBuffer();
      imagefilledrectangle(
        $buffer, 0, 0, $this->_width, $this->_height, $this->_colorOff
      );
      $sourceWidth = $sourceWidth > 0 ? $sourceWidth : imagesx($image);
      $sourceHeight = $sourceHeight > 0 ? $sourceHeight : imagesy($image);
      imagecopy(
        $buffer, $image, $targetX, $targetY, $sourceX, $sourceY, $sourceWidth, $sourceHeight
      );
      $this->updateDisplays();
      return $this;
    }

    /**
     * Scroll the image horizontally
     *
     * @param resource $image
     * @param int $step moving offset
     * @param int $milliseconds
     * @return $this
     */
    public function scrollX($image, $step = -1, $milliseconds = 250) {
      $this->stop();
      $sourceWidth = imagesx($image);
      $sourceHeight = imagesy($image);
      $target = $this->getBuffer();
      $this->loop()->setInterval(
        function() use ($target, $image, $step, $sourceWidth, $sourceHeight) {
          static $offset = 0;
          if ($offset > 0) {
            imagecopy(
              $target, $image, 0, 0, $sourceWidth - $offset, 0, $offset, $sourceHeight
            );
          }
          for ($i = $offset; $i < $this->_width; $i += $sourceWidth) {
            imagecopy(
              $target, $image, $i, 0, 0, 0, $sourceWidth, $sourceHeight
            );
          }
          $this->updateDisplays();
          $offset += $step;
          if ($offset <= -$sourceWidth) {
            $offset = 0;
          } elseif ($offset >= $sourceWidth) {
            $offset = 0;
          }
        },
        $milliseconds
      );
      return $this;
    }


    /**
     * Scroll the image vertically
     *
     * @param resource $image
     * @param int $step moving offset
     * @param int $milliseconds
     * @return $this
     */
    public function scrollY($image, $step = -1, $milliseconds = 250) {
      $this->stop();
      $sourceWidth = imagesx($image);
      $sourceHeight = imagesy($image);
      $target = $this->getBuffer();
      $this->loop()->setInterval(
        function() use ($target, $image, $step, $sourceWidth, $sourceHeight) {
          static $offset = 0;
          if ($offset > 0) {
            imagecopy(
              $target, $image, 0, 0, 0, $sourceHeight - $offset, $sourceWidth, $offset
            );
          }
          for ($i = $offset; $i < $this->_height; $i += $sourceHeight) {
            imagecopy(
              $target, $image, 0, $i, 0, 0, $sourceWidth, $sourceHeight
            );
          }
          $this->updateDisplays();
          $offset += $step;
          if ($offset <= -$sourceHeight) {
            $offset = 0;
          } elseif ($offset >= $sourceHeight) {
            $offset = 0;
          }
        },
        $milliseconds
      );
      return $this;
    }

    /**
     * Stop current animation and remove it from event loop.
     *
     * @return $this
     */
    public function stop() {
      if ($this->_timer) {
        $this->loop()->remove($this->_timer);
        $this->_timer = NULL;
      }
      return $this;
    }

    /**
     * Create and return an image as internal canvas.
     *
     * @return resource
     */
    private function getBuffer() {
      if (!$this->_buffer) {
        $this->_buffer = imagecreate($this->_width, $this->_height);
        $this->_colorOff = imagecolorallocate($this->_buffer, 0, 0, 0);
      }
      return $this->_buffer;
    }

    /**
     * Read data from canvas, update displays and commit status to the
     * actual hardware.
     */
    private function updateDisplays() {
      foreach ($this->_displays as $display) {
        $this->updateDisplay($display[0], $display[1], $display[2]);
      }
      $this->commit();
    }

    /**
     * Update a display with the canvas data, but do not yet transfer
     * the status to the hardware.
     */
    private function updateDisplay(Matrix\Display $display, $left, $top) {
      $buffer = $this->getBuffer();
      for ($x = 0; $x < 8; $x++) {
        for ($y = 0; $y < 8; $y++) {
          $offsetX = $left + $x;
          $offsetY = $top + $y;
          if ($offsetX < $this->_width && $offsetY < $this->_height) {
            $color = imagecolorsforindex($buffer, imagecolorat($buffer, $offsetX, $offsetY));
            $isOn = max($color) > 120;
          } else {
            $isOn = FALSE;
          }
          $display->setDot($x, $y, $isOn, FALSE);
        }
      }
    }

    /**
     * Transfer display status to the actual hardware.
     *
     * This aproach to allows with a single command for each register.
     */
    private function commit() {
      $bytes = [];
      for ($i = count($this->_displays) - 1; $i >= 0; $i--) {
        /** @var Matrix\Display $display */
        $display = $this->_displays[$i][0];
        foreach ($display->getBytes() as $index => $value) {
          $bytes[$index][] = $index + 1;
          $bytes[$index][] = $value;
        }
      }
      $shiftOut = $this->getShiftOut();
      foreach ($bytes as $buffer) {
        $shiftOut->write($buffer);
      }
    }

    /**
     * Index and count are updated by addDidplay, throw in exception
     * if someone tries to set it.
     *
     * @param int $index
     * @param int $count
     */
    public function setIndex($index, $count = 1) {
      throw new \LogicException('Will changed automatically with addDisplay().');
    }
  }
}
