<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

use local_signin\event\password_reset_request_attempt;
use local_signin\event\password_reset_request_complete;
use local_signin\form\forgot_form;
use local_signin\helper\recovery_helper;
use local_signin\util;
use local_helpdesk\Helper\SigninLinkHelper;

require_once dirname(dirname(__DIR__)) . '/config.php';
/** @var core_renderer $OUTPUT */
/** @var moodle_page   $PAGE */
require_once "{$CFG->dirroot}/login/set_password_form.php";

$flash = optional_param('flash', -1, PARAM_INT);
$token = optional_param('token', '', PARAM_ALPHANUM);

$forgotten = get_string('form_page_title', util::MOODLE_COMPONENT);
$login     = get_string('login');

if (version_compare(moodle_major_version(), '3.4', '<')) {
    $PAGE->https_required();
}
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

        $guest = guest_user();
        $event = password_reset_request_complete::create(array(
            'userid'   => $guest->id,
            'objectid' => $reset->resetid,
            'other'    => array(
                'userid'   => property_exists($reset, 'id')       ? $reset->id       : '<null>',
                'username' => property_exists($reset, 'username') ? $reset->username : '<null>' ,
                'status'   => $status,
            ),
        ));
        $event->trigger();

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
        if (version_compare(moodle_major_version(), '3.4', '<')) {
            $PAGE->verify_https_required();
        }

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
        if ($user) {
            $status = recovery_helper::begin_recovery($user);

            $guest = guest_user();
            $event = password_reset_request_attempt::create(array(
                'userid' => $guest->id,
                'other'  => array(
                    'userid'   => property_exists($user, 'id')       ? $user->id       : '<null>',
                    'username' => property_exists($user, 'username') ? $user->username : '<null>' ,
                    'status'   => $status,
                    'values'   => (array) $values,
                ),
            ));
            $event->trigger();
        }

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
        if (version_compare(moodle_major_version(), '3.4', '<')) {
            $PAGE->verify_https_required();
        }

        if ($flashmsg = recovery_helper::get_flash_message($flash)) {
            $flashclass = recovery_helper::is_successful($flash)
                    ? 'notifysuccess' : 'notifyproblem';
            $flashmsg = $OUTPUT->notification($flashmsg, $flashclass);
        }

        list($url, $urlTitle) = SigninLinkHelper::getActionLink();

        echo
            $OUTPUT->header(),
            $flashmsg,
            $mform->render(),
            "<a href=$url>$urlTitle</a>",
            $OUTPUT->footer();
    }
}
