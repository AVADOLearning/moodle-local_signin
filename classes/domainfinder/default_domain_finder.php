<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\domainfinder;

defined('MOODLE_INTERNAL') || die;

/**
 * Default domain finder.
 *
 * Given a user object, determine the appropriate domain through which they
 * should authenticate.
 */
interface default_domain_finder {
    /**
     * Get the user's domain.
     *
     * @param \stdClass $user
     *
     * @return string The default domain.
     *
     * @throws \Exception On failure, in which case the default should be
     *                    assumed.
     */
    public function get_user_domain($user);
}
