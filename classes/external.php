<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin;

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use local_signin\domainfinder\user_default_domain;

defined('MOODLE_INTERNAL') || die;

class external extends external_api {
    /**
     * Validates input (username/email).
     *
     * @return external_function_parameters
     */
    public static function check_domain_parameters() {
        return new external_function_parameters(array(
            'input' => new external_value(PARAM_RAW, 'Username/email to be checked', VALUE_REQUIRED)
        ));
    }

    /**
     * Obtain default domain for given username/email address.
     *
     * @param string $input Username/email address to lookup.
     *
     * @return user_default_domain
     */
    public static function check_domain($input) {
        return user_default_domain::get($input);
    }

    /**
     * Ensure valid return value.
     *
     * @return external_single_structure
     */
    public static function check_domain_returns() {
        return new external_single_structure(array(
            'domain'   => new external_value(PARAM_URL, 'Domain for user associated brand'),
            'username' => new external_value(PARAM_USERNAME, 'Username'),
            'email'    => new external_value(PARAM_TEXT, 'User email'),
        ));
    }
}
