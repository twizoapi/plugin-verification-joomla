<?php

namespace TwizoPlugin\DataSource\DataAccessObjects;

//include "C:/xampp/htdocs/joomla/libraries/joomla/database/driver.php";

use mysqli_sql_exception;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit\Framework\TestCase;
use TwizoPlugin\Exceptions\TwizoDataException;
use TwizoPlugin\Helpers\TrustedDeviceHelper;
use TwizoPlugin\Model\TwizoData;

/**
 * @package     tests\TwizoPlugin\DataSource\DataAccessObjects
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TwizoDataDAOTest extends TestCase
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
     *
     * @return array $trustedDeviceDAO, $trustedDeviceHelper, $jDatabaseDriverMock
     */
    public function prepareTwizoDataDAO()
    {
        $trustedDeviceDAO    = $this->createMock(TrustedDeviceDAO::class);
        $trustedDeviceHelper = $this->createMock(TrustedDeviceHelper::class);

        $jDatabaseDriverMock = $this->getMockBuilder(\JDatabaseDriver::class)
            ->setMethods(
                [
                    "getQuery", "setQuery", "loadObject",
                    "transactionStart", "transactionCommit", "transactionRollback",
                    "execute", "update", "set", "where", "select", "from", "quote"
                ]
            )->getMock();

        $jDatabaseDriverMock->method("getQuery")->willReturnSelf();
        $jDatabaseDriverMock->method("update")->willReturnSelf();
        $jDatabaseDriverMock->method("where")->willReturnSelf();
        $jDatabaseDriverMock->method("set")->willReturnSelf();
        $jDatabaseDriverMock->method("select")->willReturnSelf();
        $jDatabaseDriverMock->method("from")->willReturnSelf();

        return array($trustedDeviceDAO, $trustedDeviceHelper, $jDatabaseDriverMock);
    }

    public function testGetInstance()
    {
        list($trustedDeviceDAO, $trustedDeviceHelper, $jDatabaseDriverMock) = $this->prepareTwizoDataDAO();


        /** @var TrustedDeviceHelper $trustedDeviceHelper */
        /** @var TrustedDeviceDAO $trustedDeviceDAO */
        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $result = new TwizoDataDAO($trustedDeviceHelper, $trustedDeviceDAO, $jDatabaseDriverMock);

        $this->assertInstanceOf(TwizoDataDAO::class, $result);
    }

    public function testGetById_No_Result()
    {
        list($trustedDeviceDAO, $trustedDeviceHelper, $jDatabaseDriverMock) = $this->prepareTwizoDataDAO();


        /** @var TrustedDeviceHelper $trustedDeviceHelper */
        /** @var TrustedDeviceDAO $trustedDeviceDAO */
        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TwizoDataDAO($trustedDeviceHelper, $trustedDeviceDAO, $jDatabaseDriverMock);

        $this->expectException(TwizoDataException::class);

        $sut->getById("");
    }

    public function testGetById_Wrong_Result()
    {
        list($trustedDeviceDAO, $trustedDeviceHelper, $jDatabaseDriverMock) = $this->prepareTwizoDataDAO();
        /**@var PHPUnit_Framework_MockObject_MockObject $jDatabaseDriverMock */
        $jDatabaseDriverMock->expects($this->any())
            ->method("loadObject")
            ->willReturn((object) array(
                "id"     => 1,
                "otpKey" => "notTwizo:{things:1}"
            ));

        /** @var TrustedDeviceHelper $trustedDeviceHelper */
        /** @var TrustedDeviceDAO $trustedDeviceDAO */
        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TwizoDataDAO($trustedDeviceHelper, $trustedDeviceDAO, $jDatabaseDriverMock);

        $this->expectException(TwizoDataException::class);

        $sut->getById("");
    }

    public function testGetById_Result()
    {
        list($trustedDeviceDAO, $trustedDeviceHelper, $jDatabaseDriverMock) = $this->prepareTwizoDataDAO();
        /**@var PHPUnit_Framework_MockObject_MockObject $jDatabaseDriverMock */

        $userId = 1;
        $jDatabaseDriverMock->expects($this->any())
            ->method("loadObject")
            ->willReturn((object) array(
                "id"     => $userId,
                "otpKey" => "TwizoAuth:{
                                \"number\": \"00000000\",
                                \"identifier\": \"0000000\",
                                \"preferredType\": \"null\"
                            }"
            ));

        /** @var TrustedDeviceHelper $trustedDeviceHelper */
        /** @var TrustedDeviceDAO $trustedDeviceDAO */
        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TwizoDataDAO($trustedDeviceHelper, $trustedDeviceDAO, $jDatabaseDriverMock);


        $result   = $sut->getById("");
        $expected = new TwizoData($userId, "00000000", "0000000", 'null');
        $this->assertEquals($expected, $result);
    }

    public function testGetByNumber_Result()
    {
        list($trustedDeviceDAO, $trustedDeviceHelper, $jDatabaseDriverMock) = $this->prepareTwizoDataDAO();
        /**@var PHPUnit_Framework_MockObject_MockObject $jDatabaseDriverMock */

        $userId = 1;
        $jDatabaseDriverMock->expects($this->any())
            ->method("loadObject")
            ->willReturn((object) array(
                "id"     => $userId,
                "otpKey" => "TwizoAuth:{
                                \"number\": \"00000000\",
                                \"identifier\": \"0000000\",
                                \"preferredType\": \"null\"
                            }"
            ));

        /** @var TrustedDeviceHelper $trustedDeviceHelper */
        /** @var TrustedDeviceDAO $trustedDeviceDAO */
        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TwizoDataDAO($trustedDeviceHelper, $trustedDeviceDAO, $jDatabaseDriverMock);


        $result = $sut->getByNumber("00000000");

        $expected = new TwizoData($userId, "00000000", "0000000", 'null');
        $this->assertEquals($expected, $result);
    }

    public function testGetByUsername_Result()
    {
        list($trustedDeviceDAO, $trustedDeviceHelper, $jDatabaseDriverMock) = $this->prepareTwizoDataDAO();
        /**@var PHPUnit_Framework_MockObject_MockObject $jDatabaseDriverMock */

        $userId = 1;
        $jDatabaseDriverMock->expects($this->any())
            ->method("loadObject")
            ->willReturn((object) array(
                "id"     => $userId,
                "otpKey" => "TwizoAuth:{
                                \"number\": \"00000000\",
                                \"identifier\": \"0000000\",
                                \"preferredType\": \"null\"
                            }"
            ));

        /** @var TrustedDeviceHelper $trustedDeviceHelper */
        /** @var TrustedDeviceDAO $trustedDeviceDAO */
        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TwizoDataDAO($trustedDeviceHelper, $trustedDeviceDAO, $jDatabaseDriverMock);


        $result = $sut->getByUsername("test");

        $expected = new TwizoData($userId, "00000000", "0000000", 'null');
        $this->assertEquals($expected, $result);
    }

    public function testGetByCurrentUser_Result()
    {
        list($trustedDeviceDAO, $trustedDeviceHelper, $jDatabaseDriverMock) = $this->prepareTwizoDataDAO();
        /**@var PHPUnit_Framework_MockObject_MockObject $jDatabaseDriverMock */

        $userId = 1;
        $jDatabaseDriverMock->expects($this->any())
            ->method("loadObject")
            ->willReturn((object) array(
                "id"     => $userId,
                "otpKey" => "TwizoAuth:{
                                \"number\": \"00000000\",
                                \"identifier\": \"0000000\",
                                \"preferredType\": \"null\"
                            }"
            ));

        /** @var TrustedDeviceHelper $trustedDeviceHelper */
        /** @var TrustedDeviceDAO $trustedDeviceDAO */
        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TwizoDataDAO($trustedDeviceHelper, $trustedDeviceDAO, $jDatabaseDriverMock);


        $result = $sut->getByCurrentUser((object) ["id" => $userId]);

        $expected = new TwizoData($userId, "00000000", "0000000", 'null');
        $this->assertEquals($expected, $result);
    }

    public function testRemoveOldTrustedDevices()
    {
        list($trustedDeviceDAO, $trustedDeviceHelperMock, $jDatabaseDriverMock) = $this->prepareTwizoDataDAO();
        /**@var PHPUnit_Framework_MockObject_MockObject $trustedDeviceHelperMock */
        /**@var PHPUnit_Framework_MockObject_MockObject $jDatabaseDriverMock */

        $userId = 1;
        $jDatabaseDriverMock->expects($this->any())
            ->method("loadObject")
            ->willReturn((object) array(
                "id"     => $userId,
                "otpKey" => "TwizoAuth:{
                                \"number\": \"00000000\",
                                \"identifier\": \"0000000\",
                                \"preferredType\": \"null\"
                            }"
            ));

        $jDatabaseDriverMock->expects($this->any())
            ->method("where")
            ->willReturnSelf();


        $trustedDeviceHelperMock->expects($this->once())
            ->method("removeOldDevices")
            ->with(array())
            ->willReturn(array("trustedDevice"));

        /** @var TrustedDeviceHelper $trustedDeviceHelperMock */
        /** @var TrustedDeviceDAO $trustedDeviceDAO */
        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TwizoDataDAO($trustedDeviceHelperMock, $trustedDeviceDAO, $jDatabaseDriverMock);


        $sut->removeOldTrustedDevices('test');
    }

    public function testUpdateTwizoData()
    {
        list($trustedDeviceDAO, $trustedDeviceHelperMock, $jDatabaseDriverMock) = $this->prepareTwizoDataDAO();
        /**@var PHPUnit_Framework_MockObject_MockObject $jDatabaseDriverMock */
        /**@var PHPUnit_Framework_MockObject_MockObject $trustedDeviceHelperMock */

        $userId   = 1;
        $expected = new TwizoData($userId, "00000000", "0000000", 'null');

        $jDatabaseDriverMock->expects($this->once())
            ->method("transactionCommit");

        /** @var TrustedDeviceHelper $trustedDeviceHelperMock */
        /** @var TrustedDeviceDAO $trustedDeviceDAO */
        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TwizoDataDAO($trustedDeviceHelperMock, $trustedDeviceDAO, $jDatabaseDriverMock);


        $sut->updateTwizoData($expected);
    }

    public function testUpdateTwizoData_Mysql_Exception()
    {
        list($trustedDeviceDAO, $trustedDeviceHelperMock, $jDatabaseDriverMock) = $this->prepareTwizoDataDAO();
        /**@var PHPUnit_Framework_MockObject_MockObject $jDatabaseDriverMock */
        /**@var PHPUnit_Framework_MockObject_MockObject $trustedDeviceHelperMock */

        $jDatabaseDriverMock->expects($this->any())
            ->method("setQuery")
            ->willThrowException(new mysqli_sql_exception);

        $jDatabaseDriverMock->expects($this->once())
            ->method("transactionRollback");

        /** @var TrustedDeviceHelper $trustedDeviceHelperMock */
        /** @var TrustedDeviceDAO $trustedDeviceDAO */
        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TwizoDataDAO($trustedDeviceHelperMock, $trustedDeviceDAO, $jDatabaseDriverMock);

        $this->expectException(mysqli_sql_exception::class);
        $sut->updateTwizoData(new TwizoData("1"));
    }

    public function testUpdateTwizoData_Empty_Twizo_Data()
    {
        list($trustedDeviceDAO, $trustedDeviceHelperMock, $jDatabaseDriverMock) = $this->prepareTwizoDataDAO();

        /** @var TrustedDeviceHelper $trustedDeviceHelperMock */
        /** @var TrustedDeviceDAO $trustedDeviceDAO */
        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new TwizoDataDAO($trustedDeviceHelperMock, $trustedDeviceDAO, $jDatabaseDriverMock);

        $this->expectExceptionMessage("TwizoData object empty.");
        $this->expectException(TwizoDataException::class);

        $sut->updateTwizoData(new TwizoData());
    }
}
