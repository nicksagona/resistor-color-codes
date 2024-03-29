/**
 * app.js
 */

var app = {
    addEmail: function(emailId) {
        var emailUser   = ['t','c','a','t','n','o','c'];
        var emailDomain = window.location.host.replace('www.', '').replace('resistors.', '');
        var email       = emailUser.reverse().join('') + '@' + emailDomain;
        var emailHref   = 'mailto:' + email + '?subject=SonicTone Resistor Color Codes';
        var emailTags   = $(emailId);
        for (var i = 0; i < emailTags.length; i++) {
            emailTags[i].innerHTML = email;
            $(emailTags[i]).attr('href', emailHref);
        }
    }
};

$(document).ready(function(){
    if ($('a.contact-email')[0] != undefined) {
        app.addEmail('a.contact-email');
    }
});