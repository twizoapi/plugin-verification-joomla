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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Twizo\Api\Twizo;
use TwizoPlugin\Helpers\TwizoHelper;
use TwizoPlugin\Helpers\TwizoSettingsHelper;

class JFormRuleApiKey extends FormRule
{
    public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        // Get the first api host from the online config.
        $apiHost = current(TwizoSettingsHelper::getApiHosts())["host"];

        $twizo               = Twizo::getInstance($value, $apiHost);
        $plugin              = PluginHelper::getPlugin('twofactorauth', 'TwizoTwoFactorAuth');
        $twizoSettings       = (new Registry($plugin->params))->toArray();
        $twizoSettingsHelper = new TwizoSettingsHelper($twizo, $twizoSettings);
        $twizoHelper         = new TwizoHelper($twizo, $twizoSettingsHelper);

        switch ($twizoHelper->getKeyType())
        {
            case 0:

                break;
            case 1:
                $element->addAttribute('message', 'The current balance is.' . $twizoHelper->getCreditBalance());

                return true;
            case 2:
                $element->addAttribute('message', 'The current api key is a test key for application: .' . $twizoHelper->getApplicationTag());

                return true;

            default:
                break;
        }

        $element->addAttribute('error', 'The api key is not correct.');

        $value = null;
        $form->setValue($element->getName(), null, null);

        return false;
    }
}