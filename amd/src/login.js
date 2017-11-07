/**
 * Javascript to handle signin logic.
 *
 * @module     local_signin/login
 * @package    local_signin
 *
 */
define(['jquery', 'core/ajax', 'core/str'], function($, ajax, str) {
    const WEB_SERVICE_METHOD_NAME = "bmdisco_domain_check_domain";
    var defaults = {
        form : {
            window     : '#local-signin',
            alert      : '.alert'
        },
        username : {
            container  : '.username_form',
            input      : '#id_username',
            submit     : '#id_submitusername',
            rememberme : '#check_rememberme',
            returnurl  : '#id_returnurl',
            validation : handleUsernameSubmission
        },
        password : {
            container  : '.password_form',
            input      : '#id_password',
            submit     : '#id_submitpassword',
            rememberme : '#check_rememberme',
            returnurl  : '#id_returnurl',
            changeuser : '.changeuser',
            validation : handlePasswordSubmission
        }
    };

    return {
        init: function(options) {
            var $options = $.extend(defaults, options);

            // Locate dom elements
            var $usernameContainer = $($options.username.container);
            var $passwordContainer = $($options.password.container);
            $options.dom = {
                username : {
                    container  : $usernameContainer,
                    form       : $usernameContainer.find('form'),
                    input      : $usernameContainer.find($options.username.input),
                    rememberme : $usernameContainer.find($options.username.rememberme),
                    returnurl  : $usernameContainer.find($options.username.returnurl),
                    submit     : $usernameContainer.find($options.username.submit)
                },
                password : {
                    container  : $passwordContainer,
                    form       : $passwordContainer.find('form'),
                    username   : $passwordContainer.find($options.username.input),
                    input      : $passwordContainer.find($options.password.input),
                    rememberme : $passwordContainer.find($options.password.rememberme),
                    returnurl  : $passwordContainer.find($options.password.returnurl),
                    submit     : $passwordContainer.find($options.password.submit),
                    changeuser : $passwordContainer.find($options.password.changeuser)
                }
            };

            // Submit button is disabled/enabled on both user & password frames if input box is empty/filled.
            if ($options.dom.username.input.length === 0) {
                $options.dom.username.submit.attr('disabled', 'disabled');
            }
            if ($options.dom.password.input.length === 0) {
                $options.dom.password.submit.attr('disabled', 'disabled');
            }

            // Change state of the submit buttons after input
            $options.dom.username.input.on('input', $options.dom.username, handleFormInputChange);
            $options.dom.password.input.on('input', $options.dom.password, handleFormInputChange);

            // Handle change user link
            $options.dom.password.changeuser.on('click', $options.dom, toggleForms);

            // Handle form submissions
            $options.dom.username.submit.on('click', $options.dom, $options.username.validation);
            $options.dom.password.submit.on('click', $options.dom, $options.password.validation);
        }
    };

    function handleFormInputChange($event) {
        var $options = $event.data;
        $options.input.val() === '' ?
            $options.submit.attr('disabled', 'disabled') :
            $options.submit.removeAttr('disabled');
    }

    function handleUsernameSubmission($event) {
        $event.preventDefault();
        var $options = $event.data;
        var username = $options.username.input.val();

        if (username.toLowerCase() !== 'guest') {
            setRememberme($options);
            setReturnurl($options);
            checkDomain(username, $options);
        } else {
            // Populate the username field in the password form
            $options.password.username.val(username);
            $options.password.form.submit();
        }
    }

    function handlePasswordSubmission($event) {
        $event.preventDefault();
        var $options = $event.data;
        var username = $options.password.username.val();

        queryWebservice(username)[0].done(function(response) {
            var currentURL = new URL(M.cfg.wwwroot).hostname;
            if(response.domain !== currentURL) {
                handleDomainRedirect(response, username);
            } else {
                $options.password.form.submit();
            }
        });
    }

    function setRememberme($options) {
        var $remFromUserForm = $options.username.rememberme;
        var $remFromPassForm = $options.password.rememberme;
        if ($remFromUserForm.prop('checked')) {
            $remFromPassForm.val("1");
        }
    }

    function setReturnurl($options) {
        var retFromUserFormVal = $options.username.returnurl.val();
        var $retFromPassForm = $options.password.returnurl;
        if (retFromUserFormVal) {
            $retFromPassForm.val(retFromUserFormVal);
        }
    }

    function checkDomain(username, $options) {
        var result = false;
        queryWebservice(username)[0].done(function(response) {
            result = handleDomainRedirect(response, username, $options);
        }).fail(function() {
            get_string_and_notify('danger', 'invalid_user');
            result = false;
        });
        return result;
    }

    function queryWebservice(username){
        return ajax.call([
            {
                methodname: WEB_SERVICE_METHOD_NAME,
                args: {
                    username: username
                }
            }
        ]);
    }

    function handleDomainRedirect(response, username, $options) {
        var currentURL = new URL(M.cfg.wwwroot);
        if (response.email === null) {
            // flag non-existent user error
            get_string_and_notify('warning', 'non_existent_user');
            return false;
        } else if (response.domain !== currentURL.hostname) {
            // Redirect to recorded user default domain
            window.location = window.location.protocol + '//' + response.domain + currentURL.pathname +
                '/local/signin/index.php?username=' + username;
            return false;
        } else {
            $(defaults.form.alert).alert('close');
            // Stay on page & flip over to password form
            // Populate username field on password form
            $options.password.username.val(username);
            // Toggle the password form
            $options.password.changeuser.click();
            $options.password.submit.on('click', $options.dom, $options.password.validation);
        }
        return true;
    }

    /**
     *
     * @param type - ('Success', 'info', 'warning', 'danger')
     * @param message - Message to be displayed
     */
    function notify(type, message) {
        $(defaults.form.alert).alert('close');
        $('<div>')
            .addClass('alert alert-' + type + ' alert-dismissable')
            .text(message)
            .appendTo(defaults.form.window)
    }

    function toggleForms($event) {
        $event.preventDefault();
        var $options = $event.data;
        $options.password.container.toggleClass('hide');
        $options.username.container.toggleClass('hide');
    }

    /**
     *
     * @param notification_type - ('Success', 'info', 'warning', 'danger')
     * @param string_ref - Pointer for lang/.../local_signin.php string
     */
    function get_string_and_notify(notification_type, string_ref){
        var string = str.get_string(string_ref, 'local_signin', null);
        $.when(string).done(function (string) {
            notify(notification_type, string);
        });
    }

});
