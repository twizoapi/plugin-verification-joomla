<?php

namespace TwizoPlugin\DataSource\DataAccessObjects;

defined('_JEXEC') || die;

use TwizoPlugin\Exceptions\TwizoDataException;
use TwizoPlugin\Helpers\TrustedDeviceHelper;
use TwizoPlugin\Model\TwizoData;

/**
 * @package     TwizoPlugin\Datasource\DataAccessObjects
 *
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TwizoDataDAO
{
    const SELECT_COLUMNS = 'id, otpKey';
    const USERS_TABLE = '#__users';
    const OTP_KEY_METHOD_NAME = 'TwizoAuth';
    /**
     * @var TrustedDeviceHelper
     * @since 0.1.0
     */
    private $trustedDeviceHelper;
    /**
     * @var TrustedDeviceDAO
     * @since 0.1.0
     */
    private $trustedDeviceDAO;
    /**
     * @var \JDatabaseDriver
     * @since 0.1.0
     */
    private $db;

    /**
     * TwizoDataDAO constructor.
     *
     * @param TrustedDeviceHelper $trustedDeviceHelper
     *
     * @param TrustedDeviceDAO    $trustedDeviceDAO
     *
     * @param \JDatabaseDriver    $db
     *
     * @since 0.1.0
     */
    public function __construct($trustedDeviceHelper, $trustedDeviceDAO, $db)
    {
        $this->trustedDeviceHelper = $trustedDeviceHelper;
        $this->trustedDeviceDAO    = $trustedDeviceDAO;
        $this->db                  = $db;
    }

    /**
     * @param $id
     *
     * @return bool|TwizoData
     * @throws TwizoDataException
     * @since 0.1.0
     */
    public function getById($id)
    {
        $query = $this->db->getQuery(true)
            ->select(self::SELECT_COLUMNS)
            ->from(self::USERS_TABLE)
            ->where('id =' . $this->db->quote($id));

        $this->db->setQuery($query);

        $result = $this->db->loadObject();

        return $this->createTwizoDataObject($result);
    }

    /**
     * @param $number
     *
     * @return bool|TwizoData
     *
     * @throws TwizoDataException
     * @since 0.1.0
     */
    public function getByNumber($number)
    {
        $query = $this->db->getQuery(true)
            ->select(self::SELECT_COLUMNS)
            ->from(self::USERS_TABLE)
            ->where('otpKey like ' . $this->db->quote('%"number":"' . $number . '"%'));

        $this->db->setQuery($query);
        $result = $this->db->loadObject();

        return $this->createTwizoDataObject($result);
    }

    /**
     * @param string $username
     *
     * @return bool|TwizoData
     *
     * @throws TwizoDataException
     * @since 0.1.0
     */
    public function getByUsername($username)
    {
        $query = $this->db->getQuery(true)
            ->select(self::SELECT_COLUMNS)
            ->from(self::USERS_TABLE)
            ->where('username= ' . $this->db->quote($username));

        $this->db->setQuery($query);
        $result = $this->db->loadObject();

        return $this->createTwizoDataObject($result);
    }

    /**
     * @internal User $user
     * @return bool|TwizoData
     *
     * @throws TwizoDataException
     * @since    0.1.0
     */
    public function getByCurrentUser($user)
    {
        $query = $this->db->getQuery(true)
            ->select(self::SELECT_COLUMNS)
            ->from(self::USERS_TABLE)
            ->where('id= ' . $this->db->quote($user->id));

        $this->db->setQuery($query);
        $result = $this->db->loadObject();

        return $this->createTwizoDataObject($result);
    }

    /**
     * @param $result
     *
     * @return bool|TwizoData
     *
     * @throws TwizoDataException
     * @since 0.1.0
     */
    private function createTwizoDataObject($result)
    {
        if (!empty($result->{'otpKey'}))
        {
            $data = $this->getJsonFromResult($result->{'otpKey'});

            // Check if method type is TwizoForJoomla && if data is returned
            if (!empty($data))
            {
                $id             = $result->{'id'};
                $trustedDevices = $this->trustedDeviceDAO->getById($id);

                return new TwizoData($id, $data['number'], $data['identifier'], $data['preferredType'], $trustedDevices === null ? [] : $trustedDevices);
            }
        }
        throw new TwizoDataException();
    }

    /**
     * @param $result
     *
     * @return array
     *
     * @since 0.1.0
     */
    private function getJsonFromResult($result)
    {
        //Format db result to proper JSON format
        $json = json_decode(preg_replace('/("(.*?)"|(\w+))(\s*:\s*(".*?"|.))/s', '"$2$3"$4', "{" . $result . "}"), true);

        return !empty($json[self::OTP_KEY_METHOD_NAME]) && isset($json[self::OTP_KEY_METHOD_NAME]) ? $json[self::OTP_KEY_METHOD_NAME] : null;
    }

    /**
     * @param string $username
     *
     * @since 0.1.0
     */
    public function removeOldTrustedDevices($username)
    {
        $twizoData      = $this->getByUsername($username);
        $trustedDevices = $this->trustedDeviceHelper->removeOldDevices($twizoData->getTrustedDevices());
        $twizoData->setTrustedDevices($trustedDevices);
        $this->updateTwizoData($twizoData);
    }

    /**
     * @param TwizoData $twizoData
     *
     * @return mixed
     * @throws TwizoDataException
     * @since 0.1.0
     */
    public function updateTwizoData($twizoData)
    {
        if (is_null($twizoData->getId()))
            throw new TwizoDataException("TwizoData object empty.");

        $this->db->transactionStart();
        try
        {
            $query = $this->db->getQuery(true)
                ->update(self::USERS_TABLE)
                ->set('otpKey =' . $this->db->quote(self::OTP_KEY_METHOD_NAME.':' . json_encode($twizoData->toDatabase())))
                ->where('id = ' . $twizoData->getId());

            $this->db->setQuery($query);

            $result = $this->db->execute();
            $this->trustedDeviceDAO->updateDevices($twizoData->getId(), $twizoData->getTrustedDevices());
            $this->db->transactionCommit();

            return $result;
        }
        catch (\mysqli_sql_exception $sqlException)
        {
            $this->db->transactionRollback();
            throw $sqlException;
        }
    }
}