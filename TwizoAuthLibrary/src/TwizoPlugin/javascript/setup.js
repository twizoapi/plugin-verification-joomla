/**
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */
jQuery(document).ready(function () {

    // Hide the secretKey field. (Only for plugin access).
    jQuery("[name='secretkey']").closest('.control-group').hide();

    // Remove One time emergency Password field joomla uses.
    jQuery("legend:contains('One time emergency')").parent().hide();
});

var allowPost = false;
jQuery(document).on("submit", function (event) {
    var loginAttempt = false;
    for (var i = 0; i < event.target.length; i++) {
        if (event.target[i].name === "task" && event.target[i].value.endsWith("login")) {
            loginAttempt = true;
        }
    }

    var setupAttempt = false;
    var twizoSelected = false;
    for (var j = 0; j < event.target.length; j++) {
        if (event.target[j].id === "jform_twofactor_method")
            for (var k = 0; k < event.target[j].length; k++) {
                if (event.target[j][k].value === "TwizoAuth")
                    twizoSelected = event.target[j][k].selected
            }
        if (event.target[j].id === "twizoNumber" && event.target[j].value && twizoSelected) {
            setupAttempt = true;
        }
    }

    if (allowPost)
        return;

    if (loginAttempt || event.target.action.endsWith("login")) {
        event.target.id = "login-form";
        event.preventDefault();

        twizoLogin(jQuery("[name='username']"), jQuery("input[name^='passw']"));
    }
    else if (setupAttempt) {
        event.preventDefault();
        openWidget('register', jQuery('#twizoNumber').val());
    }
});

