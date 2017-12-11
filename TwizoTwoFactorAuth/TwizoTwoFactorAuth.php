<?php
defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserHelper;
use Twizo\Api\Exception;
use Twizo\Api\Twizo;
use TwizoPlugin\DataSource\DataAccessObjects\TrustedDeviceDAO;
use TwizoPlugin\DataSource\DataAccessObjects\TwizoDataDAO;
use TwizoPlugin\Exceptions\TwizoDataException;
use TwizoPlugin\Helpers\TrustedDeviceHelper;
use TwizoPlugin\Helpers\TwizoHelper;
use TwizoPlugin\Helpers\TwizoSettingsHelper;
use TwizoPlugin\Model\TwizoData;

/**
 * @package     TwizoTwoFactorAuth
 *
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class plgTwofactorauthTwizoTwoFactorAuth extends CMSPlugin
{
    /**
     * Method name
     *
     * @var    string
     * @since  0.1.0
     */
    protected $methodName = 'TwizoAuth';
    private $twizoHelper;
    private $twizoSettingsHelper;
    private $trustedDeviceDAO;
    private $trustedDeviceHelper;
    private $twizoDataDAO;

    /**
     * @param       $subject
     * @param array $config
     *
     * @since 0.1.0
     */
    public function __construct($subject, array $config = array())
    {
        parent::__construct($subject, $config);

        $params  = $this->params->toArray();
        $apiKey  = isset($params['api_key']) ? $params['api_key'] : null;
        $apiHost = isset($params['api_host']) ? $params['api_host'] : null;
        $twizo   = Twizo::getInstance($apiKey, $apiHost);
        $db      = Factory::getDbo();

        $this->twizoSettingsHelper = new TwizoSettingsHelper($twizo, $params);
        $this->twizoHelper         = new TwizoHelper($twizo, $this->twizoSettingsHelper);
        $this->trustedDeviceDAO    = new TrustedDeviceDAO($db);
        $this->trustedDeviceHelper = new TrustedDeviceHelper($this->trustedDeviceDAO);
        $this->twizoDataDAO        = new TwizoDataDAO($this->trustedDeviceHelper, $this->trustedDeviceDAO, $db);
    }


    /**
     * This method returns the identification object for this two factor
     * authentication plugin.
     *
     * @return  stdClass | bool  An object with public properties method and title
     *
     * @since   0.1.0
     */
    public function onUserTwofactorIdentify()
    {
        $app = Factory::getApplication();

        try
        {
            //Check what is the current status of the api key. If it is not a live key give a message.
            if ($this->twizoHelper->getKeyType() == 2 && $app->isClient('administrator'))
                $app->enqueueMessage('The current Twizo API-key is only for test purposes! <br> API-key for "' . $this->twizoHelper->getApplicationTag() . '".', 'warning');
        }
        catch (\Twizo\Api\Entity\Exception $exception)
        {
            // Show the error message if site is on admin page
            if ($app->isClient('administrator'))
                $app->enqueueMessage('Twizo two factor authentication plugin is not configured correctly! <br>'
                    . Text::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR') . '<br> <br> '
                    . $exception->getMessage(), 'error');

            return false;
        }

        // Return mandatory Joomla settings
        return (object) array(
            'method' => $this->methodName,
            'title'  => 'Twizo Verification',
        );
    }

    /**
     * Shows the configuration page for this two factor authentication method.
     *
     * @param   object  $otpConfig The two factor auth configuration object
     * @param   integer $user_id   The numeric user ID of the user whose form we'll display
     *
     * @return  boolean|array  False if the method is not ours, the HTML of the configuration page otherwise
     *
     * @see     UsersModelUser::getOtpConfig
     * @since   0.1.0
     */
    public function onUserTwofactorShowConfiguration($otpConfig, $user_id = null)
    {
        $isClientSite = Factory::getApplication()->isClient('site');
        if ($isClientSite)
        {
            // Is this a new TOTP setup? If so, we'll have to show the code validation field.
            $newInstance = $otpConfig->method !== $this->methodName;

            // Info needed to display the config form
            $enabledValidations = json_encode($this->twizoSettingsHelper->getPreferredTypes());
            $backupCodesEnabled = $this->twizoSettingsHelper->backupCodeIsEnabled();

            //Set the variables for /tmpl/form.php to use.
            list($currentNumber, $currentPreferredMethod, $amountOfBackupCodes) = $this->setConfigVariables($newInstance, $backupCodesEnabled);
        }

        // Start output buffering
        @ob_start();

        include_once __DIR__ . '/tmpl/form.php';

        // Stop output buffering and get the form contents
        $html = @ob_get_clean();

        // Return the form contents
        return array(
            'method' => $this->methodName,
            'form'   => $html,
        );

    }

    /**
     * Ignored. Returns true but the database save action is done elsewhere.
     *
     * @param   string $method The two factor auth method for which we'll show the config page
     *
     * @return  boolean|stdClass  False if the method doesn't match or we have an error, OTP config object if it succeeds
     *
     * @see     plgAjaxTwizoAjaxHandler::register()
     * @since   0.1.0
     */
    public function onUserTwofactorApplyConfiguration($method)
    {
        if ($method !== $this->methodName)
        {
            return false;
        }

        return true;
    }


    /**
     * This method should handle any two factor authentication and report back
     * to the subject.
     *
     * @param   array $credentials Array holding the user credentials
     * @param   array $options     Array of extra options
     *
     * @return  boolean  True if the user is authorised with this two-factor authentication method
     *
     * @since   3.2
     */
    public function onUserTwofactorAuthenticate($credentials, $options)
    {
        //Get user plugin settings.
        $otpConfig    = $options['otp_config'];
        $sessionToken = $credentials['secretkey'];

        $twizoData = new TwizoData(null, $otpConfig->config['number']);

        // Check for old trusted devices in the db and remove them
        try
        {
            $this->twizoDataDAO->removeOldTrustedDevices($credentials['username']);
        }
        catch (TwizoDataException $exception){
            Log::add($exception, Log::ERROR, 'TwizoPlugin\Exceptions');
        }

        if (empty($sessionToken) || empty($twizoData->getNumber()))
            return false;

        // Check if we have the correct method
        if ($otpConfig->method !== $this->methodName)
            return false;

        // Get cookie data
        $inputCookie = Factory::getApplication()->input->cookie;
        $value       = $inputCookie->get('twizoData', null);

        // Check if user is trying to login through a trustedDevice
        // If cookie has the correct data. Set login successful
        if ($value != null && $sessionToken == 'trustedDevice')
            return $this->trustedDeviceHelper->checkCookie($value, $credentials['username'], $twizoData->getNumber());

        try
        {
            // If widget is success set result to true. (Login successful)
            return $this->twizoHelper->isWidgetSuccess($sessionToken, $twizoData);
        }
        catch (Exception $exception)
        {
            // If a twizo exception occurs the login attempt must be set to unsuccessful (false)
            Log::add($exception, Log::ERROR, 'Twizo\Api\Exception');

            return false;
        }
    }

    /**
     * @param $newInstance
     * @param $backupCodesEnabled
     *
     * @return array
     * @since 0.1.0
     */
    private function setConfigVariables($newInstance, $backupCodesEnabled)
    {
        $params = $this->params->toArray();

        $amountOfBackupCodes    = null;
        $currentNumber          = null;
        $currentPreferredMethod = null;

        // Check if this is a setup attempt or a already setup user.
        if ($newInstance)
            return array($currentNumber, $currentPreferredMethod, $amountOfBackupCodes);
        try
        {
            $twizoData     = $this->twizoDataDAO->getByCurrentUser(UserHelper::getProfile());
            $currentNumber = $twizoData->getNumber();
            try
            {
                // Set currentPreferredMethod to be shown on the form.php page.
                if (!empty($twizoData->getPreferredType()))
                    $currentPreferredMethod = $twizoData->getPreferredType();
                else
                    $currentPreferredMethod = $params['default_validation'];

                // Set amountOfBackupCodes to be shown on the form.php page.
                if ($backupCodesEnabled)
                    $amountOfBackupCodes = $this->twizoHelper->getAmountOfBackupCodes($twizoData->getIdentifier());
            }
            catch (Exception $exception)
            {
                // If getAmountOfBackupCodes is unsuccessful set amountOfBackupCodes to null and log the exception
                Log::add($exception, Log::ERROR, 'Twizo\Api\Exception');
            }
        }
        catch (TwizoDataException $exception)
        {
            Log::add($exception, Log::ERROR, 'TwizoPlugin\Exceptions');
        }

        return array($currentNumber, $currentPreferredMethod, $amountOfBackupCodes);
    }
}