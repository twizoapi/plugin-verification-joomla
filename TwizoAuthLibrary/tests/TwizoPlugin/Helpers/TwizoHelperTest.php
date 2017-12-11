<?php

namespace TwizoPlugin\Helpers;

use PHPUnit\Framework\TestCase;
use Twizo\Api\Entity\Application\VerifyCredentials;
use Twizo\Api\Entity\BackupCode;
use Twizo\Api\Entity\Balance;
use Twizo\Api\Entity\Exception;
use Twizo\Api\Entity\WidgetSession;
use Twizo\Api\TwizoInterface;
use TwizoPlugin\Model\TwizoData;

/**
 * @package     tests\TwizoPlugin\Helpers
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TwizoHelperTest extends TestCase
{
    /**
     * @param $twizoMock
     * @param $twizoSettingsHelper
     *
     * @return TwizoHelper
     */
    public function getTwizoHelper($twizoMock, $twizoSettingsHelper)
    {
        $sut = new TwizoHelper($twizoMock, $twizoSettingsHelper);

        return $sut;
    }

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
     * Create mocks for multiple tests
     *
     * @returns array $twizoMock, $widgetSessionMock, $backupCodeMock, $balanceMock, $verifyCredentialsMock
     * @since 0.1.0
     */
    public function prepareTwizoHelper()
    {
        $widgetSessionMock       = $this->createMock(WidgetSession::class);
        $twizoSettingsHelperMock = $this->createMock(TwizoSettingsHelper::class);
        $twizoMock               = $this->createMock(TwizoInterface::class);
        $backupCodeMock          = $this->createMock(BackupCode::class);
        $balanceMock             = $this->createMock(Balance::class);
        $verifyCredentialsMock   = $this->createMock(VerifyCredentials::class);

        return array($twizoMock, $twizoSettingsHelperMock, $widgetSessionMock, $backupCodeMock, $balanceMock, $verifyCredentialsMock);
    }

    public function testGetInstance()
    {
        list($twizoMock, $twizoSettingsHelperMock) = $this->prepareTwizoHelper();

        $result = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $this->assertInstanceOf(TwizoHelper::class, $result);
    }

    /* Test Get Widget Session*/

    public function testGetWidgetSession_Success()
    {
        list($twizoMock, $twizoSettingsHelperMock, $widgetSessionMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn(["sms", "call"]);

        $twizoSettingsHelperMock->expects($this->any())
            ->method("getWidgetLogo")
            ->willReturn("logoUrlValue");

        $widgetSessionMock->expects($this->any())
            ->method("getSessionToken")
            ->willReturn("sessionTokenValue");

        $twizoMock->expects($this->any())
            ->method("createWidgetSession")
            ->willReturn($widgetSessionMock);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $expected = array("sessionToken" => "sessionTokenValue",
                          "logoUrl"      => "logoUrlValue");

        $twizoData = new TwizoData("1", "0000000000", "0000000000", "null");

        $this->assertArraySubset($expected, $sut->getWidgetSession($twizoData));
    }

    public function testGetWidgetSession_PreferredType()
    {
        list($twizoMock, $twizoSettingsHelperMock, $widgetSessionMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn(["sms", "call"]);

        // Assert expect parameter 'sms'
        $expected = 'call';
        $widgetSessionMock->expects($this->once())
            ->method("setPreferredType")
            ->with($expected);


        $widgetSessionMock->expects($this->any())
            ->method("getSessionToken")
            ->willReturn("sessionTokenValue");

        $twizoMock->expects($this->any())
            ->method("createWidgetSession")
            ->willReturn($widgetSessionMock);


        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData("1", "0000000000", "0000000000", $expected);
        $sut->getWidgetSession($twizoData);
    }

    public function testGetWidgetSession_Default_Validation()
    {
        list($twizoMock, $twizoSettingsHelperMock, $widgetSessionMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn(["sms", "call"]);

        $expected = 'call';
        $twizoSettingsHelperMock->expects($this->once())
            ->method("getDefaultValidation")
            ->willReturn($expected);

        // Assert expect parameter 'sms'
        $widgetSessionMock->expects($this->once())
            ->method("setPreferredType")
            ->with($expected);

        $widgetSessionMock->expects($this->any())
            ->method("getSessionToken")
            ->willReturn("sessionTokenValue");

        $twizoMock->expects($this->any())
            ->method("createWidgetSession")
            ->willReturn($widgetSessionMock);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        // No preferred type in twizo data.
        $twizoData = new TwizoData("1", "0000000000", "0000000000", null);

        $sut->getWidgetSession($twizoData);
    }

    public function testGetWidgetSession_No_Recipient()
    {
        list($twizoMock, $twizoSettingsHelperMock, $widgetSessionMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn(["sms", "call"]);

        $twizoMock->expects($this->any())
            ->method("createWidgetSession")
            ->willReturn($widgetSessionMock);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData("1", null);

        $this->expectExceptionMessage("Recipient is not set");
        $sut->getWidgetSession($twizoData);
    }

    public function testGetWidgetSession_BackupCode_Enabled()
    {
        list($twizoMock, $twizoSettingsHelperMock, $widgetSessionMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn(["sms", "call", "backupcode"]);

        $twizoSettingsHelperMock->method('backupCodeIsEnabled')->willReturn(true);

        // Assert expect parameter '123456'
        $expected = '123456';
        $widgetSessionMock->expects($this->once())
            ->method("setBackupCodeIdentifier")
            ->with($expected);


        $widgetSessionMock->expects($this->any())
            ->method("getSessionToken")
            ->willReturn("sessionTokenValue");

        $twizoMock->expects($this->any())
            ->method("createWidgetSession")
            ->willReturn($widgetSessionMock);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        // No preferred type in twizo data.

        $twizoData = new TwizoData("1", "0000000000", $expected, null);
        $sut->getWidgetSession($twizoData);
    }

    public function testGetWidgetSession_BackupCode_Enabled_No_Identifier()
    {
        list($twizoMock, $twizoSettingsHelperMock, $widgetSessionMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn(["sms", "call", "backupcode"]);

        $twizoSettingsHelperMock->method('backupCodeIsEnabled')->willReturn(true);

        // Assert expect method to never be called
        $widgetSessionMock->expects($this->never())
            ->method("setBackupCodeIdentifier");

        $widgetSessionMock->expects($this->any())
            ->method("getSessionToken")
            ->willReturn("sessionTokenValue");

        $twizoMock->expects($this->any())
            ->method("createWidgetSession")
            ->willReturn($widgetSessionMock);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData("1", "0000000000", null, null);
        $sut->getWidgetSession($twizoData);
    }

    public function testGetWidgetSession_BackupCode_Not_Enabled()
    {
        list($twizoMock, $twizoSettingsHelperMock, $widgetSessionMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->method('backupCodeIsEnabled')->willReturn(false);

        $twizoSettingsHelperMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn(["sms", "call"]);

        // Assert expect method to never be called
        $widgetSessionMock->expects($this->never())
            ->method("setBackupCodeIdentifier");

        $twizoMock->expects($this->any())
            ->method("createWidgetSession")
            ->willReturn($widgetSessionMock);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData("1", "0000000000", 123456, null);
        $sut->getWidgetSession($twizoData);
    }

    public function testGetWidgetSession_Set_Sender()
    {
        list($twizoMock, $twizoSettingsHelperMock, $widgetSessionMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn(["sms", "call"]);

        // Assert expect method to never be called
        $widgetSessionMock->expects($this->once())
            ->method("setSender");

        $widgetSessionMock->expects($this->any())
            ->method("getSessionToken")
            ->willReturn("sessionTokenValue");

        $twizoMock->expects($this->any())
            ->method("createWidgetSession")
            ->willReturn($widgetSessionMock);


        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData("1", "0000000000", 123456, null);
        $sut->getWidgetSession($twizoData);
    }

    public function testGetWidgetSession_Sms_Disabled()
    {
        list($twizoMock, $twizoSettingsHelperMock, $widgetSessionMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn(["call"]);

        // Assert expect method to never be called
        $widgetSessionMock->expects($this->never())
            ->method("setSender");

        $widgetSessionMock->expects($this->any())
            ->method("getSessionToken")
            ->willReturn("sessionTokenValue");

        $twizoMock->expects($this->any())
            ->method("createWidgetSession")
            ->willReturn($widgetSessionMock);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData("1", "0000000000", 123456, null);
        $sut->getWidgetSession($twizoData);
    }

    public function testGetWidgetSession_No_Sender()
    {
        list($twizoMock, $twizoSettingsHelperMock, $widgetSessionMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn(["sms", "call"]);

        $expected = null;
        $twizoSettingsHelperMock->expects($this->once())
            ->method("getSender")
            ->willReturn($expected);

        // Assert expect method to never be called
        $widgetSessionMock->expects($this->once())
            ->method("setSender")
            ->with($expected);

        $widgetSessionMock->expects($this->any())
            ->method("getSessionToken")
            ->willReturn("sessionTokenValue");

        $twizoMock->expects($this->any())
            ->method("createWidgetSession")
            ->willReturn($widgetSessionMock);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData("1", "0000000000", 123456, null);
        $sut->getWidgetSession($twizoData);
    }

    public function testGetWidgetSession_No_LogoUrl()
    {
        list($twizoMock, $twizoSettingsHelperMock, $widgetSessionMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn(["sms", "call"]);

        $expected = null;
        $twizoSettingsHelperMock->expects($this->once())
            ->method("getWidgetLogo")
            ->willReturn($expected);

        $widgetSessionMock->expects($this->any())
            ->method("getSessionToken")
            ->willReturn("sessionTokenValue");

        $twizoMock->expects($this->any())
            ->method("createWidgetSession")
            ->willReturn($widgetSessionMock);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $expected = array("sessionToken" => "sessionTokenValue",
                          "logoUrl"      => $expected);

        $twizoData = new TwizoData("1", "0000000000", "0000000000", "null");

        $this->assertArraySubset($expected, $sut->getWidgetSession($twizoData));
    }

    public function testGetWidgetStatus_Successful()
    {
        list($twizoMock, , $widgetSessionMock) = $this->prepareTwizoHelper();

        $widgetSessionMock->expects($this->any())
            ->method("getStatus")
            ->willReturn("success");

        $twizoMock->expects($this->any())
            ->method("getWidgetSession")
            ->willReturn($widgetSessionMock);

        $sut = $this->getTwizoHelper($twizoMock, null);

        $twizoData = new TwizoData("0", "0000000000", "0000000000", null);
        $result    = $sut->isWidgetSuccess("sessionTokenValue", $twizoData);

        $this->assertTrue($result);
    }

    public function testGetWidgetStatus_No_SessionToken()
    {
        list($twizoMock) = $this->prepareTwizoHelper();

        $sut = $this->getTwizoHelper($twizoMock, null);

        $this->expectExceptionMessage('Session token is not set.');

        $sut->isWidgetSuccess(null, new TwizoData());
    }

    public function testGetWidgetStatus_No_Number()
    {
        list($twizoMock) = $this->prepareTwizoHelper();

        $sut = $this->getTwizoHelper($twizoMock, null);

        $this->expectExceptionMessage('Recipient can\'t be assigned.');

        $sut->isWidgetSuccess("sessionTokenValue", new TwizoData());
    }

    public function testGetWidgetStatus_Unknown_SessionToken()
    {
        list($twizoMock, , $widgetSessionMock) = $this->prepareTwizoHelper();

        $ex = $ex = new Exception(
            '',
            0,
            0,
            null
        );

        $widgetSessionMock->expects($this->any())
            ->method("getStatus")
            ->willThrowException($ex);

        $twizoMock->expects($this->any())
            ->method("getWidgetSession")
            ->willReturn($widgetSessionMock);

        $sut = $this->getTwizoHelper($twizoMock, null);

        $this->expectExceptionMessage('Session token not found.');
        $sut->isWidgetSuccess("sessionTokenValue", new TwizoData("1", "00000"));
    }

    /* Test Generate Backup Codes*/

    public function testGenerateBackupCodes()
    {
        list($twizoMock, $twizoSettingsHelperMock, , $backupCodeMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->method('backupCodeIsEnabled')->willReturn(true);

        $expected = array("expected");
        $backupCodeMock->expects($this->any())
            ->method("getCodes")
            ->willReturn($expected);

        $twizoMock->expects($this->any())
            ->method("createBackupCode")
            ->willReturn($backupCodeMock);

        $sut    = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);
        $result = $sut->generateBackupCodes("identifier");

        $this->assertEquals($expected, $result);
    }

    public function testGenerateBackupCodes_Not_Enabled()
    {
        list($twizoMock, $twizoSettingsHelperMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->method('backupCodeIsEnabled')->willReturn(false);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $this->expectExceptionMessage("Backup codes are not enabled.");

        $sut->generateBackupCodes("identifier");
    }

    /* Test Update Backup Codes*/

    public function testUpdateBackupCodes()
    {
        list($twizoMock, $twizoSettingsHelperMock, , $backupCodeMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->method('backupCodeIsEnabled')->willReturn(true);

        $backupCodeMock->expects($this->any())
            ->method("getCodes")
            ->willReturn(array());

        $twizoMock->expects($this->any())
            ->method("getBackupCode")
            ->willReturn($backupCodeMock);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData("0", "0000000000", "0000000000", null);

        $result = $sut->updateBackupCodes($twizoData);

        $this->assertEquals(array(), $result);
    }

    public function testUpdateBackupCodes_Not_Enabled()
    {
        list($twizoMock, $twizoSettingsHelperMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->method('backupCodeIsEnabled')->willReturn(false);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $twizoData = new TwizoData("0", "0000000000", "0000000000", null);

        $this->expectExceptionMessage("Backup codes are not enabled.");
        $sut->updateBackupCodes($twizoData);
    }

    public function testUpdateBackupCodes_Exception()
    {
        list($twizoMock, $twizoSettingsHelperMock, , $backupCodeMock) = $this->prepareTwizoHelper();

        $twizoSettingsHelperMock->method('backupCodeIsEnabled')->willReturn(true);

        $ex = new BackupCode\Exception(new Exception(
            '',
            0,
            0,
            null
        ));

        $backupCodeMock->expects($this->any())
            ->method("update")
            ->willThrowException($ex);

        $twizoMock->expects($this->any())
            ->method("getBackupCode")
            ->willReturn($backupCodeMock);

        // Set method stubs for "generateBackupCodes" Method.
        $expected = "success";
        $backupCodeMock->expects($this->any())
            ->method("getCodes")
            ->willReturn($expected);

        $twizoMock->expects($this->any())
            ->method("createBackupCode")
            ->willReturn($backupCodeMock);

        $sut       = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);
        $twizoData = new TwizoData("0", "0000000000", "0000000000", null);
        $this->assertEquals($expected, $sut->updateBackupCodes($twizoData));
    }

    /* Test Get Amount Of Backup Codes*/

    public function testGetAmountOfBackupCodes()
    {
        list($twizoMock, $twizoSettingsHelperMock, , $backupCodeMock) = $this->prepareTwizoHelper();

        $backupCodeMock->expects($this->any())
            ->method("getAmountOfCodesLeft")
            ->willReturn(0);

        $twizoMock->expects($this->any())
            ->method("getBackupCode")
            ->willReturn($backupCodeMock);

        $sut    = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);
        $result = $sut->getAmountOfBackupCodes("identifier");

        $this->assertEquals(0, $result);
    }

    /* Test Get Credit Balance*/

    public function testGetCreditBalance()
    {
        list($twizoMock, $twizoSettingsHelperMock, , , $balanceMock) = $this->prepareTwizoHelper();

        $balanceMock->expects($this->any())
            ->method("getCredit")
            ->willReturn(0);

        $twizoMock->expects($this->any())
            ->method('getBalance')
            ->willReturn($balanceMock);

        $sut    = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);
        $result = $sut->getCreditBalance();

        $this->assertEquals(0, $result);
    }

    /* Test Get Key Type*/

    public function testGetKeyType_Invalid_Key()
    {
        list($twizoMock, $twizoSettingsHelperMock) = $this->prepareTwizoHelper();

        /**@var Exception $twizoApiEntityExceptionMock */

        $ex = new Exception(
            '',
            0,
            0,
            null
        );

        $twizoMock->expects($this->any())
            ->method('verifyCredentials')
            ->willThrowException($ex);

        $sut    = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);
        $result = $sut->getKeyType();

        $this->assertEquals(0, $result);
    }

    public function testGetKeyType_Test_Key()
    {
        list($twizoMock, $twizoSettingsHelperMock, , , , $verifyCredentialsMock) = $this->prepareTwizoHelper();

        $verifyCredentialsMock->expects($this->any())
            ->method('getIsTestKey')
            ->willReturn(false);

        $twizoMock->expects($this->any())
            ->method('verifyCredentials')
            ->willReturn($verifyCredentialsMock);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $result = $sut->getKeyType();

        $this->assertEquals(1, $result);
    }

    public function testGetKeyType_Valid_Key()
    {
        list($twizoMock, $twizoSettingsHelperMock, , , , $verifyCredentialsMock) = $this->prepareTwizoHelper();

        $verifyCredentialsMock->expects($this->any())
            ->method('getIsTestKey')
            ->willReturn(true);

        $twizoMock->expects($this->any())
            ->method('verifyCredentials')
            ->willReturn($verifyCredentialsMock);

        $sut    = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);
        $result = $sut->getKeyType();

        $this->assertEquals(2, $result);
    }

    /* Test Get Application Tag*/

    public function testGetApplicationTag()
    {
        list($twizoMock, $twizoSettingsHelperMock, , , , $verifyCredentialsMock) = $this->prepareTwizoHelper();

        $expected = 'app';
        $verifyCredentialsMock->expects($this->any())
            ->method('getApplicationTag')
            ->willReturn($expected);

        $twizoMock->expects($this->any())
            ->method('verifyCredentials')
            ->willReturn($verifyCredentialsMock);

        $sut = $this->getTwizoHelper($twizoMock, $twizoSettingsHelperMock);

        $this->assertEquals($expected, $sut->getApplicationTag());
    }
}
