<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin;

use dml_missing_record_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use local_signin\interfaces\static_default_domain;
use local_signin\model\user_default_domain;

defined('MOODLE_INTERNAL') || die;

class external extends external_api {
    /**
     * Validates input (username/email). Runs automatically on webservice call - hence no direct call.
     *
     * @return external_function_parameters
     */
    public static function check_domain_parameters() {
        return new external_function_parameters(
            array(
                'input' => new external_value(PARAM_RAW, 'Username/email to be checked', VALUE_REQUIRED)
            )
        );
    }

    /**
     * Returns domain for given username.
     *
     * 1) If user does not exist, no redirect takes place.
     * 2) If user exists & does not have a cohort/brand, redirect to default root.
     * 3) If user exists & has brand, redirect to default brand domain.
     *
     * @param $input [string] user/email to be checked against the domain.
     * @return object
     */
    public static function check_domain($input) {
        return user_default_domain::get($input);
    }

    public static function check_domain_returns() {
        return new external_single_structure(
            array(
                'domain'   => new external_value(PARAM_URL, 'Domain for user associated brand'),
                'username' => new external_value(PARAM_USERNAME, 'Username'),
                'email'    => new external_value(PARAM_TEXT, 'User email'),
            )
        );
    }
}
