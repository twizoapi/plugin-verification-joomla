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
class JFormFieldEnabledTypes extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    protected $type = 'EnabledTypes';

    protected function getOptions()
    {
        $options = array();


        $verificationTypesJSON = \TwizoPlugin\Helpers\TwizoSettingsHelper::getVerificationTypesJSON();
        foreach ($verificationTypesJSON as $verificationType)
        {
            $options[] = JHtmlSelect::option(key($verificationTypesJSON), $verificationType["translations"]["en"]);
            next($verificationTypesJSON);
        }

        return $options;
    }
}