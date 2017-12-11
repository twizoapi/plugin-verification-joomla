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

JHtmlBehavior::formvalidator();

/** @var boolean $isClientSite */
/** @var boolean $newInstance */
/** @var array $enabledValidations */
/** @var string $currentNumber */
/** @var string $currentPreferredMethod */
/** @var boolean $backupCodesEnabled */
/** @var integer $amountOfBackupCodes */

?>
<div>
    <p>
        Welcome on the Twizo configuration page. Setup and change your settings here.
    </p>
</div>

<script></script>
<?php

$backupCodeFieldSet =
    <<<HTML
<fieldset id="twizoBackupCodes" hidden>
    <legend>Backup codes for Twizo</legend>
    <p style="font-weight: bold;">
        These backup codes will only be shown once so save them in a safe place!.<br>
        Upon use of one code it wil immediately be destroyed.
    </p>
    <div class="alert alert-info">
        <div id="backupCodesTable"></div>
        <input class="btn btn-small" type="button" onclick="printDiv('backupCodesTable');" value="Print">
    </div>
</fieldset>
HTML;
if ($isClientSite):
    if ($newInstance): ?>
        <fieldset id="twizoSetup">
            <legend>
                Twizo set up
            </legend>
            <div class="control-group">
                <label class="control-label" for="twizoNumber">
                    Phone number
                </label>
                <div class="controls">
                    <input type="text" name="validationNumber"
                           class="input-medium "
                           id="twizoNumber" autocomplete="1">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="selectPreferredMethod" aria-invalid="false">
                    Choose preferred method
                </label>
                <div class="controls">
                    <select id="selectPreferredMethod" onclick='' title="select preferred 2fa method"
                            name="jform[twofactor][twizo][preferredMethod]">
                    </select>
                    <script>
                        var methods = [null].concat(<?=$enabledValidations?>);
                        var sel = document.getElementById("selectPreferredMethod");
                        for (var i = 0; i < methods.length; i++) {
                            var opt = document.createElement("option");
                            opt.innerHTML = methods[i];
                            opt.value = methods[i];
                            sel.appendChild(opt);
                        }
                    </script>
                </div>
            </div>
        </fieldset>
        <?php
        echo $backupCodeFieldSet;
    else: ?>
        <fieldset>
            <legend>
               Twizo configuration
            </legend>
            <p>
                Your number for Twizo is linked to your user account. If you want to unlink your number from your
                user
                account. Select disable two factor authentication and save your user profile.
            </p>
            <div class="control-group">
                <div class="control-label">
                    <label id="lbl" for="newTwizoNumber" class="hasPopover" title=""
                           data-content="Change your number for Twizo two factor authentication."
                           data-original-title="Phone number">Phone number
                    </label>
                </div>
                <div class="controls">
                    <input type="text" class="input" id="newTwizoNumber" value="<?= $currentNumber ?>"
                           placeholder="<?= $currentNumber ?>">
                    <button type="button" class="btn btn-small" id="newTwizoNumber-change"
                            onclick="openWidget('updateNumber',jQuery('#newTwizoNumber').val())">
                        <span>Update</span>
                    </button>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <label id="lbl" for="updatePreferredMethodBtn" aria-invalid="false" class="hasPopover" title=""
                           data-content="Change your preferred verification type."
                           data-original-title="Preferred verification type">Preferred verification type
                    </label>
                </div>
                <div class="controls">
                    <select id="selectPreferredMethod" onclick='' title="select preferred 2fa method"
                            style="display: none;">
                    </select>
                    <button type="button" class="btn btn-small" id="updatePreferredMethodBtn"
                            onclick='updatePreferredMethod(jQuery("#selectPreferredMethod").val())'>
                        <span>Update</span>
                    </button>
                </div>
                <script>
                    var methods = [null].concat(<?=$enabledValidations?>);

                    var sel = document.getElementById("selectPreferredMethod");

                    for (var i = 0; i < methods.length; i++) {
                        var opt = document.createElement("option");
                        opt.innerHTML = methods[i];
                        opt.value = methods[i];
                        if (methods[i] === "<?=$currentPreferredMethod?>")
                            opt.selected = "selected";
                        sel.appendChild(opt);
                    }
                </script>
            </div>
            <?php if ($backupCodesEnabled): ?>
                <div class="control-group">
                    <div class="control-label">
                        <label id="lbl" for="updateBackupCodes" class="hasPopover" title=""
                               data-content="Amount of one time backup codes left"
                               data-original-title="Backup codes left">Backup codes left</label>
                    </div>
                    <div class="controls">
                        <input type="text" disabled value="<?= $amountOfBackupCodes ?>/10">
                        <button type="button" class="btn btn-small" id="updateBackupCodes"
                                onclick="openWidget('updateBackupCodes')">
                            <span>Generate</span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </fieldset>
        <?php
        echo $backupCodeFieldSet;
    endif; ?>
<?php else: ?>
    <fieldset id="twizoSetup">
        <legend>
            Twizo
        </legend>
        <div>
            <p>
                The settings for Twizo 2fa can only be changed by the user.
                <br>
                You can disable the widget by selecting Authentication Method → "Disable Two Factor Authentication"
            </p>
            <p>
                To change your own 2fa settings log in om the front end of the website.
            </p>
        </div>
    </fieldset>
<?php endif; ?>
