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
        $mform  = $this->_form;

        $pass_attributes = array('placeholder'   => $this->lang_string('form_password_placeholder'),
                                 'additionalcss' => $this->lang_string('form_password_button_class'));
        $mform->addElement('password', 'password', $this->lang_string('form_password_label'), $pass_attributes);
        $mform->setType('password', PARAM_RAW);

        $submit_attributes = array('additionalcss' => $this->lang_string('form_password_button_class'));
        $mform->addElement('submit', 'submitpassword', $this->lang_string('form_password_button_label'), $submit_attributes);
    }

    public function lang_string($id) {
        return get_string($id, util::MOODLE_COMPONENT);
    }
}