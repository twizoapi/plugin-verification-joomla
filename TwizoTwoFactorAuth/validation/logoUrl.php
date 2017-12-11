<?php
defined('_JEXEC') || die;

/**
 * @package     TwizoTwoFactorAuth
 *
 * @author      Yarince <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

class JFormRuleLogoUrl extends FormRule
{
    public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        if (substr($value, 0, 7) === "https://")
        {
            $element->addAttribute('error', 'The current logo url is not in Https.');

            return false;
        }

        return true;
    }
}