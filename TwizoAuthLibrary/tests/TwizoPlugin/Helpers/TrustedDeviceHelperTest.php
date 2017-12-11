<?php

namespace TwizoPlugin\Helpers;

use PHPUnit\Framework\TestCase;
use TwizoPlugin\DataSource\DataAccessObjects\TrustedDeviceDAO;
use TwizoPlugin\Model\TrustedDevice;

/**
 * @package     tests\TwizoPlugin\Helpers
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TrustedDeviceHelperTest extends TestCase
{

    /**
     * @before
     * @since 0.1.0
     */
    protected function setUp()
    {
        if (!defined("_JEXEC"))
            define('_JEXEC', 1);
    }

    public function prepareTrustedDeviceHelper()
    {
        $trustedDeviceDAO = $this->createMock(TrustedDeviceDAO::class);

        return $trustedDeviceDAO;
    }

    public function testGetInstance()
    {
        $trustedDeviceDAO = $this->prepareTrustedDeviceHelper();

        /** @var TrustedDeviceDAO $trustedDeviceDAO */
        $result = new TrustedDeviceHelper($trustedDeviceDAO);

        $this->assertInstanceOf(TrustedDeviceHelper::class, $result);
    }

    public function deviceProvider()
    {
        return [
            "All good devices" => [
                [
                    date('Y-m-d H:i:s', strtotime("-5 days")),
                    date('Y-m-d H:i:s', strtotime("-3 days")),
                    date('Y-m-d H:i:s', strtotime("-3 days")),
                ],
                [
                    date('Y-m-d H:i:s', strtotime("-5 days")),
                    date('Y-m-d H:i:s', strtotime("-3 days")),
                    date('Y-m-d H:i:s', strtotime("-3 days")),
                ]
            ],
            "One old device"   => [
                [
                    date('Y-m-d H:i:s', strtotime("-3 days")),
                    date('Y-m-d H:i:s', strtotime("-3 days")),
                    date('Y-m-d H:i:s', strtotime("-31 days")),
                ],
                [
                    date('Y-m-d H:i:s', strtotime("-3 days")),
                    date('Y-m-d H:i:s', strtotime("-3 days")),
                ]
            ],
        ];
    }

    /**
     * @dataProvider deviceProvider
     */
    public function testRemoveOldDevices($inputDates, $expectedDates)
    {
        $trustedDeviceDAOMock = $this->prepareTrustedDeviceHelper();

        $trustedDevices = [];
        foreach ($inputDates as $key => $date)
            $trustedDevices[$key] = new TrustedDevice(null, '', $date);

        $expectedDevices = [];
        foreach ($expectedDates as $key => $date)
            $expectedDevices[$key] = new TrustedDevice(null, '', $date);

        /** @var TrustedDeviceDAO $trustedDeviceDAOMock */
        $sut = new TrustedDeviceHelper($trustedDeviceDAOMock);

        $result = $sut->removeOldDevices($trustedDevices);
        $this->assertEquals($expectedDevices, $result);
    }

    public function testCheckCookie()
    {
        $trustedDeviceDAOMock = $this->prepareTrustedDeviceHelper();

        $recipient = "00000000";
        $cookie    = "00000000";
        $username  = 'test';

        $expected = hash('sha512', sprintf('%s_%s', $recipient, $cookie));

        // Expect the hash
        $trustedDeviceDAOMock->expects($this->once())
            ->method("checkHash")
            ->with($username, $expected);

        /** @var TrustedDeviceDAO $trustedDeviceDAOMock */
        $sut = new TrustedDeviceHelper($trustedDeviceDAOMock);

        $sut->checkCookie($cookie, $username, $recipient);
    }
}