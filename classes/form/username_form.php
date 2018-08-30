<?php

/**
 * Username form
 *
 * @author Earle Skinner <earle.skinner@avadolearning.com>
 * @updated Magyar-Hunor Tamas <magyar-hunor.tamas@avadolearning.com>
 *
 * @copyright 2018 AVADO Learning
 */

namespace local_signin\form;

use local_signin\domainfinder\user_default_domain;
use local_signin\moodle_url;
use local_signin\util;

defined('MOODLE_INTERNAL') || die;

class username_form extends no_sesskey_form {
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
        $mform->addElement('checkbox', 'rememberme', '', util::lang_string('form_username_remusername_label'), array ('id' => 'check_rememberme'));
        $mform->addElement('html', '</div>');

        $submit_attributes = array('additionalcss' => util::lang_string('form_username_button_class'));
        $mform->addElement('submit', 'submitusername', util::lang_string('form_username_button_label'), $submit_attributes);

        $mform->addElement('html', sprintf(util::ELEMENT_WRAPPER,
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

        if (!isset($data['username']) || $data['username'] == '') {
            return array('username' => util::lang_string('form_username_not_provided'));
        }

        $username = $data['username']; // this can be username or email used to login

        if (static::number_of_accounts_for_user($username) > 1) {
            return array('username' => util::lang_string('duplicate_field'));
        }

        if (strlen($username) == 0 || !static::active_user_exists($username)) {
            return array('username' => util::lang_string('form_username_not_found_valid'));
        }

        if ($username == 'guest') {
            return;
        }

        // Redirect to other domain if necessary.
        $defaultdomain = user_default_domain::get($username);
        $currentdomain = parse_url($CFG->wwwroot, PHP_URL_HOST);
        if ($defaultdomain->domain !== $currentdomain) {
            $url = new moodle_url($PAGE->url, array('username' => $username));
            $url->set_host($defaultdomain->domain);
            redirect($url);
        }

        return;
    }

    /**
     * Confirms whether the username belongs to an active user (neither deleted, nor suspended).
     *
     * @param $username
     * @return bool
     * @throws \dml_exception
     */
    public static function active_user_exists($username) {
        global $DB;
        $select = "deleted = 0 AND suspended = 0 AND (username = ? OR email = ?)";
        return $DB->record_exists_select('user', $select, array($username, $username));
    }

    /**
     * Returns number of accounts with given argument, if that is an email
     *
     * @param $email
     * @return int
     * @throws \dml_exception
     */
    public static function number_of_accounts_for_user($email) {
        global $DB;
        return $DB->count_records('user', array('email' => "{$email}"));
    }
}
