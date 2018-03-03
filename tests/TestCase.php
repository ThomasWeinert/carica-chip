<?php

namespace Carica\Chip {

  use Carica\Io\Device\Pin;
  use PHPUnit\Framework\MockObject\MockObject;

  abstract class TestCase extends \PHPUnit\Framework\TestCase {

    /**
     * @return MockObject|\Carica\Io\Device\Pin
     * @throws \ReflectionException
     */
    protected function getMockForPin() {
      return $this->createMock(Pin::class);
    }
  }
}
