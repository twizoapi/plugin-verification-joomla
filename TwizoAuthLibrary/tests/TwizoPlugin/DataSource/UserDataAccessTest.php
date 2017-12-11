<?php

namespace TwizoPlugin\DataSource;

use TwizoPlugin\DataSource\DataAccessObjects\TwizoDataDAO;
use PHPUnit\Framework\TestCase;
use TwizoPlugin\Helpers\TwizoHelper;
use TwizoPlugin\Model\TwizoData;

/**
 * @package     tests\TwizoPlugin\DataSource
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class UserDataAccessTest extends TestCase
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
     * @return array $jDatabaseDriverMock, $twizoDataDAOMock, $twizoHelperMock
     */
    public function prepareUserDataAccess()
    {
        $twizoHelperMock  = $this->createMock(TwizoHelper::class);
        $twizoDataDAOMock = $this->createMock(TwizoDataDAO::class);

        $jDatabaseDriverMock = $this->getMockBuilder(\JDatabaseDriver::class)
            ->setMethods(
                [
                    "getQuery", "setQuery", "loadObjectList", "loadObject", "insertObject",
                    "transactionStart", "transactionCommit", "transactionRollback", "quoteName",
                    "execute", "update", "set", "where", "select", "from", "quote", "delete", "loadResult"
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

        return [$jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock];
    }

    public function testGetInstance()
    {
        list($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock) = $this->prepareUserDataAccess();

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $result = new UserDataAccess($jDatabaseDriverMock, $twizoDataDAOMock, $twizoHelperMock);

        $this->assertInstanceOf(UserDataAccess::class, $result);
    }

    public function testGetUserId()
    {
        list($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock) = $this->prepareUserDataAccess();


        $jDatabaseDriverMock->method("quoteName")->willReturnCallback(function ($string) {
            return '\'' . $string . '\'';
        });

        $jDatabaseDriverMock->method("quote")->willReturnCallback(function ($string) {
            return '\'' . $string . '\'';
        });

        $username = "test";

        $expected = "'username' = '$username'";
        $jDatabaseDriverMock->expects($this->atLeast(1))
            ->method("where")
            ->with($expected);

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new UserDataAccess($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock);

        $sut->getUserId($username);
    }

    /* Test update Number*/

    public function testUpdateNumber()
    {
        list($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock) = $this->prepareUserDataAccess();

        $twizoHelperMock->expects($this->any())
            ->method("isWidgetSuccess")
            ->willReturn(true);

        $expected = "123456456";
        // Check if "updateTwizoData" is called with the updated twizoData object.
        $twizoDataDAOMock->expects($this->once())
            ->method("updateTwizoData")
            ->with(new TwizoData(null, $expected));

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new UserDataAccess($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock);

        $twizoData = new TwizoData();
        $sut->updateNumber($twizoData, "sessionTokenValue", $expected);
    }

    public function testUpdateNumber_No_Number()
    {
        list($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock) = $this->prepareUserDataAccess();

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new UserDataAccess($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock);

        $this->expectExceptionMessage('Number is not set.');
        $twizoData = new TwizoData();
        $sut->updateNumber($twizoData, "sessionTokenValue", null);
    }

    public function testUpdateNumber_Wrong_SessionToken()
    {
        list($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock) = $this->prepareUserDataAccess();

        $twizoHelperMock->expects($this->any())
            ->method("isWidgetSuccess")
            ->willReturn(false);

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new UserDataAccess($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock);

        $this->expectExceptionMessage('Twizo widget is not validated.');
        $twizoData = new TwizoData();
        $sut->updateNumber($twizoData, "sessionTokenValue", "numberValue");
    }

    /* Test update Backup Codes*/

    public function testUpdateBackupCodes_Update()
    {
        list($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock) = $this->prepareUserDataAccess();

        $twizoHelperMock->expects($this->any())
            ->method("isWidgetSuccess")
            ->willReturn(true);

        $twizoData = new TwizoData("1", "12345", "654321");
        $twizoHelperMock->expects($this->once())
            ->method("updateBackupCodes")
            ->with($twizoData);

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new UserDataAccess($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock);

        $sut->updateBackupCodes($twizoData, "sessionTokenValue");
    }

    public function testUpdateBackupCodes_Generate()
    {
        list($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock) = $this->prepareUserDataAccess();

        $twizoHelperMock->expects($this->any())
            ->method("isWidgetSuccess")
            ->willReturn(true);

        $twizoData = new TwizoData("1", "12345");
        $twizoDataDAOMock->expects($this->once())
            ->method("updateTwizoData")
            ->with($twizoData);

        $expected = "0987654321";
        $twizoHelperMock->expects($this->once())
            ->method("generateBackupCodes")
            ->with($expected);

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new UserDataAccess($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock);

        $sut->updateBackupCodes($twizoData, "sessionTokenValue", $expected);
    }

    public function testUpdateBackupCodes_Wrong_SessionToken()
    {
        list($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock) = $this->prepareUserDataAccess();

        $twizoHelperMock->expects($this->any())
            ->method("isWidgetSuccess")
            ->willReturn(false);

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new UserDataAccess($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock);

        $this->expectExceptionMessage('Twizo widget is not validated.');
        $twizoData = new TwizoData();
        $sut->updateBackupCodes($twizoData, "sessionTokenValue");
    }

    /* Test update Preferred Method */

    public function testUpdatePreferredMethod()
    {
        list($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock) = $this->prepareUserDataAccess();

        $twizoData = new TwizoData(null, null, null, "call");
        $twizoDataDAOMock->expects($this->once())
            ->method("updateTwizoData")
            ->with($twizoData);

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new UserDataAccess($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock);

        $twizoData = new TwizoData();
        $sut->updatePreferredMethod($twizoData, "call");
    }

    public function testUpdatePreferredMethod_No_Method()
    {
        list($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock) = $this->prepareUserDataAccess();

        /** @var \JDatabaseDriver $jDatabaseDriverMock */
        $sut = new UserDataAccess($jDatabaseDriverMock, $twizoHelperMock, $twizoDataDAOMock);

        $this->expectExceptionMessage('Preferred method is not set.');
        $twizoData = new TwizoData();
        $sut->updatePreferredMethod($twizoData, null);
    }
}
