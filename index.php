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

require_once dirname(dirname(__DIR__)) . '/config.php';

redirect_if_major_upgrade_required();

$helper = new \local_signin\helper\login_helper();

$helper->handle_cancel_request();

// Will redirect at this point if the user is logged in
$helper->handle_testsession_request();

if ($helper->is_user_already_loggedin()) {
    $helper->redirect_to_logout_page();
}

$PAGE->https_required();
$PAGE->set_cacheable(false);
$PAGE->set_url(new moodle_url('/local/signin/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
$site = get_site();
$PAGE->set_title($site->fullname);
$PAGE->set_heading($site->fullname);
$helper->additional_meta_tags();

$helper->handle_session_timeout();

$helper->reset_auth_global_vars();
$helper->auth_plugin_bootstrapper();

$username_form = new \local_signin\form\username_form();
$password_form = new \local_signin\form\password_form();

// If true, a auth plugin has filled out the form on behalf of the user
// See auth_plugin_bootstrapper function
if (!$helper->is_auth_global_vars_populated()) {
    $helper->create_new_user_object();

    // Form submissions go here
    if ($username_form->is_submitted() &&
        $username_form->is_validated()) {
        $values = $username_form->get_data();
        $helper->set_username_in_auth_global_vars($values);

        // Need to parse the username field to the password form
        $password_form->set_data(array('username' => $helper->get_username_in_auth_global_vars()));
    }
    if ($password_form->is_submitted() &&
        $password_form->is_validated()) {
        $values = $password_form->get_data();
        $helper->set_password_in_auth_global_vars($values);
    }
}

if ($helper->authenticate()) {
    if ($helper->user_needs_to_change_their_password()) {
        // Gives the user a chance to change their password
        redirect(new moodle_url('/local/expired_user.php'));
    }

    $helper->set_remember_me();
    redirect($helper->get_return_url());
} else {
    // The user object is populated if exited at correct stage
    list($confirm, $email) = $helper->user_needs_to_confirm_account();
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
        $login_forgot_password_frm->display();
        echo $OUTPUT->footer();
        die;
    }
}

echo $OUTPUT->header();

if ($helper->is_username_set_in_auth_global_vars()) {
    $password_form->display();
} else {
    $username_form->set_data(array('username' =>
        $helper->get_username_from_querystring_or_cookie()));
    $username_form->display();
}

echo $OUTPUT->footer();
die;