<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\recovery_method;

use local_signin\util;

defined('MOODLE_INTERNAL') || die;

/**
 * Email-based password recovery.
 *
 * Locates an individual user by a valid email address, taking care to identiy
 * email address which have duplicate user accounts.
 */
class email_recovery_method extends abstract_recovery_method
        implements recovery_method {
    /**
     * SQL: locate user.
     *
     * @var string
     */
    const SQL_LOCATE_USER = <<<SQL
%s
AND mnethostid = :mnethostid
AND deleted    = 0
AND suspended  = 0
SQL;

    /**
     * @override \local_signin\recovery_method\recovery_method
     */
    public function get_name() {
        return 'email';
    }

    /**
     * @override \local_signin\recovery_method\recovery_method
     */
    public function locate_user($data) {
        global $CFG, $DB;

        $select = sprintf(
                static::SQL_LOCATE_USER,
                $DB->sql_like('email', ':email', false, true, false, '|'));
        $params = array(
            'email'      => $DB->sql_like_escape($data->email, '|'),
            'mnethostid' => $CFG->mnet_localhost_id,
        );

        return $DB->get_record_select(
                'user', $select, $params, '*', IGNORE_MULTIPLE);
    }

    /**
     * @override \local_signin\recovery_method\recovery_method
     */
    public function validate($data, $files) {
        global $CFG, $DB;

        $errors = array();

        if (!$data['email']) {
            $errors['email'] = get_string('email_required', util::MOODLE_COMPONENT);
        } elseif (!validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail');
        } elseif ($DB->count_records('user', array('email' => $data['email'])) > 1) {
            $errors['email'] = get_string('forgottenduplicate');
        } else {
            $user = get_complete_user_data('email', $data['email']);

            if ($user && !$user->confirmed) {
                $errors['email'] = get_string('confirmednot');
            }

            if (!$user && !$CFG->protectusernames) {
                $errors['email'] = get_string('emailnotfound');
            }
        }

        return $errors;
    }
}
