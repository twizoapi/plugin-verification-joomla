<?php

namespace TwizoPlugin\Helpers;

defined('_JEXEC') || die;

use Twizo\Api\Exception;
use TwizoPlugin\DataSource\DataAccessObjects\TwizoDataDAO;
use TwizoPlugin\DataSource\UserDataAccess;
use TwizoPlugin\Model\TrustedDevice;
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
class UserLoginHelper
{
    const WIDGET_SUCCESS = 'success';

    /**
     * @var TwizoDataDAO
     * @since 0.1.0
     */
    private $twizoDataDAO;

    /**
     * @var TwizoHelper
     * @since 0.1.0
     */
    private $twizoHelper;

    /**
     * @var UserDataAccess
     * @since 0.1.0
     */
    private $userDataAccess;

    /**
     * @var TrustedDeviceHelper
     * @since 0.1.0
     */
    private $trustedDeviceHelper;
    /**
     * @var TwizoSettingsHelper
     * @since 0.1.0
     */
    private $twizoSettingsHelper;

    /**
     * UserFunctions constructor.
     *
     * @param TwizoDataDAO        $twizoDataDAO
     * @param userDataAccess      $userDataAccess
     * @param TwizoHelper         $twizoHelper
     * @param TrustedDeviceHelper $trustedDeviceHelper
     * @param TwizoSettingsHelper $twizoSettingsHelper
     *
     * @since 0.1.0
     */
    public function __construct($twizoDataDAO, $userDataAccess, $twizoHelper, $trustedDeviceHelper, $twizoSettingsHelper)
    {
        $this->twizoDataDAO        = $twizoDataDAO;
        $this->userDataAccess      = $userDataAccess;
        $this->twizoHelper         = $twizoHelper;
        $this->trustedDeviceHelper = $trustedDeviceHelper;
        $this->twizoSettingsHelper = $twizoSettingsHelper;
    }

    /**
     * Returns session token in array
     *
     * @param TwizoData $twizoData
     * @param           $username
     * @param           $checkedCredentials
     * @param           $cookieValue
     *
     * @return array
     * @throws Exception
     * @since 0.1.0
     */
    public function checkCredentials($twizoData, $username, $checkedCredentials, $cookieValue)
    {
        if (!isset($username))
            throw new Exception('Username not set.');

        $result = array(
            "enabled"          => false,
            "credentialsCheck" => $checkedCredentials,
            "sessionToken"     => null,
            "trustedDevice"    => false,
            "logoUrl"          => null
        );

        if ($checkedCredentials && !empty($twizoData->getNumber()) && $this->twizoSettingsHelper->enabled())
        {
            $result["trustedDevice"] = $this->trustedDeviceHelper->checkCookie($cookieValue, $username, $twizoData->getNumber());
            $result['enabled']       = true;

            if ($result['credentialsCheck'] && !$result["trustedDevice"])
            {
                try
                {
                    $widgetSession          = $this->twizoHelper->getWidgetSession($twizoData);
                    $result["sessionToken"] = $widgetSession["sessionToken"];
                    $result["logoUrl"]      = $widgetSession["logoUrl"];
                }
                catch (\Twizo\Api\Entity\Exception $exception)
                {
                    // If a twizo exception occurs set the enabled status of the plugin back to false.
                    $result['trustedDevice'] = false;
                    $result['enabled']       = false;
                }

            }
        }

        return $result;
    }

    /**
     * @param TwizoData     $twizoData
     *
     * @param               $checkedCredentials
     * @param               $sessionToken
     * @param               $isTrusted
     * @param \JInputCookie $inputCookie
     *
     * @return array|string
     * @throws Exception
     */
    public function login($twizoData, $checkedCredentials, $sessionToken, $isTrusted, $inputCookie)
    {
        if (!$checkedCredentials)
            throw new Exception('Error logging in. Credentials not verified');
        if (!$this->twizoHelper->isWidgetSuccess($sessionToken, $twizoData))
            throw new Exception("Session token is not validated.");

        // If device is a trusted device save it as one in the DB and set a cookie
        if ($isTrusted)
        {
            if (is_null($twizoData->getNumber()))
                throw new Exception("Number not set.");

            $token         = bin2hex(random_bytes(30));
            $recipient     = $twizoData->getNumber();
            $hashedVersion = hash('sha512', sprintf('%s_%s', $recipient, $token));

            $newDevice = new TrustedDevice(
                null, $hashedVersion, date('Y-m-d H:i:s')
            );

            // Add new device to twizo data
            $trustedDevices = $twizoData->getTrustedDevices();
            array_push($trustedDevices, $newDevice);
            $twizoData->setTrustedDevices($trustedDevices);

            $inputCookie->set('twizoData', $token, strtotime('30 days'), '/', '', true, true);

            // Update the edited twizo data object
            $this->twizoDataDAO->updateTwizoData($twizoData);
        }

        return self::WIDGET_SUCCESS;
    }

    /**
     * @param TwizoData $twizoData
     *
     * @param           $user
     * @param           $sessionToken
     * @param           $number
     * @param           $preferredType
     * @param           $identifier
     *
     * @return array
     * @throws Exception
     * @since 0.1.0
     */
    public function register($twizoData, $user, $sessionToken, $number, $preferredType, $identifier)
    {
        $twizoData->setNumber($number);
        if (!isset($number))
            throw new Exception('Number is not set.');
        if (!isset($sessionToken))
            throw new Exception('Session token is not set.');
        if ($twizoData->getId() !== null)
            throw new Exception('User is already registered.');

        // Check if widget is validated successfully.
        if (!$this->twizoHelper->isWidgetSuccess($sessionToken, $twizoData))
            throw new Exception("Session token is not validated.");

        // Add the new twizo data object to the db
        $twizoData->setId($user->id);
        $twizoData->setPreferredType($preferredType);

        $result = array(
            "backupCodesEnabled" => false
        );

        // Get a unique id to generate the backup codes
        $twizoData->setIdentifier($identifier);

        if ($this->twizoSettingsHelper->backupCodeIsEnabled())
        {
            $result["backupCodesEnabled"] = true;
            $result["backupCodes"]        = $this->twizoHelper->generateBackupCodes($identifier);
        }

        $this->twizoDataDAO->updateTwizoData($twizoData);

        return $result;
    }
}