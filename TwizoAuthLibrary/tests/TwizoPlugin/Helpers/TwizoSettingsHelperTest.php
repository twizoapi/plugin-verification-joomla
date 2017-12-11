<?php

namespace TwizoPlugin\Helpers;

use PHPUnit\Framework\TestCase;
use Twizo\Api\Entity\Application\VerificationTypes;
use Twizo\Api\Entity\Exception;
use Twizo\Api\Twizo;

/**
 * @package     tests\TwizoPlugin\Helpers
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TwizoSettingsHelperTest extends TestCase
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
     * @return array $twizoMock, $verificationTypesMock
     */
    public function prepareTwizoSettingsHelper()
    {
        $twizoMock             = $this->createMock(Twizo::class);
        $verificationTypesMock = $this->createMock(VerificationTypes::class);

        return array($twizoMock, $verificationTypesMock);
    }

    public function testGetInstance()
    {
        list($twizoMock) = $this->prepareTwizoSettingsHelper();

        /** @var Twizo $twizoMock */
        $twizoSettings = array();
        $result        = new TwizoSettingsHelper($twizoMock, $twizoSettings);

        $this->assertInstanceOf(TwizoSettingsHelper::class, $result);
    }

    public function testGetApiHosts()
    {
        list($twizoMock) = $this->prepareTwizoSettingsHelper();

        $twizoSettings = array();
        /** @var Twizo $twizoMock */
        $sut = new TwizoSettingsHelper($twizoMock, $twizoSettings);

        $result = $sut::getApiHosts();

        $expected = json_decode('{
            "asia-01": {
                "host": "api-asia-01.twizo.com",
                "callbackHost": "api-asia-01-out.twizo.com",
                "location": "Singapore"
            },
            "eu-01": {
                "host": "api-eu-01.twizo.com",
                "callbackHost": "api-eu-01-out.twizo.com",
                "location": "Germany"
            }
        }', true);

        $this->assertArraySubset($expected, $result);
    }

    /* Test Get verificationTypes*/

    public function testGetVerificationTypes()
    {
        list($twizoMock, $verificationTypesMock) = $this->prepareTwizoSettingsHelper();

        $expected = array("sms", "call");

        $verificationTypesMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn($expected);

        $twizoMock->expects($this->once())
            ->method("getVerificationTypes")
            ->willReturn($verificationTypesMock);

        /** @var Twizo $twizoMock */
        $sut = new TwizoSettingsHelper($twizoMock, $expected);

        $this->assertEquals($expected, $sut->getVerificationTypes());
    }

    public function testGetVerificationTypes_Empty()
    {
        list($twizoMock, $verificationTypesMock) = $this->prepareTwizoSettingsHelper();

        $expected = array();
        $verificationTypesMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn($expected);

        $twizoMock->expects($this->once())
            ->method("getVerificationTypes")
            ->willReturn($verificationTypesMock);

        /** @var Twizo $twizoMock */
        $sut = new TwizoSettingsHelper($twizoMock, $expected);

        $this->assertEquals($expected, $sut->getVerificationTypes());
    }

    public function testGetVerificationTypes_Exception()
    {
        list($twizoMock, $verificationTypesMock) = $this->prepareTwizoSettingsHelper();

        $expected = array();

        $verificationTypesMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willThrowException(new Exception("", 0));

        $twizoMock->expects($this->once())
            ->method("getVerificationTypes")
            ->willReturn($verificationTypesMock);

        /** @var Twizo $twizoMock */
        $sut = new TwizoSettingsHelper($twizoMock, $expected);

        $this->assertEquals($expected, $sut->getVerificationTypes());
    }

    /* Test Get preferredTypes*/

    public function testGetPreferredTypes_BackupCode()
    {
        list($twizoMock, $verificationTypesMock) = $this->prepareTwizoSettingsHelper();

        $verificationTypesMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn(array("sms", "call", "backupcode"));

        $twizoMock->expects($this->once())
            ->method("getVerificationTypes")
            ->willReturn($verificationTypesMock);

        $expected = array("sms", "call");
        /** @var Twizo $twizoMock */
        $sut = new TwizoSettingsHelper($twizoMock, $expected);

        $this->assertEquals($expected, $sut->getPreferredTypes());
    }

    /* Test backup Code Is Enabled*/

    public function testBackupCodeIsEnabled_Enabled()
    {
        list($twizoMock, $verificationTypesMock) = $this->prepareTwizoSettingsHelper();

        $settings = array("sms", "call", "backupcode");
        $verificationTypesMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn($settings);

        $twizoMock->expects($this->once())
            ->method("getVerificationTypes")
            ->willReturn($verificationTypesMock);

        /** @var Twizo $twizoMock */
        $sut = new TwizoSettingsHelper($twizoMock, $settings);

        $this->assertTrue($sut->backupCodeIsEnabled());
    }

    public function testBackupCodeIsEnabled_Disabled()
    {
        list($twizoMock, $verificationTypesMock) = $this->prepareTwizoSettingsHelper();

        $settings = array("sms", "call");
        $verificationTypesMock->expects($this->any())
            ->method("getVerificationTypes")
            ->willReturn($settings);

        $twizoMock->expects($this->once())
            ->method("getVerificationTypes")
            ->willReturn($verificationTypesMock);

        /** @var Twizo $twizoMock */
        $sut = new TwizoSettingsHelper($twizoMock, $settings);

        $this->assertFalse($sut->backupCodeIsEnabled());
    }

    /* Test get default validation*/

    public function testGetDefaultValidation()
    {
        list($twizoMock) = $this->prepareTwizoSettingsHelper();

        $expected = "sms";
        $settings = array("default_validation" => $expected);

        /** @var Twizo $twizoMock */
        $sut = new TwizoSettingsHelper($twizoMock, $settings);

        $this->assertEquals($expected, $sut->getDefaultValidation());
    }

    /* Test get sender*/

    public function testGetSender()
    {
        list($twizoMock) = $this->prepareTwizoSettingsHelper();

        $expected = "testSender";
        $settings = array("sender" => $expected);

        /** @var Twizo $twizoMock */
        $sut = new TwizoSettingsHelper($twizoMock, $settings);

        $this->assertEquals($expected, $sut->getSender());
    }

    /* Test get widget logo*/

    public function testGetWidgetLogo()
    {
        list($twizoMock) = $this->prepareTwizoSettingsHelper();

        $expected = "https://img.test.com/img.jpg";
        $settings = array("widget_logo" => $expected);

        /** @var Twizo $twizoMock */
        $sut = new TwizoSettingsHelper($twizoMock, $settings);

        $this->assertEquals($expected, $sut->getWidgetLogo());
    }
}
