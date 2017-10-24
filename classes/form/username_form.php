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
    const DEFAULT_DOMAIN_FROM_USERNAME = <<<SQL
SELECT
  bd.domain
FROM
  {user} u
INNER JOIN
  {cohort_members} cm ON u.id = cm.userid
INNER JOIN
  {brandmanager_brand_cohort} bc ON cm.cohortid = bc.cohortid
INNER JOIN
  {brandmanager_brand_domain} bd ON bc.brandid = bd.brandid
WHERE
  (u.username = :username
AND
  bd.defaultdomain = 1)
SQL;

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

    /**
     * Validate the username field.
     *
     * @param array $data
     * @param array $files
     * @return mixed
     */
    public function validation($data, $files) {
        global $CFG;

        // A username field must exist in the form data.
        if (array_key_exists('username', $data)) {
            $username = $data['username'];

            // Error if the username is empty string.
            if (strlen($username) == 0) {
                return array('username' => $this->lang_string('form_username_not_provided'));
            } else {
                // Return/exit if the username is 'guest'.
                if ($username == 'guest') {
                    return;
                }

                // The non-guest username must exist in the campus database.
                if (static::user_exists($username)) {
                    // The user must have a brand association via a cohort.
                    if (static::get_default_domain($username) != null) {
                        // Redirect the user if they are on the wrong domain.
                        $correct_domain = static::get_default_domain($username)->domain;
                        if ('http://' . $correct_domain != $CFG->wwwroot) {
                            $url = 'http://' . $correct_domain . '/local/signin/index.php&username=' . $username;
                            redirect($url);
                        // Return/exit if the user is already on the right domain.
                        } else {
                            return;
                        }
                    // Return/exit if the user exists but has no brand association via a cohort.
                    } else {
                        return;
                    }
                // Error if username does not exist in the campus database.
                } else {
                    return array('username' => $this->lang_string('form_username_not_found'));
                }
            }
        }
    }

    /**
     * Return default domain for a given username (via cohort and brand).
     *
     * @param $username
     * @return mixed
     */
    public static function get_default_domain($username) {
        global $DB;
        return $DB->get_record_sql(static::DEFAULT_DOMAIN_FROM_USERNAME, array('username' => $username));
    }

    /**
     * Confirms whether a username exists in the database.
     *
     * @param $username
     * @return boolean
     */
    public static function user_exists($username) {
        global $DB;
        return $DB->record_exists('user', array('username' => $username));
    }
}
