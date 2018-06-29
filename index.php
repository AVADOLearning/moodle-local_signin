<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

use local_signin\form\password_form;
use local_signin\form\username_form;
use local_signin\helper\login_helper;
use local_signin\util;

require_once dirname(dirname(__DIR__)) . '/config.php';
/** @var moodle_page $PAGE */

redirect_if_major_upgrade_required();

$helper = new login_helper();

$helper->handle_cancel_request();

// Will redirect at this point if the user is logged in
$helper->handle_testsession_request();

if ($helper->is_user_already_loggedin()) {
    $helper->redirect_to_logout_page();
}

if (version_compare(moodle_major_version(), '3.4', '<')) {
    $PAGE->https_required();
}

$PAGE->set_cacheable(false);
$PAGE->set_url(new moodle_url('/local/signin/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
$site = get_site();
$PAGE->set_title($site->fullname);
$PAGE->set_heading($site->fullname);
$helper->additional_meta_tags();

/** @var local_signin_renderer $renderer */
$renderer = $PAGE->get_renderer(util::MOODLE_COMPONENT);

$helper->set_wants_url();
$helper->handle_session_timeout();

$helper->reset_auth_global_vars();
$helper->auth_plugin_bootstrapper();

$usernameform = new username_form($helper->get_login_url());
$passwordform = new password_form($helper->get_login_url());

$username = '';

// If true, an auth plugin has filled out the form on behalf of the user
// See auth_plugin_bootstrapper function
if (!$helper->is_auth_global_vars_populated()) {
    $helper->create_new_user_object();

    // Form submissions go here
    if ($usernameform->is_submitted() &&
        $usernameform->is_validated()) {
        $values = $usernameform->get_data();
        $helper->set_userform_params_in_auth_global_vars($values);

        // Need to parse the username field to the password form
        list($username, $rememberme) = $helper->get_userform_params_from_auth_global_vars();
        $passwordform->set_data(array(
            'username' => $username,
            'rememberme' => $rememberme,
        ));
    }

    // If user is not logged in but cookie exists (because 'remember me' has been previously checked),
    // set the form data so that the user goes straight to password form
    if (get_moodle_cookie()) {
        global $frm;
        $username = $helper->get_username_from_querystring_or_cookie();
        $frm->username = $username;
        $passwordform->set_data(array(
            'username' => $username,
            'rememberme' => 1,
        ));
    }

    if ($passwordform->is_submitted() &&
        $passwordform->is_validated()) {
        $values = $passwordform->get_data();
        $helper->set_passform_params_in_auth_global_vars($values);
    }
}

if ($helper->authenticate()) {
    if ($helper->user_needs_to_change_their_password()) {
        // Gives the user a chance to change their password
        redirect(new moodle_url('/local/signin/expired_user.php'));
    }

    $helper->set_remember_me();
    redirect($helper->get_test_session_url());
} else {
    // The user object is populated if exited at correct stage
    list($confirm, $email) = $helper->user_needs_to_confirm_account($username);
    if ($confirm) {
        $PAGE->set_title(get_string('mustconfirm'));
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('mustconfirm'));
        echo $OUTPUT->box(get_string('emailconfirmsent', '', $email), 'generalbox boxaligncenter');
        echo $OUTPUT->footer();
        die;
    }

    list($restore, $username) = $helper->user_needs_to_restore_account();
    if ($restore) {
        $PAGE->set_title(get_string('restoredaccount'));
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('restoredaccount'));
        echo $OUTPUT->box(get_string('restoredaccountinfo'), 'generalbox boxaligncenter');
        require_once($CFG->dirroot.'/login/restored_password_form.php'); // Use our "supplanter" login_forgot_password_form. MDL-20846
        $login_forgot_password_frm = new login_forgot_password_form($CFG->wwwroot . '/login/forgot_password.php', array('username' => $username));
        echo $renderer->forgot_password_form($login_forgot_password_frm);
        echo $OUTPUT->footer();
        die;
    }
}

echo $OUTPUT->header();

// This variable is only used for testing the non-JS workflow in Behat.
$nojs = optional_param('nojs', 0, PARAM_BOOL);
if (!$nojs) {
    $PAGE->requires->js_call_amd('local_signin/login', 'init');
}

if ($helper->is_username_set_in_auth_global_vars()) {
    echo $renderer->password_form($passwordform);
} else {
    if ($paramusername = $helper->get_username_from_querystring_or_cookie()) {
        $passwordform->set_data(array(
            'username' => $paramusername,
            'rememberme' => 0,
        ));
        echo $renderer->password_form($passwordform);
    } else {
        $usernameform->set_data(array('username' => $helper->get_username_from_querystring_or_cookie()));
        echo $renderer->login_form($usernameform, $passwordform);
    }
}

echo $OUTPUT->footer();
die;
