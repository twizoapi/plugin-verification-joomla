<?php
defined('_JEXEC') || die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

jimport('joomla.plugin.plugin');
jimport('joomla.application.module.helper');

/**
 * @package     TwizoAuthSystemPlugin
 *
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
class plgSystemTwizoReadyUp extends CMSPlugin
{

    /**
     * Function called after the framework has loaded and the application initialise method has been called.
     *
     * @since 0.1.0
     */
    public function onAfterInitialise()
    {
        $application = Factory::getApplication();

        $twizoPluginPath = 'libraries/TwizoPlugin/src/TwizoPlugin';
        $twizoPath       = 'libraries/Twizo/src';

        if ($application->isClient('administrator'))
        {
            $twizoPluginPath = '../' . $twizoPluginPath;
            $twizoPath       = '../' . $twizoPath;
        }

        // !! psr4 is going to be default in Joomla! 4.0 !!
        // Register Twizo Plugin library namespace for whole application
        JLoader::registerNamespace('TwizoPlugin', $twizoPluginPath, false, false, 'psr4');

        // Register Twizo library namespace for whole application
        JLoader::registerNamespace('Twizo\\Api', $twizoPath, false, false, 'psr4');
    }

    /**
     * Function called at the compilation of HTML HEAD.
     * Adds js files to site
     * @since 0.1.0
     */
    public function onBeforeCompileHead()
    {
        // Declare language sentence for Javascript
        Text::script('COM_USERS_USER_NOT_FOUND');

        $application = Factory::getApplication();
        $widget      = 'https://widget.twizo.com/widget.js';

        $base = Uri::base();
        if ($application->isClient('administrator'))
            $base .= '../';

        $document  = Factory::getDocument();
        $srcFolder = $base . 'libraries/TwizoPlugin/src/TwizoPlugin/';

        $scripts = array(
            $widget,
            $srcFolder . 'javascript/functions.js',
            $srcFolder . 'javascript/ajaxTwizoFunc.js',
            $srcFolder . 'javascript/setup.js'
        );

        // Set the global var BASE_URL to post to. Needs php to get the value.
        $document->addScriptDeclaration('BASE_URL = "' . $base . '"');

        // Adds the declared scripts to the document.
        foreach ($scripts as $script)
        {
            $document->addScript($script);
        }
    }
}