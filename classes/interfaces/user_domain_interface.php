<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\interfaces;

defined('MOODLE_INTERNAL') || die;

interface user_domain_interface {
    /**
     *
     *
     * @param \stdClass $user
     * @return string
     */
    public function get_user_domain($user);
}
