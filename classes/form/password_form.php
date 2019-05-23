<?php

/**
 * Password form
 *
 * @author Earle Skinner <earle.skinner@avadolearning.com>
 * @copyright 2017 AVADO Learning
 */

namespace local_signin\form;

use coding_exception;
use local_signin\util;
use moodle_url;
use moodleform;

defined('MOODLE_INTERNAL') || die;

class password_form extends no_sesskey_form {
    /**
     * @override \moodleform
     */
    public function definition() {
        global $frm;

        $mform  = $this->_form;

        $username = '';
        if (isset($frm) && isset($frm->username) && $frm->username) {
            $username = $frm->username;
        }
        $user_attributes = array('placeholder'   => util::lang_string('form_username_placeholder'),
                                 'additionalcss' => util::lang_string('form_username_button_class'),
                                 'readonly'      => '',
                                 'value'         => $username);
        $mform->addElement('text', 'username', util::lang_string('form_username_label'), $user_attributes);
        $mform->setType('username', PARAM_USERNAME);

        $pass_attributes = array('placeholder'   => util::lang_string('form_password_placeholder'),
                                 'additionalcss' => util::lang_string('form_password_button_class'),
                                 'autofocus'     => '');
        $mform->addElement('password', 'password', util::lang_string('form_password_label'), $pass_attributes);
        $mform->setType('password', PARAM_RAW);

        $mform->addElement('hidden', 'rememberme', 0, array ('id' => 'check_rememberme'));
        $mform->setType('rememberme', PARAM_INT);

        $submit_attributes = array('additionalcss' => util::lang_string('form_password_button_class'));
        $mform->addElement('submit', 'submitpassword', util::lang_string('form_password_button_label'), $submit_attributes);

        $mform->addElement('html', sprintf(util::ELEMENT_WRAPPER,
            util::lang_string('form_password_changeusername_class'),
            new moodle_url('/local/signin/change_user.php'),
            util::lang_string('form_password_changeusername_label')));

        $mform->addElement('html', sprintf(util::ELEMENT_WRAPPER,
            util::lang_string('form_userpass_forgot_class'),
            new moodle_url('/local/signin/forgot.php'),
            util::lang_string('form_password_forgot_label')));

        $mform->addElement('html', sprintf(util::ELEMENT_WRAPPER,
            util::lang_string('form_userpass_forgot_class'),
            new moodle_url('/local/recoveraccount/index.php'),
            util::lang_string('form_account_recover_label')));
    }
}
