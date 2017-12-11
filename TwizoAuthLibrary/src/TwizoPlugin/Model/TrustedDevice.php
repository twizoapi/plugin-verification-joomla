<?php

namespace TwizoPlugin\Model;

defined('_JEXEC') || die;

/**
 * Trusted device model from database converted to json like object.
 *
 * @property    integer userId
 * @package     TwizoPlugin\Model
 *
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TrustedDevice implements \JsonSerializable
{
    /**
     * @var int
     * @since 0.1.0
     */
    public $id;
    /**
     * @var string
     * @since 0.1.0
     */
    public $hash;
    /**
     * @var \DateTime
     * @since 0.1.0
     */
    public $date;

    /**
     * trustedDevice constructor.
     *
     * @param $id
     * @param $hash
     * @param $date
     *
     * @since    0.1.0
     */
    public function __construct($id, $hash, $date)
    {
        $this->id   = $id;
        $this->hash = $hash;
        $this->date = $date;
    }

    /**
     * @return array
     *
     * @since 0.1.0
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return integer
     * @since 0.1.0
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @since 0.1.0
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     * @since 0.1.0
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @since 0.1.0
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     *
     * @since 0.1.0
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param mixed $hash
     *
     * @since 0.1.0
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }
}