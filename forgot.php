<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

use local_signin\form\forgot_form;
use local_signin\helper\recovery_helper;
use local_signin\util;

require_once dirname(dirname(__DIR__)) . '/config.php';
require_once "{$CFG->dirroot}/login/set_password_form.php";

$flash = optional_param('flash', '', PARAM_ALPHANUM);
$token = optional_param('token', '', PARAM_ALPHANUM);

$forgotten = get_string('passwordforgotten');
$login     = get_string('login');

$PAGE->https_required();
$PAGE->set_cacheable(false);
$PAGE->set_url(new moodle_url('/local/signin/forgot.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title($forgotten);
$PAGE->set_heading($COURSE->fullname);

$PAGE->navbar->add($login, get_login_url());
$PAGE->navbar->add($forgotten);

if (isloggedin() && !isguestuser()) {
    redirect(new moodle_url('/index.php'));
}

if ($token) {
    $status = recovery_helper::validate_token($token);

    if ($status !== recovery_helper::STATUS_VALID_TOKEN) {
        redirect(recovery_helper::get_reset_url(array('flash' => $status)));
    }

    $reset = recovery_helper::get_reset_info($token);

    $mform = new login_set_password_form();
    $mform->set_data(array(
        'username'  => $reset->username,
        'username2' => $reset->username,
        'token'     => $reset->resettoken,
    ));

    if ($values = $mform->get_data()) {
        $status = recovery_helper::set_password($reset, $values->password);

        switch ($status) {
            case recovery_helper::STATUS_ERROR_UPDATING_PASSWORD:
                print_error('errorpasswordupdate', 'auth');
                break;
            case recovery_helper::STATUS_PASSWORD_RESET:
                if ($reset->lang) {
                    unset($SESSION->lang);
                }
                complete_user_login($reset);

                unset($SESSION->wantsurl);
                redirect(
                    core_login_get_return_url(),
                    get_string('passwordset'));
        }
    } else {
        $PAGE->verify_https_required();

        echo
            $OUTPUT->header(),
            $mform->render(),
            $OUTPUT->footer();
    }
} else {
    $mform = new forgot_form(null, array(
        'forgotmethods' => explode(',', get_config(
                util::MOODLE_COMPONENT, util::SETTING_FORGOT_METHODS)),
    ));

    if ($values = $mform->get_data()) {
        $user = $mform->locate_user();
        $status = recovery_helper::begin_recovery($user);

        if ($CFG->protectusernames) {
            notice(get_string('emailpasswordconfirmmaybesent'), new moodle_url('/index.php'));
        } else {
            switch ($status) { // 0124
                case recovery_helper::STATUS_DIRECTIONS_SENT:
                case recovery_helper::STATUS_TOKEN_SENT:
                case recovery_helper::STATUS_NONE_SENT:
                case recovery_helper::STATUS_ALREADY_SENT:
                    redirect(recovery_helper::get_reset_url(array('flash' => $status)));

                case recovery_helper::STATUS_ERROR:
                    print_error('cannotmailconfirm');
            }
        }
    } else {
        $PAGE->verify_https_required();

        if ($flashmsg = recovery_helper::get_flash_message($flash)) {
            $flashclass = recovery_helper::is_successful($flash)
                    ? 'notifysuccess' : 'notifyproblem';
            $flashmsg = $OUTPUT->notification($flashmsg, $flashclass);
        }

        echo
            $OUTPUT->header(),
            $flashmsg,
            $mform->render(),
            $OUTPUT->footer();
    }
}
