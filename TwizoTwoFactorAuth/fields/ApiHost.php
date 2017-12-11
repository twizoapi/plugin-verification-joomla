<?php


use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') || die;

FormHelper::loadFieldClass('list');

/**
 * @package     TwizoTwoFactorAuth
 *
 * @author      Yarince <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class JFormFieldApiHost extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    protected $type = 'ApiHost';

    protected function getOptions()
    {
        $options = array();

        //OC5q7sbENSVcy20-MGlyuuxqwYNidpz-LmpL-Z0PYy9VEmVl
        $apiKeys = \TwizoPlugin\Helpers\TwizoSettingsHelper::getApiHosts();
        foreach ($apiKeys as $apiKey)
        {
            $options[] = JHtmlSelect::option($apiKey["host"], $apiKey["location"] . " - " . key($apiKeys));
            next($apiKeys);
        }

        return $options;
    }
}