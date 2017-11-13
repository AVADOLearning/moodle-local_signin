<?php

/**
 * Username form
 *
 * @author Earle Skinner <earle.skinner@avadolearning.com>
 * @copyright 2017 AVADO Learning
 */

namespace local_signin\form;

use bmdisco_domain\brand_domain;
use dml_missing_record_exception;
use local_signin\external;
use local_signin\model\user_default_domain;
use local_signin\util;
use local_signin\moodle_url;
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
        $user_attributes = array('placeholder'   => util::lang_string('form_username_placeholder'),
                                 'additionalcss' => util::lang_string('form_username_button_class'),
                                 'autofocus'     => '',
                                 'value'         => $username);
        $mform->addElement('text', 'username', util::lang_string('form_username_label'), $user_attributes);
        $mform->setType('username', PARAM_USERNAME);

        $mform->addElement('html', sprintf('<div class="%s">', util::lang_string('form_username_remusername_class')));
        $mform->addElement('advcheckbox', 'rememberme', '', util::lang_string('form_username_remusername_label'), array ('id' => 'check_rememberme'));
        $mform->addElement('html', '</div>');

        $submit_attributes = array('additionalcss' => util::lang_string('form_username_button_class'));
        $mform->addElement('submit', 'submitusername', util::lang_string('form_username_button_label'), $submit_attributes);

        $mform->addElement('html', sprintf('<div class="%s"><a href="%s">%s</a></div>',
            util::lang_string('form_userpass_forgot_class'),
            new moodle_url('/local/signin/forgot.php'),
            util::lang_string('form_username_forgot_label')));
    }

    /**
     * Validate the username field.
     *
     * @param array $data
     * @param array $files
     * @return array|null
     */
    public function validation($data, $files) {
        global $CFG, $PAGE;

        if (!array_key_exists('username', $data)) {
            return array('username' => util::lang_string('form_username_not_provided'));
        }

        $username = $data['username'];

        if (strlen($username) == 0 || !static::active_user_exists($username)) {
            return array('username' => util::lang_string('form_username_not_found_valid'));
        }

        if ($username == 'guest') {
            return;
        }

        // Redirect to other domain if necessary.
        $domain_object = user_default_domain::get($username);
        $current_domain = parse_url($CFG->wwwroot, PHP_URL_HOST);
        if ($domain_object->domain !== $current_domain) {
            $url = new moodle_url($PAGE->url, array('username' => $username));
            $url->set_host($domain_object->domain);
            redirect($url);
        }

        return;
    }

    /**
     * Confirms whether the username belongs to an active user (neither deleted, nor suspended).
     *
     * @param $username
     * @return boolean
     */
    public static function active_user_exists($username) {
        global $DB;
        return $DB->record_exists('user', array('username' => $username, 'deleted' => 0, 'suspended' => 0));
    }
}
