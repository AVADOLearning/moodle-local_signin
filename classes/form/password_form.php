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
use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once "{$CFG->libdir}/formslib.php";

class password_form extends moodleform {
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
        $user_attributes = array('placeholder'   => $this->lang_string('form_username_placeholder'),
                                 'additionalcss' => $this->lang_string('form_username_button_class'),
                                 'readonly'      => '',
                                 'value'         => $username);
        $mform->addElement('text', 'username', $this->lang_string('form_username_label'), $user_attributes);
        $mform->setType('username', PARAM_RAW);

        $pass_attributes = array('placeholder'   => $this->lang_string('form_password_placeholder'),
                                 'additionalcss' => $this->lang_string('form_password_button_class'),
                                 'autofocus'     => '');
        $mform->addElement('password', 'password', $this->lang_string('form_password_label'), $pass_attributes);
        $mform->setType('password', PARAM_RAW);

        $mform->addElement('hidden', 'rememberme', 0);
        $mform->setType('rememberme', PARAM_INT);

        $returnurl = '';
        if (isset($frm) && isset($frm->returnurl) && $frm->returnurl) {
            $returnurl = $frm->returnurl;
        }
        $mform->addElement('hidden', 'returnurl', $returnurl);
        $mform->setType('returnurl', PARAM_URL);

        $submit_attributes = array('additionalcss' => $this->lang_string('form_password_button_class'));
        $mform->addElement('submit', 'submitpassword', $this->lang_string('form_password_button_label'), $submit_attributes);

        $mform->addElement('html', sprintf('<div class="%s"><a href="%s">%s</a></div>',
            $this->lang_string('form_password_changeusername_class'),
            new \moodle_url('/local/signin/change_user.php'),
            $this->lang_string('form_password_changeusername_label')));

        $mform->addElement('html', sprintf('<div class="%s"><a href="%s">%s</a></div>',
            $this->lang_string('form_userpass_forgot_class'),
            new \moodle_url('/local/signin/forgot.php'),
            $this->lang_string('form_password_forgot_label')));
    }

    public function lang_string($id) {
        return get_string($id, util::MOODLE_COMPONENT);
    }
}
