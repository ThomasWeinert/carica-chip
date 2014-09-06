<?php

namespace Carica\Chip\Max7219 {

  use Carica\Chip\Max7219;

  class Matrix extends Max7219 {

    private $_displays = [];
    private $_width = 8;
    private $_height = 8;

    private $_buffer = null;
    private $_colorOn;
    private $_colorOff;

    public function addDisplay($x, $y, $rotation = MatrixDisplay::ROTATION_NONE) {
      $pins = $this->getPins();
      $display = new MatrixDisplay(
        $this->getBoard(), $pins['data'], $pins['clock'], $pins['latch'], $rotation
      );
      $this->_displays[] = [
        'position' => [$x, $y],
        'display' => $display
      ];
      $this->_width = max($this->_width, ($x + 1) * 8);
      $this->_height = max($this->_height, ($y + 1) * 8);
      $this->_buffer = NULL;
    }

    private function getBuffer() {
      if (!$this->_buffer) {
        $this->_buffer = imagecreate($this->_width, $this->_height);
        $this->_colorOn = imagecolorallocate($this->_buffer, 255, 255, 255);
        $this->_colorOff = imagecolorallocate($this->_buffer, 0, 0, 0);
        imagefilledrectangle(
          $this->_buffer, 0, 0, $this->_width, $this->_height, $this->_colorOff
        );
      }
      return $this->_buffer;
    }

    public function draw(
      $image,
      $targetX = 0, $targetY = 0,
      $sourceX = 0, $sourceY = 0, $sourceWidth = 0, $sourceHeight = 0
    ) {
      $buffer = $this->getBuffer();
      $sourceWidth = $sourceWidth > 0 ? $sourceWidth : imagesx($image);
      $sourceHeight = $sourceHeight > 0 ? $sourceHeight : imagesy($image);
      imagecopy(
        $buffer, $image, $targetX, $targetY, $sourceX, $sourceY, $sourceWidth, $sourceHeight
      );
      $count = count($this->_displays);
      imagepng($buffer, __DIR__.'/target.png');
      foreach ($this->_displays as $i => $item) {
        $display = $item['display'];
        $display->setIndex($i, $count);
        $display->clear()->on();
        $this->updateDisplay($display, $item['position'][0], $item['position'][1]);
        $display->commit();
      }
    }


    private function updateDisplay($display, $x, $y) {
      $buffer = $this->getBuffer();
      $left = $x * 8;
      $top = $y * 8;
      for ($x = 0; $x < 8; $x++) {
        for ($y = 0; $y < 8; $y++) {
          $color = imagecolorsforindex($buffer, imagecolorat($buffer, $left + $x, $top + $y));
          $isOn = max($color) > 120;
          $display->setDot($x, $y, $isOn, FALSE);
        }
      }
    }
  }
}
