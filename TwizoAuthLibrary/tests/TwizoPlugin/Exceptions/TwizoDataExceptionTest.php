<?php
namespace TwizoPlugin\Exceptions;

use PHPUnit\Framework\TestCase;

/**
 * @package     tests\TwizoPlugin\Exceptions
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TwizoDataExceptionTest extends TestCase
{

    protected function setUp()
    {
        if (!defined("_JEXEC"))
            define('_JEXEC', 1);
    }

    public function testGetInstance()
    {
        $result = new TwizoDataException();

        $this->assertInstanceOf(TwizoDataException::class, $result);
    }

    public function test_Throw()
    {
        $this->expectException(TwizoDataException::class);
        throw new TwizoDataException();
    }

    public function test_Default_Message()
    {
        $this->expectExceptionMessage("Cannot get Twizo data object. Not found in the database.");
        throw new TwizoDataException();
    }

    public function test_Custom_Message()
    {
        $this->expectExceptionMessage("test");
        throw new TwizoDataException("test");
    }
}
