<?php

namespace TwizoPlugin\Helpers;

defined('_JEXEC') || die;

use Twizo\Api\Exception;
use Twizo\Api\Twizo;
use Twizo\Api\TwizoInterface;
use TwizoPlugin\Model\TwizoData;

/**
 * @package     TwizoPlugin\Helpers
 *
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TwizoHelper
{
    /**
     * @var Twizo
     *
     * @since 0.1.0
     */
    private $twizo;

    /**
     * @var TwizoSettingsHelper
     * @since 0.1.01
     */
    private $twizoSettingsHelper;

    /**
     * TwizoFunctions constructor.
     *
     * @param TwizoInterface      $twizo
     * @param TwizoSettingsHelper $twizoSettingsHelper
     *
     * @since    0.1.0
     */
    public function __construct($twizo, $twizoSettingsHelper)
    {
        $this->twizo               = $twizo;
        $this->twizoSettingsHelper = $twizoSettingsHelper;
    }

    /**
     * returns a new widget session
     *
     * @param TwizoData $twizoData
     * @param array     $allowedTypes
     *
     * @return array [$sessionToken, null|$logoUrl]
     * @throws Exception
     * @since 0.1.0
     */
    public function getWidgetSession($twizoData, $allowedTypes = array())
    {
        // When recipient is not set a error is thrown.
        if (is_null($twizoData->getNumber()))
            throw new Exception('Recipient is not set');
        if (!$this->twizoSettingsHelper->enabled())
            throw new Exception('Plugin not enabled');

        $identifier        = $twizoData->getIdentifier();
        $recipient         = $twizoData->getNumber();
        $preferredType     = $twizoData->getPreferredType();
        $widgetSession     = $this->twizo->createWidgetSession();
        $verificationTypes = $this->twizoSettingsHelper->getVerificationTypes();

        // Check if $verificationTypes is overwritten
        $enabledValidations = empty($allowedTypes) ? $verificationTypes : $allowedTypes;

        // Set the preferred type to user favourite or default type set by admin.
        if (!is_null($preferredType) && $preferredType !== '')
            $widgetSession->setPreferredType($preferredType);
        else
            $widgetSession->setPreferredType($this->twizoSettingsHelper->getDefaultValidation());

        //Check if backupCodes are enabled in settings and user identifier is set
        if (isset($identifier) && in_array('backupcode', $enabledValidations))
            $widgetSession->setBackupCodeIdentifier($identifier);

        //Check if sender is set in settings and if sms is enabled.
        if (in_array('sms', $enabledValidations))
            $widgetSession->setSender($this->twizoSettingsHelper->getSender());

        $widgetSession->setRecipient($recipient);
        $widgetSession->setAllowedTypes($enabledValidations);
        // Set service tag for usage statistics
        $widgetSession->setTag("Joomla!");

        // Make the prepared widget session.
        $widgetSession->create();

        return array(
            "sessionToken" => $widgetSession->getSessionToken(),
            "logoUrl"      => $this->twizoSettingsHelper->getWidgetLogo()
        );
    }

    /**
     * @param           $sessionToken
     * @param TwizoData $twizoData
     *
     * @return bool
     * @throws Exception
     *
     * @since    0.1.0
     */
    public function isWidgetSuccess($sessionToken, $twizoData)
    {
        if (!isset($sessionToken))
            throw new Exception('Session token is not set.');

        if (empty($twizoData->getNumber()))
            throw new Exception('Recipient can\'t be assigned.');

        try
        {
            $widgetResult = $this->twizo->getWidgetSession($sessionToken, $twizoData->getNumber());

            return $widgetResult->getStatus() === 'success';
        }
        catch (\Twizo\Api\Entity\Exception $exception)
        {
            throw new Exception('Session token not found.');
        }
    }

    /**
     * @param $identifier
     *
     * @return array
     * @throws Exception
     * @since 0.1.0
     */
    public function generateBackupCodes($identifier)
    {
        if (!$this->twizoSettingsHelper->backupCodeIsEnabled())
            throw new Exception("Backup codes are not enabled.");

        $backupCode = $this->twizo->createBackupCode($identifier);
        $backupCode->create();

        return $backupCode->getCodes();
    }

    /**
     * Updates backup codes for a existing identifier and returns them.
     *
     * @param TwizoData $twizoData
     *
     * @return array
     * @throws Exception
     * @since 0.1.0
     */
    public function updateBackupCodes($twizoData)
    {
        if (!$this->twizoSettingsHelper->backupCodeIsEnabled())
            throw new Exception("Backup codes are not enabled.");

        try
        {
            $backupCode = $this->twizo->getBackupCode($twizoData->getIdentifier());

            // Weird syntax in twizo lib. Have to set the identifier again manually
            $backupCode->setIdentifier($twizoData->getIdentifier());

            $backupCode->update();
        }
        catch (\Twizo\Api\Entity\BackupCode\Exception $ignored)
        {
            return $this->generateBackupCodes($twizoData->getIdentifier());
        }

        return $backupCode->getCodes();
    }

    /**
     * @param $identifier
     *
     * @return int|null
     * @since 0.1.0
     */
    public function getAmountOfBackupCodes($identifier)
    {
        $backupCode = $this->twizo->getBackupCode($identifier);

        return $backupCode->getAmountOfCodesLeft();
    }

    /**
     * @return float
     *
     * @since 0.1.0
     */
    public function getCreditBalance()
    {
        return $this->twizo->getBalance()->getCredit();
    }

    /**
     * Check if api key is valid.
     * Returns:
     *          0 if key is invalid
     *          1 if key is valid
     *          2 if key is valid test key
     *
     * @return integer
     * @since 0.1.0
     */
    public function getKeyType()
    {
        try
        {
            return $this->twizo->verifyCredentials()->getIsTestKey() ? 2 : 1;
        }
        catch (\Twizo\Api\Entity\Exception $exception)
        {
            return 0;
        }
    }

    /**
     * @return string
     *
     * @since 0.1.0
     */
    public function getApplicationTag()
    {
        // Made this function as a wrapper because I expect things to change
        return $this->twizo->verifyCredentials()->getApplicationTag();
    }
}