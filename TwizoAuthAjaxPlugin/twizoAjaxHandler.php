<?php

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\Registry\Registry;
use Twizo\Api\Exception;
use Twizo\Api\Twizo;
use TwizoPlugin\DataSource\DataAccessObjects\TrustedDeviceDAO;
use TwizoPlugin\DataSource\DataAccessObjects\TwizoDataDAO;
use TwizoPlugin\DataSource\UserDataAccess;
use TwizoPlugin\Exceptions\TwizoDataException;
use TwizoPlugin\Helpers\TrustedDeviceHelper;
use TwizoPlugin\Helpers\TwizoHelper;
use TwizoPlugin\Helpers\TwizoSettingsHelper;
use TwizoPlugin\Helpers\UserLoginHelper;
use TwizoPlugin\Model\TwizoData;

/**
 * @package     TwizoAuthAjaxPlugin
 *
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class plgAjaxTwizoAjaxHandler extends CMSPlugin
{
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
     * @var UserLoginHelper
     * @since 0.1.0
     */
    private $userLoginHelper;

    /**
     * @var TwizoData
     * @since 0.1.0
     */
    private $twizoData;

    /**
     * @var UserDataAccess
     * @since 0.1.0
     */
    private $userDataAccess;

    public function __construct($subject, array $config = array())
    {
        parent::__construct($subject, $config);

        $plugin        = PluginHelper::getPlugin('twofactorauth', 'TwizoTwoFactorAuth');
        $twizoSettings = (new Registry($plugin->params))->toArray();

        $apiKey  = isset($twizoSettings['api_key']) ? $twizoSettings['api_key'] : null;
        $apiHost = isset($twizoSettings['api_host']) ? $twizoSettings['api_host'] : null;
        $twizo   = Twizo::getInstance($apiKey, $apiHost);
        $db      = Factory::getDbo();

        $twizoSettingsHelper = new TwizoSettingsHelper($twizo, $twizoSettings);
        $trustedDeviceDAO    = new TrustedDeviceDAO($db);
        $trustedDeviceHelper = new TrustedDeviceHelper($trustedDeviceDAO);

        $this->twizoHelper     = new TwizoHelper($twizo, $twizoSettingsHelper);
        $this->twizoDataDAO    = new TwizoDataDAO($trustedDeviceHelper, $trustedDeviceDAO, $db);
        $this->userDataAccess  = new userDataAccess($db, $this->twizoHelper, $this->twizoDataDAO);
        $this->userLoginHelper = new UserLoginHelper($this->twizoDataDAO, $this->userDataAccess, $this->twizoHelper, $trustedDeviceHelper, $twizoSettingsHelper);
    }

    /**
     * @return array | string
     *
     * @throws Exception
     * @since    0.1.0
     */
    public function onAjaxTwizoAjaxHandler()
    {
        list($functionName, $username, $password, $sessionToken, $number, $preferredMethod, $isTrusted) = $this->getVars();

        $this->setTwizoData($username, $number);

        switch ($functionName)
        {
            case 'checkCredentials':
                return $this->checkCredentials($username, $password);

            case 'validateWidget':
                return $this->validateWidget($sessionToken);

            case 'login':
                return $this->login($sessionToken, $username, $password, $isTrusted);

            case 'getWidget':
                return $this->getWidget($number);

            case 'register':
                return $this->register($sessionToken, $number, $preferredMethod);

            case 'updateNumber':
                $this->userDataAccess->updateNumber($this->twizoData, $sessionToken, $number);
                break;

            case 'updateBackupCodes':
                return $this->userDataAccess->updateBackupCodes($this->twizoData, $sessionToken, uniqid());

            case 'updatePreferredMethod':
                $this->userDataAccess->updatePreferredMethod($this->twizoData, $preferredMethod);
                break;

            case 'getAmountOfBackupCodes':
                return $this->getAmountOfBackupCodes();

            default:
                throw new Exception('Function name not specified');
        }
    }

    /**
     * @return array
     * @since 0.1.0
     */
    private function getVars()
    {
        $input           = Factory::getApplication()->input;
        $functionName    = $input->getString('functionName');
        $username        = $input->getString('username');
        $password        = $input->getString('password');
        $sessionToken    = $input->getString('sessionToken');
        $number          = $input->getString('number');
        $preferredMethod = $input->getString('preferredMethod');
        $isTrusted       = json_decode($input->get("isTrusted"));

        return array($functionName, $username, $password, $sessionToken, $number, $preferredMethod, $isTrusted);
    }

    /**
     * @param $username
     * @param $number
     */
    private function setTwizoData($username, $number)
    {
        try
        {
            $user = UserHelper::getProfile();
            if ($user->id > 0)
                $this->twizoData = $this->twizoDataDAO->getByCurrentUser($user);
            elseif (isset($username))
                $this->twizoData = $this->twizoDataDAO->getByUsername($username);
            elseif (isset($number))
                $this->twizoData = $this->twizoDataDAO->getByNumber($number);
            else
                $this->twizoData = new TwizoData();
        }
        catch (TwizoDataException $ignored)
        {
            $this->twizoData = new TwizoData();
        }
    }

    /**
     * @param $username
     * @param $password
     *
     * @return array
     * @throws Exception
     * @internal param $twizoData
     * @since    0.1.0
     */
    private function checkCredentials($username, $password)
    {
        // Verify password
        $user               = Factory::getUser($this->userDataAccess->getUserId($username));
        $checkedCredentials = UserHelper::verifyPassword($password, $user->password, $user->id);

        // Get cookie data
        $inputCookie = Factory::getApplication()->input->cookie;
        $cookieValue = $inputCookie->get('twizoData', null);

//        return $user->username . " - " . $user->password_clear . " - " . $user->password . " - " . $user->id . " - " . $checkedCredentials;

        return $this->userLoginHelper->checkCredentials($this->twizoData, $username, $checkedCredentials, $cookieValue);
    }

    /**
     * @param $sessionToken
     * @param $username
     * @param $password
     * @param $isTrusted
     *
     * @return string
     * @throws Exception
     * @internal param TwizoData $twizoData
     * @since    0.1.0
     */
    private function login($sessionToken, $username, $password, $isTrusted)
    {
        // Verify password
        $user               = Factory::getUser($this->userDataAccess->getUserId($username));
        $checkedCredentials = UserHelper::verifyPassword($password, $user->password, $user->id);

        // Get cookie object
        $inputCookie = Factory::getApplication()->input->cookie;

        return $this->userLoginHelper->login($this->twizoData, $checkedCredentials, $sessionToken, $isTrusted, $inputCookie);
    }

    /**
     * Registers user in database if the sessionToken is correct.
     * Returns the backup codes for the user
     *
     * @param $sessionToken
     * @param $number
     * @param $preferredType
     *
     * @return array
     * @throws Exception
     * @internal param TwizoData $twizoData
     * @since    0.1.0
     */
    private function register($sessionToken, $number, $preferredType)
    {
        // Get current user
        $user = Factory::getUser();

        return $this->userLoginHelper->register($this->twizoData, $user, $sessionToken, $number, $preferredType, uniqid());
    }

    /**
     * @param $sessionToken
     *
     * @return bool|string
     * @throws Exception
     * @internal param TwizoData $twizoData
     * @since    0.1.0
     */
    private function validateWidget($sessionToken)
    {
        return $this->twizoHelper->isWidgetSuccess($sessionToken, $this->twizoData);
    }

    /**
     * @param $number
     *
     * @return array
     * @since    0.1.0
     */
    private function getWidget($number = null)
    {
        // Check if number is changed from the saved number and it's not empty
        if ($number != $this->twizoData->getNumber() && !empty($number))
            $this->twizoData->setNumber($number);

        // Override allowed types with only sms and call.
        $allowedTypes = array("sms", "call");

        return $this->twizoHelper->getWidgetSession($this->twizoData, $allowedTypes);
    }

    /**
     * @return string
     * @internal param TwizoData $twizoData
     * @since    0.1.0
     */
    private function getAmountOfBackupCodes()
    {
        return $this->twizoHelper->getAmountOfBackupCodes($this->twizoData->getIdentifier());
    }
}