<?php

/**
 * Username form
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

class username_form extends moodleform {
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
                                 'autofocus'     => '',
                                 'value'         => $username);
        $mform->addElement('text', 'username', $this->lang_string('form_username_label'), $user_attributes);
        $mform->setType('username', PARAM_RAW);

        $returnurl = optional_param('returnurl', '', PARAM_URL);
        $mform->addElement('hidden', 'returnurl', $returnurl);
        $mform->setType('returnurl', PARAM_URL);

        $mform->addElement('html', sprintf('<div class="%s">', $this->lang_string('form_username_remusername_class')));
        $mform->addElement('advcheckbox', 'rememberme', '', $this->lang_string('form_username_remusername_label'));
        $mform->addElement('html', '</div>');

        $submit_attributes = array('additionalcss' => $this->lang_string('form_username_button_class'));
        $mform->addElement('submit', 'submitusername', $this->lang_string('form_username_button_label'), $submit_attributes);

        $mform->addElement('html', sprintf('<div class="%s"><a href="%s">%s</a></div>',
            $this->lang_string('form_userpass_forgot_class'),
            new \moodle_url('/local/signin/forgot.php'),
            $this->lang_string('form_username_forgot_label')));
    }

    public function lang_string($id) {
        return get_string($id, util::MOODLE_COMPONENT);
    }

    public function validation($data, $files) {
        if (array_key_exists('username', $data) && strlen($data['username']) == 0) {
            return array('username' => $this->lang_string('form_username_not_provided'));
        }
    }
}
