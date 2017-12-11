<?php

namespace TwizoPlugin\Helpers;

defined('_JEXEC') || die;

use Twizo\Api\Entity\Exception;
use Twizo\Api\TwizoInterface;

/**
 * @package     TwizoPlugin\Helpers
 *
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TwizoSettingsHelper
{
    private $twizo;
    const HTTPS_CDN_TWIZO_COM_INFORMATION_JSON = 'https://cdn.twizo.com/information.json';
    private $twizoSettings;


    /**
     * TwizoSettingsHelper constructor.
     *
     * @param TwizoInterface $twizo
     *
     * @param array          $twizoSettings
     *
     * @since 0.1.0
     */
    public function __construct($twizo, $twizoSettings)
    {
        $this->twizo         = $twizo;
        $this->twizoSettings = $twizoSettings;
    }

    /**
     * Returns array of api hosts.
     * Dynamic from online Json file
     *
     * @return array
     * @since 0.1.0
     */
    public static function getApiHosts()
    {
        $json = json_decode(file_get_contents(self::HTTPS_CDN_TWIZO_COM_INFORMATION_JSON), true);

        $hosts = array();
        foreach ($json["hosts"] as $key => $host)
        {
            $hosts[$key] = $host;
        }

        return $hosts;
    }

    /**
     * Returns array of enabled verification Types
     *
     * @return array
     * @since 0.1.0
     */
    public function getVerificationTypes()
    {
        try
        {
            $verificationTypes = $this->twizo->getVerificationTypes()->getVerificationTypes();
        }
        catch (Exception $ignored)
        {
        }

        return empty($verificationTypes) ? array() : $verificationTypes;
    }

    /**
     * Returns the enabled verification types allowed to be set as preferredType.
     * @return array
     * @since 0.1.0
     */
    public function getPreferredTypes()
    {
        $verificationTypes = $this->getVerificationTypes();
        // Remove backup code from enabled validations array
        // Search for the index and unset the item
        $index = array_search('backupcode', $verificationTypes);
        if ($index)
            unset($verificationTypes[$index]);

        return $verificationTypes;
    }

    /**
     * Returns true if backup codes are enabled
     *
     * @return bool
     * @since 0.1.0
     */
    public function backupCodeIsEnabled()
    {
        return in_array("backupcode", $this->getVerificationTypes());
    }

    /**
     * Returns default validation type
     *
     * @return string | null
     * @since 0.1.0
     */
    public function getDefaultValidation()
    {
        return $this->twizoSettings["default_validation"];
    }

    /**
     * Returns default validation type
     *
     * @return string | null
     * @since 0.1.0
     */
    public function getSender()
    {
        return $this->twizoSettings["sender"];
    }

    /**
     * @return string | null
     * @since 0.1.0
     */
    public function getWidgetLogo()
    {
        return $this->twizoSettings["widget_logo"];
    }
}