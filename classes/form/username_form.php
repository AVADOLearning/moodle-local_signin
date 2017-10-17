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

global $CFG;

require_once "{$CFG->libdir}/formslib.php";

class username_form extends moodleform {
    /**
     * @override \moodleform
     */
    public function definition() {
        $mform  = $this->_form;

        $returnurl = optional_param('returnurl', '', PARAM_URL);

        $user_attributes = array('placeholder'   => $this->lang_string('form_username_placeholder'),
                                 'additionalcss' => $this->lang_string('form_username_button_class'));
        $mform->addElement('text', 'username', $this->lang_string('form_username_label'), $user_attributes);
        $mform->setType('username', PARAM_RAW);

        $mform->addElement('hidden', 'returnurl', $returnurl);
        $mform->setType('returnurl', PARAM_URL);

        $submit_attributes = array('additionalcss' => $this->lang_string('form_username_button_class'));
        $mform->addElement('submit', 'submitusername', $this->lang_string('form_username_button_label'), $submit_attributes);
    }

    public function lang_string($id) {
        return get_string($id, util::MOODLE_COMPONENT);
    }
}