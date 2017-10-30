<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\recovery_method;

use core_text;
use local_signin\util;

defined('MOODLE_INTERNAL') || die;

/**
 * Username-based password recovery.
 *
 * Locates an individual user by a valid Moodle username.
 */
class username_recovery_method extends abstract_recovery_method
        implements recovery_method {
    /**
     * @override \local_signin\recovery_method\recovery_method
     */
    public function get_name() {
        return 'username';
    }

    /**
     * @override \local_signin\recovery_method\recovery_method
     */
    public function locate_user($data) {
        global $CFG, $DB;

        return $DB->get_record('user', $userparams = array(
            'username'   => core_text::strtolower($data->username),
            'mnethostid' => $CFG->mnet_localhost_id,
            'deleted'    => 0,
            'suspended'  => 0,
        ));
    }

    /**
     * @override \local_signin\recovery_method\recovery_method
     */
    public function validate($data, $files) {
        global $CFG;

        $errors = array();

        if (!$data['username']) {
            $errors['username'] = get_string('username_required', util::MOODLE_COMPONENT);
        } else {
            $user = get_complete_user_data('username', $data['username']);

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
