<?php

namespace TwizoPlugin\DataSource\DataAccessObjects;

defined('_JEXEC') || die;

use TwizoPlugin\Model\TrustedDevice;

/**
 * @package     TwizoPlugin\DataSource\DataAccessObjects
 *
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TrustedDeviceDAO
{
    const TWIZODATA_USERS_TABLE = '#__twizodata_users';
    /**
     * @var \JDatabaseDriver
     * @since 0.1.0
     */
    private $db;

    /**
     * TrustedDeviceDAO constructor.
     *
     * @param \JDatabaseDriver $db
     *
     * @since 0.1.0
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @param $id
     *
     * @return array
     * @since 0.1.0
     */
    public function getById($id)
    {

        $query = $this->db->getQuery(true)
            ->select('id, hash, date')
            ->from(self::TWIZODATA_USERS_TABLE)
            ->where('userId =' . $this->db->quote($id));

        $this->db->setQuery($query);

        $result = $this->db->loadObjectList();

        $parsed = array();
        foreach ($result as $item)
        {
            array_push($parsed,
                new TrustedDevice(
                    $item->{'id'},
                    $item->{'hash'},
                    $item->{'date'}
                )
            );
        }

        return $parsed;
    }

    /**
     * @param $username
     *
     * @return array
     * @since 0.1.0
     */
    public function getByUsername($username)
    {
        $query = $this->db->getQuery(true)
            ->select('id, hash, date')
            ->from(self::TWIZODATA_USERS_TABLE)
            ->where('userId in (select id from #__users where username = ' . $this->db->quote($username) . ')');

        $this->db->setQuery($query);

        $result = $this->db->loadObjectList();
        $parsed = array();
        foreach ($result as $item)
        {
            array_push($parsed,
                new TrustedDevice(
                    $item->{'id'},
                    $item->{'hash'},
                    $item->{'date'}
                )
            );
        }

        return $parsed;
    }

    /**
     * @param  int   $id
     * @param  array $trustedDevices
     *
     * @since 0.1.0
     */
    public function updateDevices($id, $trustedDevices)
    {

        $toInsert    = array();
        $existingIds = array();

        if (empty($trustedDevices))
            return;

        /** @var TrustedDevice $trustedDevice */
        foreach ($trustedDevices as $trustedDevice)
        {
            if (!empty($trustedDevice))
            {
                if (is_null($trustedDevice->getId()))
                    array_push($toInsert, $trustedDevice);
                else
                    array_push($existingIds, $trustedDevice->getId());
            }
        }

        $this->delete($id, $existingIds);
        $this->insert($id, $toInsert);
    }

    /**
     * @param int   $id
     * @param array $existingIds The Id's not to delete from the database
     *
     * @since 0.1.0
     */
    private function delete($id, $existingIds)
    {
        if (empty($existingIds))
            return;

        $deleteQuery = $this->db->getQuery(true)
            ->delete(self::TWIZODATA_USERS_TABLE)
            ->where('userId = ' . $id . ' AND id NOT IN (\'' . implode('\',\'', $existingIds) . '\')');

        $this->db->setQuery($deleteQuery);
        $this->db->execute();
    }

    /**
     * @param $id
     * @param $toInsert
     *
     * @since 0.1.0
     */
    private function insert($id, $toInsert)
    {
        if (empty($toInsert))
            return;
        /** @var TrustedDevice $item */
        foreach ($toInsert as $item)
        {
            $item->userId = $id;
            $this->db->insertObject(self::TWIZODATA_USERS_TABLE, $item);
        }
    }

    /**
     * Returns true if hash exists in db for user
     *
     * @param $username
     * @param $hashedVersion
     *
     * @return boolean
     * @since 0.1.0
     */
    public function checkHash($username, $hashedVersion)
    {
        $query = $this->db->getQuery(true)
            ->select('id, hash, date')
            ->from(self::TWIZODATA_USERS_TABLE)
            ->where('userId in (select id from #__users where username = ' . $this->db->quote($username) . ') AND hash = ' . $this->db->quote($hashedVersion));

        $this->db->setQuery($query);

        return !empty($this->db->loadObject());
    }
}
