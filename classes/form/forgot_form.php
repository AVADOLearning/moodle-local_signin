<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\form;

use coding_exception;
use local_signin\util;
use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once "{$CFG->libdir}/formslib.php";

/**
 * Forgotten password form.
 */
class forgot_form extends moodleform {
    /**
     * @override \moodleform
     */
    public function __construct($action=null, $customdata=null, $method='post',
                                $target='', $attributes=null, $editable=true) {
        if (!array_key_exists('forgotmethods', $customdata)
                || !is_array($customdata['forgotmethods'])) {
            throw new coding_exception('$customdata must contain the forgotmethods key');
        }

        parent::moodleform($action, $customdata, $method, $target, $attributes,
                           $editable);
    }

    /**
     * Is the specified password recovery method enabled?
     *
     * @param string $method One of the \local_signin\util::FORGOT_METHOD_*
     *                       values.
     *
     * @return boolean
     */
    protected function has_forgot_method($method) {
        return in_array($method, $this->_customdata['forgotmethods']);
    }

    /**
     * @override \moodleform
     */
    public function definition() {
        $mform  = $this->_form;
        $submit = get_string('search');

        if ($this->has_forgot_method('username')) {
            $mform->addElement(
                'header', 'searchbyusername',
                get_string('searchbyusername'));
            $mform->addElement('text', 'username', get_string('username'));
            $mform->setType('username', PARAM_RAW);
            $mform->addElement('submit', 'submitusername', $submit);
        }

        if ($this->has_forgot_method('email')) {
            $mform->addElement(
                    'header', 'searchbyemail', get_string('searchbyemail'));
            $mform->addElement('text', 'email', get_string('email'));
            $mform->setType('email', PARAM_RAW_TRIMMED);
            $mform->addElement('submit', 'submitemail', $submit);
        }
    }

    protected function get_password_recovery_method($data=null) {
        if ($data === null) {
            $data = $this->get_data();
        }

        if (is_object($data)) {
            $data = (array) $data;
        }

        $submitbuttons = array_map(function($method) {
            return "submit{$method}";
        }, util::get_password_recovery_methods());

        $submittedbuttons = array_intersect($submitbuttons, array_keys($data));
        if (count($submittedbuttons) !== 1) {
            throw new coding_exception('Exactly one password recovery method should be supplied');
        }

        $methodname = array_keys($submittedbuttons)[0];
        return util::get_password_recovery_method($methodname);
    }

    public function locate_user() {
        $method = $this->get_password_recovery_method();

        return $method->locate_user($this->get_data());
    }

    /**
     * @override \moodleform
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $method = $this->get_password_recovery_method($data);

        return array_merge($errors, $method->validate($data, $files));
    }
}
