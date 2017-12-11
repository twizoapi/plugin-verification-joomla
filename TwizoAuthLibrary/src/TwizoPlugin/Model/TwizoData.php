<?php

namespace TwizoPlugin\Model;

defined('_JEXEC') || die;

/**
 * @package     TwizoPlugin\Model
 *
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TwizoData implements \JsonSerializable
{
    /**
     * @var int|null
     * @since 0.1.0
     */
    private $id;
    /**
     * @var null|string
     * @since 0.1.0
     */
    private $number;
    /**
     * @var null|string
     * @since 0.1.0
     */
    private $identifier;
    /**
     * @var null|string
     * @since 0.1.0
     */
    private $preferredType;
    /**
     * @var array|null
     * @since 0.1.0
     */
    private $trustedDevices;

    /**
     * TwizoData constructor.
     *
     * @param integer $id
     * @param string  $number
     * @param string  $identifier
     * @param string  $preferredType
     * @param array   $trustedDevices
     *
     * @since    0.1.0
     */
    public function __construct($id = null, $number = null, $identifier = null, $preferredType = null, $trustedDevices = [])
    {
        $this->id             = $id;
        $this->number         = $number;
        $this->identifier     = $identifier;
        $this->preferredType  = $preferredType;
        $this->trustedDevices = $trustedDevices;
    }


    /**
     * Return data which should be serialized by json_encode().
     *
     * @return  mixed
     *
     * @since   0.1.0
     */
    public function jsonSerialize()
    {
        $getObjectVars = get_object_vars($this);

        //Removes Id from the object when returning as JSON
        unset($getObjectVars['id']);

        return $getObjectVars;
    }

    /**
     *
     * @return array
     *
     * @since 0.1.0
     */
    public function toDatabase()
    {
        $getObjectVars = get_object_vars($this);

        //Removes Id from the object when returning as JSON for the database.
        unset($getObjectVars['trustedDevices']);
        unset($getObjectVars['id']);

        return $getObjectVars;
    }

    /**
     * @return mixed
     *
     * @since 0.1.0
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @since 0.1.0
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     *
     * @since 0.1.0
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     *
     * @since 0.1.0
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     *
     * @since 0.1.0
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     *
     * @since 0.1.0
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     *
     * @since 0.1.0
     */
    public function getPreferredType()
    {
        return $this->preferredType == "null" ? null : $this->preferredType;
    }

    /**
     * @param string $preferredType
     *
     * @since 0.1.0
     */
    public function setPreferredType($preferredType)
    {
        $this->preferredType = $preferredType;
    }

    /**
     * @return array
     *
     * @since 0.1.0
     */
    public function getTrustedDevices()
    {
        return $this->trustedDevices;
    }

    /**
     * @param array $trustedDevices
     *
     * @since 0.1.0
     */
    public function setTrustedDevices($trustedDevices)
    {
        $this->trustedDevices = $trustedDevices;
    }
}