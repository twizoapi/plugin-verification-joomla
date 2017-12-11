<?php

namespace TwizoPlugin\Exceptions;

defined('_JEXEC') || die;

/**
 * @package     TwizoPlugin\Exceptions
 *
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class TwizoDataException extends \Exception
{

    /**
     * TwizoDataException constructor.
     *
     * @since 0.1.0
     *
     * @param string $message
     */
    public function __construct($message = null)
    {
        parent::__construct($message ? $message : "Cannot get Twizo data object. Not found in the database.");
    }
}