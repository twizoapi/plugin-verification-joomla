/**
 * @author      Yarince Martis <info@twizo.com>
 * @copyright   Copyright (c) 2016-2017 Twizo
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://twizo.com
 * @since       0.1.0
 */

function jsonConcat(o1, o2) {
    for (var key in o2) {
        o1[key] = o2[key];
    }
    return o1;
}

function showError(error) {
    console.log(error);
    Joomla.renderMessages({"Error": [error]});
    window.scrollTo(0, 0);
}

function fillForm(username, password, sessionToken) {
    jQuery("input[name='username']").val(username);
    jQuery("input[name^='passw']").val(password);
    jQuery("input[name='secretkey']").val(sessionToken);

    allowPost = true;
    jQuery("form[id='login-form']").submit();
}

function fillBackupCodes(codes) {
    // Create dynamic table.
    var table = document.createElement("table");

    // Add json data to the table as rows.
    var tr = document.createElement("tr");
    for (var i = 0; i < codes.length; i++) {

        if (i % 2 === 0)
            tr = table.insertRow(-1);

        var tabCell = tr.insertCell(-1);
        tabCell.innerHTML = codes[i];
    }

    // Finally add the newly created table with json data to a container.
    var elementById = document.getElementById("backupCodesTable");
    elementById.innerHTML = "";
    elementById.appendChild(table);
    jQuery("#twizoBackupCodes").show();
}


function twizoLogin(username, password) {

    checkCredentials(username.val(), password.val());
}

function callWidget(sessionToken, functionName, logoUrl, askTrusted, number, username, password) {
    const handler = TwizoWidget.configure({
        sessionToken: sessionToken,
        askTrusted: askTrusted,
        logoUrl: logoUrl,
        trustedDays: 30
    });

    handler.open(function (sessionToken, isError, errorCode, returnData) {

        if (!isError) {


            switch (functionName) {
                case "register":
                    register(sessionToken, number);
                    break;
                case "login":
                    login(sessionToken, username, password, returnData.isTrusted);
                    break;
                case "updateBackupCodes":
                    updateBackupCodes(sessionToken);
                    break;
                case "updateNumber":
                    updateNumber(sessionToken, number);
                    break;
            }
        }
        else {
            Joomla.renderMessages({"Error": ["Verification error " + errorCode]});
        }
    });
}

function printDiv(printpage) {
    var headstr = "<html><head><title></title></head><body>";
    var footstr = "</body>";
    var newstr = document.all.item(printpage).innerHTML;
    var oldstr = document.body.innerHTML;
    document.body.innerHTML = headstr + newstr + footstr;
    window.print();
    document.body.innerHTML = oldstr;
    return false;
}