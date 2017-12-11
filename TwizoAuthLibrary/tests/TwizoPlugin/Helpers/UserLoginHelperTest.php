<?php

namespace TwizoPlugin\Helpers;

use PHPUnit\Framework\TestCase;
use Twizo\Api\Entity\Exception;
use TwizoPlugin\DataSource\DataAccessObjects\TwizoDataDAO;
use TwizoPlugin\DataSource\UserDataAccess;
use TwizoPlugin\Model\TwizoData;

/**
 * @package     tests\TwizoPlugin\Helpers
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class UserLoginHelperTest extends TestCase
{

    protected function setUp()
    {
        if (!defined("_JEXEC"))
            define('_JEXEC', 1);
    }

    /**
     * Create mocks for multiple tests
     *
     * @returns array $twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock
     * @since 0.1.0
     */
    public function prepareUserLoginHelper()
    {
        $twizoDataDAOMock        = $this->createMock(TwizoDataDAO::class);
        $userDataAccessMock      = $this->createMock(UserDataAccess::class);
        $twizoHelperMock         = $this->createMock(TwizoHelper::class);
        $trustedDeviceHelperMock = $this->createMock(TrustedDeviceHelper::class);
        $twizoSettingsHelperMock = $this->createMock(TwizoSettingsHelper::class);


        return array($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);
    }

    public function testGetInstance()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $result = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $this->assertInstanceOf(UserLoginHelper::class, $result);
    }

    /* Test Check Credentials */

    public function testCheckCredentials_Credentials_No_TrustedDevice()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $widgetSession = array("sessionToken" => "sessionTokenValue", "logoUrl" => "logoUrlValue");
        $twizoHelperMock->method("getWidgetSession")->willReturn($widgetSession);

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData('1', "0000000000");

        $expected = array(
            "enabled"          => true,
            "credentialsCheck" => true,
            "sessionToken"     => "sessionTokenValue",
            "trustedDevice"    => false,
            "logoUrl"          => "logoUrlValue"
        );

        $result = $sut->checkCredentials($twizoData, 'henk', true, 'value');
        $this->assertArraySubset($expected, $result);
    }

    public function testCheckCredentials_Credentials_No_Username()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData();

        $this->expectExceptionMessage('Username not set.');
        $sut->checkCredentials($twizoData, null, true, 'value');
    }

    public function testCheckCredentials_Credentials_Is_TrustedDevice()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $trustedDeviceHelperMock->method("checkCookie")->willReturn(true);

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData('1', "0000000000");

        $expected = array(
            "enabled"          => true,
            "credentialsCheck" => true,
            "sessionToken"     => null,
            "trustedDevice"    => true,
            "logoUrl"          => null
        );

        $result = $sut->checkCredentials($twizoData, 'henk', true, 'value');
        $this->assertArraySubset($expected, $result);
    }

    public function testCheckCredentials_Credentials_Validated_No_Number()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData('1', null);

        $expected = array(
            "enabled"          => false,
            "credentialsCheck" => true,
            "sessionToken"     => null,
            "trustedDevice"    => false,
            "logoUrl"          => null
        );

        $result = $sut->checkCredentials($twizoData, 'henk', true, 'value');
        $this->assertArraySubset($expected, $result);
    }

    public function testCheckCredentials_No_Credentials_Validated()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData('1', '33333333');

        $expected = array(
            "enabled"          => false,
            "credentialsCheck" => false,
            "sessionToken"     => null,
            "trustedDevice"    => false,
            "logoUrl"          => null
        );

        $result = $sut->checkCredentials($twizoData, 'henk', false, 'value');
        $this->assertArraySubset($expected, $result);
    }

    public function testCheckCredentials_Twizo_Exception()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $twizoHelperMock->method("getWidgetSession")->willThrowException(new Exception("", 0));

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData('1', '33333333');

        $expected = array(
            "enabled"          => false,
            "credentialsCheck" => true,
            "sessionToken"     => null,
            "trustedDevice"    => false,
            "logoUrl"          => null
        );

        $result = $sut->checkCredentials($twizoData, 'henk', true, 'value');
        $this->assertArraySubset($expected, $result);
    }

    /* Test login*/

    public function testLogin_isTrusted()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $twizoHelperMock->method("isWidgetSuccess")->willReturn(true);

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $cookieMock = $this->getMockBuilder(\JInputCookie::class)->setMethods(["set"])->getMock();

        $cookieMock->expects($this->once())
            ->method("set")
            ->willReturn("");

        $twizoData = new TwizoData("1", "123456");

        /** @var \JInputCookie $cookieMock */
        $result = $sut->login($twizoData, true, null, true, $cookieMock);

        $this->assertEquals("success", $result);
    }

    public function testLogin_isTrusted_No_Number()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $twizoHelperMock->method("isWidgetSuccess")->willReturn(true);

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $this->expectExceptionMessage("Number not set.");

        $sut->login(new TwizoData(), true, null, true, null);
    }

    public function testLogin_Not_Trusted()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $twizoHelperMock->method("isWidgetSuccess")->willReturn(true);

        $twizoDataDAOMock->expects($this->never())->method("updateTwizoData");

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        /** @var \JInputCookie $cookieMock */
        $result = $sut->login(new TwizoData(), true, null, false, null);

        $this->assertEquals("success", $result);
    }

    public function testLogin_Credentials_Not_Validated()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $this->expectExceptionMessage('Error logging in. Credentials not verified');
        /** @var \JInputCookie $cookieMock */
        $sut->login(new TwizoData(), false, null, null, null);
    }

    public function testLogin_SessionToken_Not_Validated()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        // Status is Invalid
        $twizoHelperMock->method("isWidgetSuccess")->willReturn(false);

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $this->expectExceptionMessage('Session token is not validated.');
        $sut->login(new TwizoData(), true, null, null, null);
    }

    /* Test Register*/

    public function testRegister_SessionToken_No_BackupCodes()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $twizoHelperMock->method("isWidgetSuccess")->willReturn(true);

        $twizoSettingsHelperMock->method("backupCodeIsEnabled")->willReturn(false);

        $number        = "000000000";
        $preferredType = "call";
        // Expect a TwizoData model back
        $twizoDataDAOMock->expects($this->once())->method("updateTwizoData")->with(new TwizoData(1, $number, "123456", $preferredType));

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData();

        $expected = array(
            "backupCodesEnabled" => false
        );

        $user = (object) ["id" => 1];

        $result = $sut->register($twizoData, $user, "sessionTokenValue", $number, $preferredType, "123456");
        $this->assertArraySubset($expected, $result);
    }

    public function testRegister_SessionToken_BackupCodes()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $twizoHelperMock->method("isWidgetSuccess")->willReturn(true);
        $twizoSettingsHelperMock->method("backupCodeIsEnabled")->willReturn(true);

        $number        = "000000000";
        $preferredType = "call";
        $identifier    = '123456';

        // Expect the updateTwizoData method with the updated twizoData model.
        $twizoHelperMock->expects($this->once())
            ->method("generateBackupCodes")
            ->willReturn(array());

        $twizoDataDAOMock->expects($this->once())
            ->method("updateTwizoData")
            ->with(new TwizoData(1, $number, $identifier, $preferredType));

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData();
        $expected  = array(
            "backupCodesEnabled" => true,
            "backupCodes"        => array()
        );

        $user = (object) ["id" => 1];

        $result = $sut->register($twizoData, $user, "sessionTokenValue", $number, $preferredType, $identifier);
        $this->assertArraySubset($expected, $result);
    }

    public function testRegister_No_Number()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $this->expectExceptionMessage('Number is not set.');
        $sut->register(new TwizoData(), null, "sessionTokenValue", null, null, null);
    }

    public function testRegister_No_SessionToken()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $this->expectExceptionMessage('Session token is not set.');
        $sut->register(new TwizoData(), null, null, "000000000000", null, null);
    }

    public function testRegister_Already_Registered()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        $widgetSession = array("sessionToken" => "sessionTokenValue", "logoUrl" => "logoUrlValue");
        $twizoHelperMock->method("getWidgetSession")->willReturn($widgetSession);

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        //The TwizoData Model is filed.
        $twizoData = new TwizoData('1', "0000000000");

        $this->expectExceptionMessage('User is already registered.');
        $sut->register($twizoData, null, "sessionTokenValue", "000000000", null, null);
    }

    public function testRegister_SessionToken_Not_Validated()
    {
        list($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock) = $this->prepareUserLoginHelper();

        // Status is Invalid
        $twizoHelperMock->method("isWidgetSuccess")->willReturn(false);

        $sut = $this->getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData();

        $this->expectExceptionMessage('Session token is not validated.');
        $sut->register($twizoData, null, "sessionTokenValue", "000000000", "call", null);
    }

    /**
     * @param $twizoDataDAOMock
     * @param $userDataAccessMock
     * @param $twizoHelperMock
     * @param $trustedDeviceHelperMock
     * @param $twizoSettingsHelperMock
     *
     * @return \TwizoPlugin\Helpers\UserLoginHelper
     */
    private function getUserLoginHelperInstance($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock)
    {
        $sut = new UserLoginHelper($twizoDataDAOMock, $userDataAccessMock, $twizoHelperMock, $trustedDeviceHelperMock, $twizoSettingsHelperMock);

        return $sut;
    }
}

