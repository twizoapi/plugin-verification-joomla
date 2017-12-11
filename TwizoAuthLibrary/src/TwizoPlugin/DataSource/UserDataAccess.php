<?php

namespace TwizoPlugin\DataSource;

use Twizo\Api\Exception;
use TwizoPlugin\DataSource\DataAccessObjects\TwizoDataDAO;
use TwizoPlugin\Helpers\TwizoHelper;
use TwizoPlugin\Model\TwizoData;

defined('_JEXEC') || die;

/**
 * @package     TwizoPlugin\Datasource
 *
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class UserDataAccess
{
    const WIDGET_SUCCESS = 'success';
    /**
     * @var \JDatabaseDriver
     * @since 0.1.0
     */
    private $db;
    private $twizoHelper;
    private $twizoDataDAO;

    /**
     * UserDataAccess constructor.
     *
     * @param \JDatabaseDriver $db
     * @param TwizoHelper      $twizoHelper
     * @param TwizoDataDAO     $twizoDataDAO
     */
    public function __construct($db, $twizoHelper, $twizoDataDAO)
    {
        $this->db           = $db;
        $this->twizoHelper  = $twizoHelper;
        $this->twizoDataDAO = $twizoDataDAO;
    }

    /**
     * @param $username
     *
     * @return integer | boolean
     *
     * @since 0.1.0
     */
    public function getUserId($username)
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__users'))
            ->where($this->db->quoteName('username') . ' = ' . $this->db->quote($username));
        $this->db->setQuery($query, 0, 1);

        return $this->db->loadResult();
    }

    /**
     * Update the number in the database
     *
     * @param TwizoData $twizoData
     * @param string    $sessionToken
     * @param string    $number
     *
     * @throws Exception
     */
    public function updateNumber($twizoData, $sessionToken, $number)
    {
        $twizoData->setNumber($number);

        if (!isset($number))
            throw new Exception('Number is not set.');
        if (!$this->twizoHelper->isWidgetSuccess($sessionToken, $twizoData))
            throw new Exception('Twizo widget is not validated.');


        // Update the edited twizo data object
        $this->twizoDataDAO->updateTwizoData($twizoData);
    }

    /**
     * Updates and returns the new backup codes
     *
     * @param TwizoData $twizoData
     * @param string    $sessionToken
     * @param string    $identifier
     *
     * @return array
     * @throws Exception
     * @since    0.1.0
     */
    public function updateBackupCodes($twizoData, $sessionToken, $identifier = null)
    {
        if (!$this->twizoHelper->isWidgetSuccess($sessionToken, $twizoData))
            throw new Exception('Twizo widget is not validated.');

        if (is_null($twizoData->getIdentifier()) && isset($identifier))
        {
            // The user does not have a identifier yet. So we have to generate a new one.
            $twizoData->setIdentifier($identifier);
            $this->twizoDataDAO->updateTwizoData($twizoData);

            return $this->twizoHelper->generateBackupCodes($identifier);
        }

        return $this->twizoHelper->updateBackupCodes($twizoData);
    }

    /**
     * Update the preferredMethod in the database
     *
     * @param TwizoData $twizoData
     * @param string    $preferredMethod
     *
     * @throws Exception
     */
    public function updatePreferredMethod($twizoData, $preferredMethod)
    {
        if (!isset($preferredMethod))
            throw new Exception('Preferred method is not set.');

        $twizoData->setPreferredType($preferredMethod);

        // Update the edited twizo data object
        $this->twizoDataDAO->updateTwizoData($twizoData);
    }
}