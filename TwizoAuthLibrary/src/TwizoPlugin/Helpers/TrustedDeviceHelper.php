<?php

namespace TwizoPlugin\Helpers;

defined('_JEXEC') || die;

use TwizoPlugin\DataSource\DataAccessObjects\TrustedDeviceDAO;
use TwizoPlugin\Model\TrustedDevice;

/**
 * @package     TwizoPlugin\Helpers
 *
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TrustedDeviceHelper
{
    /**
     * @var TrustedDeviceDAO
     * @since 0.1.0
     */
    private $trustedDeviceDAO;

    /**
     * TrustedDeviceHelper constructor.
     *
     * @param TrustedDeviceDAO $trustedDeviceDAO
     *
     * @since 0.1.0
     */
    public function __construct($trustedDeviceDAO)
    {
        $this->trustedDeviceDAO = $trustedDeviceDAO;
    }

    /**
     * Check if set date of device is still within 30 days of today.
     *
     * @param array $trustedDevices
     *
     * @return array
     *
     * @since 0.1.0
     */
    public function removeOldDevices($trustedDevices)
    {
        $passedDevices = array();

        /** @var TrustedDevice $trustedDevice */
        foreach ($trustedDevices as $key => $trustedDevice)
        {
            // Check if date of trustedDevice is still younger than 30 days.
            if (strtotime($trustedDevice->getDate()) > strtotime("-30 days"))
            {
                // If it is add the trusted device to the passedDevices array.
                array_push($passedDevices, $trustedDevice);
            }
        }

        return $passedDevices;
    }

    /**
     * @param $cookie
     * @param $username
     * @param $recipient
     *
     * @return bool
     * @since 0.1.0
     */
    public function checkCookie($cookie, $username, $recipient)
    {
        $hashedVersion = hash('sha512', sprintf('%s_%s', $recipient, $cookie));

        return $this->trustedDeviceDAO->checkHash($username, $hashedVersion);
    }
}