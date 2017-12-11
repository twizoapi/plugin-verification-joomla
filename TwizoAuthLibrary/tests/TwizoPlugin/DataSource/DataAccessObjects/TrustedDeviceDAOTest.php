<?php

namespace TwizoPlugin\DataSource\DataAccessObjects;

use PHPUnit\Framework\TestCase;
use TwizoPlugin\Model\TrustedDevice;

/**
 * @package     tests\TwizoPlugin\DataSource\DataAccessObjects
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TrustedDeviceDAOTest extends TestCase
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject $jDatabaseDriverMock
     */
    public function prepareTrustedDeviceDAO()
    {
        $jDatabaseDriverMock = $this->getMockBuilder(\JDatabaseDriver::class)
            ->setMethods(
                [
                    "getQuery", "setQuery", "loadObjectList", "loadObject", "insertObject",
                    "transactionStart", "transactionCommit", "transactionRollback",
                    "execute", "update", "set", "where", "select", "from", "quote", "delete"
                ]
            )->getMock();

        $jDatabaseDriverMock->method("getQuery")->willReturnSelf();
        $jDatabaseDriverMock->method("update")->willReturnSelf();
        $jDatabaseDriverMock->method("where")->willReturnSelf();
        $jDatabaseDriverMock->method("set")->willReturnSelf();
        $jDatabaseDriverMock->method("select")->willReturnSelf();
        $jDatabaseDriverMock->method("from")->willReturnSelf();
        $jDatabaseDriverMock->method("delete")->willReturnSelf();

        $jDatabaseDriverMock->expects($this->any())
            ->method("execute")
            ->willReturn(true);

        return $jDatabaseDriverMock;
    }

    public function testGetInstance()
    {
        $jDatabaseDriverMock = $this->prepareTrustedDeviceDAO();


        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $result = new TrustedDeviceDAO($jDatabaseDriverMock);

        $this->assertInstanceOf(TrustedDeviceDAO::class, $result);
    }

    public function testGetById()
    {
        $jDatabaseDriverMock = $this->prepareTrustedDeviceDAO();

        $jDatabaseDriverMock->expects($this->any())
            ->method("loadObjectList")
            ->willReturn(
                array(
                    (object) array(
                        "id"   => 1,
                        "hash" => "0",
                        "date" => ""
                    ),
                    (object) array(
                        "id"   => 1,
                        "hash" => "1",
                        "date" => ""
                    ),
                )
            );

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TrustedDeviceDAO($jDatabaseDriverMock);

        $expected = array(
            new TrustedDevice(1, "0", ""),
            new TrustedDevice(1, "1", "")
        );

        $this->assertArraySubset($expected, $sut->getById(1));
    }

    public function testGetByUserName()
    {
        $jDatabaseDriverMock = $this->prepareTrustedDeviceDAO();

        $jDatabaseDriverMock->expects($this->any())
            ->method("loadObjectList")
            ->willReturn(
                array(
                    (object) array(
                        "id"   => 1,
                        "hash" => "0",
                        "date" => ""
                    ),
                    (object) array(
                        "id"   => 1,
                        "hash" => "1",
                        "date" => ""
                    ),
                )
            );

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TrustedDeviceDAO($jDatabaseDriverMock);

        $expected = array(
            new TrustedDevice(1, "0", ""),
            new TrustedDevice(1, "1", "")
        );

        $this->assertArraySubset($expected, $sut->getByUsername('test'));
    }

    public function testUpdateDevices_Empty_TrustedDevices()
    {
        $jDatabaseDriverMock = $this->prepareTrustedDeviceDAO();

        $jDatabaseDriverMock->expects($this->never())
            ->method("execute");

        $jDatabaseDriverMock->expects($this->never())
            ->method("insertObject");

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TrustedDeviceDAO($jDatabaseDriverMock);

        $sut->updateDevices(1, array());
    }

    public function testUpdateDevices_Delete()
    {
        $jDatabaseDriverMock = $this->prepareTrustedDeviceDAO();

        $userId   = 1;
        $expected = 'userId = ' . $userId . ' AND id NOT IN (\'' . implode('\',\'', [0, 1]) . '\')';


        $jDatabaseDriverMock->expects($this->once())
            ->method("where")
            ->with($expected);

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TrustedDeviceDAO($jDatabaseDriverMock);

        $trustedDevices = array(
            new TrustedDevice(0, "0", "3"),
            new TrustedDevice(1, "1", "")
        );

        $sut->updateDevices($userId, $trustedDevices);
    }

    public function testUpdateDevices_Insert()
    {
        $jDatabaseDriverMock = $this->prepareTrustedDeviceDAO();

        $userId = 1;

        $trustedDevices = array(
            new TrustedDevice(null, "0", "3"),
            new TrustedDevice(null, "1", "4")
        );

        // Item 0
        $trustedDevices[0]->userId = $userId;
        $jDatabaseDriverMock->expects($this->at(0))
            ->method("insertObject")
            ->with('#__twizodata_users', $trustedDevices[0]);

        // Item 1
        $trustedDevices[1]->userId = $userId;
        $jDatabaseDriverMock->expects($this->at(1))
            ->method("insertObject")
            ->with('#__twizodata_users', $trustedDevices[1])
            ->willReturn(true);

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TrustedDeviceDAO($jDatabaseDriverMock);

        $sut->updateDevices($userId, $trustedDevices);
    }

    public function testCheckHash()
    {
        $jDatabaseDriverMock = $this->prepareTrustedDeviceDAO();

        $username      = "username";
        $hashedVersion = "hashValue";

        $jDatabaseDriverMock->expects($this->once())
            ->method("loadObject")
            ->willReturn((object) []);
//      'userId in (select id from #__users where username = \'' . $username . '\') AND hash = ' . $hashedVersion . '\''

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TrustedDeviceDAO($jDatabaseDriverMock);

        $this->assertTrue((bool) $sut->checkHash($username, $hashedVersion));
    }
}
