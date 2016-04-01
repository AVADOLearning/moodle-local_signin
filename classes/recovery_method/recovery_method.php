<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\recovery_method;

defined('MOODLE_INTERNAL') || die;

/**
 * Password recovery method.
 *
 * Password recovery methods are a means of uniquely identifying a single Moodle
 * user account to enable recovery of a lost user account via a password reset.
 */
interface recovery_method {
    /**
     * Get the name of the password reset method.
     *
     * @return string e.g. "email", "username"
     */
    public function get_name();

    /**
     * Get the visible name of the password reset method.
     *
     * @return string e.g. "Email address", "Username"
     */
    public function get_visible_name();

    /**
     * Locate the matching user.
     *
     * @param \stdClass $data User-submitted form data.
     *
     * @return \stdClass
     */
    public function locate_user($data);

    /**
     * Validate user-supplied data from the form.
     *
     * @param mixed[] $data
     * @param mixed[] $files
     *
     * @return void
     */
    public function validate($data, $files);
}
