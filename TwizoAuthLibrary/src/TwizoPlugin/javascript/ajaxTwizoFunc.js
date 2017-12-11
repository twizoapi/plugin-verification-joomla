/**
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */


function openWidget(functionName, number) {
    ajaxPost({
        functionName: "getWidget",
        number: number
    }).done(function (result) {
        if (jQuery.trim(result)) {
            if (result.success) {
                //If credentials are correct open the widget

                var data = result.data[0];
                callWidget(data.sessionToken, functionName, data.logoUrl, false, number);
            } else {
                showError(result.message);
            }
        } else {
            console.log("No result returned");
        }
    });
}

function register(sessionToken, number) {
    ajaxPost({
        functionName: "register",
        number: number,
        sessionToken: sessionToken,
        preferredMethod: jQuery("#selectPreferredMethod").val()
    }).done(function (result) {
        if (result.success) {
            // Hide field set for setup.
            var twizoSetupElement = jQuery("#twizoSetup");

            var data = result.data[0];
            /**@external data.backupCodesEnabled*/
            /**@external data.backupCodes*/
            if (data.backupCodesEnabled) {
                fillBackupCodes(data.backupCodes);
                allowPost = true;
                Joomla.renderMessages({
                    "Message": ["Twizo is now set up correctly!" +
                    "<p style=\"font-weight: bold; font-size: medium\"> After saving the backup codes click on submit.</p>"]
                });
                window.scrollTo(0, 0);
            } else {
                Joomla.renderMessages({
                    "Message": ["Twizo is now set up correctly!" +
                    "<p style=\"font-weight: bold; font-size: medium\"> The page will be reloaded.</p>"]
                });
                window.scrollTo(0, 0);
                // Submit the original form
                allowPost = true;
                jQuery("#member-profile").submit();
            }
        } else {
            showError(result.message);
        }
    });
}

function login(sessionToken, username, password, isTrusted) {
    ajaxPost({
        functionName: "login",
        username: username,
        password: password,
        sessionToken: sessionToken,
        isTrusted: isTrusted
    }).done(function (result) {
        if (jQuery.trim(result)) {
            if (result.success) {
                // Submit form with sessionToken as secret key
                fillForm(username, password, sessionToken);
                return;
            }
        }
        showError(result.message);
    });
}

function checkCredentials(username, password) {
    ajaxPost({
        functionName: "checkCredentials",
        username: username,
        password: password
    }).done(function (result) {
            if (jQuery.trim(result)) {
                if (result.success) {
                    //If credentials are correct open the widget

                    var data = result.data[0];
                    /**@external data.credentialsCheck*/
                    /**@external data.trustedDevice*/
                    /**@external data.logoUrl*/
                    if (!data.enabled || !data.credentialsCheck) {
                        fillForm(username, password);
                    } else if (data.trustedDevice) {
                        fillForm(username, password, "trustedDevice");
                    } else if (data.credentialsCheck) {
                        callWidget(data.sessionToken, "login", data.logoUrl, true, NaN, username, password);
                    }
                } else {
                    showError(result.message);
                }
            } else {
                console.log("No result returned");
            }
        }
    );
}

function updatePreferredMethod(preferredMethod) {
    ajaxPost({
        functionName: "updatePreferredMethod",
        preferredMethod: preferredMethod
    }).done(function (result) {
        if (jQuery.trim(result)) {
            if (result.success) {
                Joomla.renderMessages({"info": ["Successfully updated preferred method"]});
                window.scrollTo(0, 0);
                jQuery("#selectPreferredMethod").find("preferredMethod").selected("selected");
            } else {
                showError(result.message);
            }
        } else {
            console.log("No result returned");
        }
    });
}

function updateNumber(sessionToken, number) {

    ajaxPost({
        functionName: "updateNumber",
        sessionToken: sessionToken,
        number: number
    }).done(function (result) {
        if (jQuery.trim(result)) {
            if (result.success) {
                Joomla.renderMessages({"info": ["Successfully updated number"]});
                window.scrollTo(0, 0);
                var numberElement = jQuery("#newTwizoNumber");
                numberElement.val(number);
                numberElement.placeholder(number);
            } else {
                showError(result.message);
            }
        } else {
            console.log("No result returned");
        }
    });
}

function updateBackupCodes(sessionToken) {
    ajaxPost({
        functionName: "updateBackupCodes",
        sessionToken: sessionToken
    }).done(function (result) {
        if (jQuery.trim(result)) {
            if (result.success) {
                //If credentials are correct open the widget
                fillBackupCodes(result.data[0]);
            } else {
                showError(result.message);
            }
        } else {
            console.log("No result returned");
        }
    });
}

function ajaxPost(data) {
    /**@const BASE_URL */
    return jQuery.ajax({
        type: "POST",
        url: BASE_URL,
        data: jsonConcat({
            option: "com_ajax",
            plugin: "TwizoAjaxHandler",
            format: "json"
        }, data)
    });
}