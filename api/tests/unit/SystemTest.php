<?php

namespace api\tests\unit;

use Codeception\Test\Unit;

class SystemTest extends Unit
{

    public function testPhpTimezone()
    {
        $this->assertEquals('UTC', date_default_timezone_get(), 'You PHP server and CLI must set timezone to UTC');
    }
}
