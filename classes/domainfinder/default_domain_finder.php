<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\domainfinder;

use stdClass;

defined('MOODLE_INTERNAL') || die;

/**
 * Default domain finder.
 *
 * Given a user object, determine the appropriate domain through which they
 * should authenticate.
 */
interface default_domain_finder {
    /**
     * Initialiser.
     *
     * @param stdClass $user
     */
    public function __construct(stdClass $user);

    /**
     * Allow email authentication for this user?
     *
     * @return boolean
     */
    public function allow_email_authentication();

    /**
     * Get the user's domain.
     *
     * @return string The default domain.
     *
     * @throws \Exception On failure, in which case the default should be
     *                    assumed.
     */
    public function get_user_domain();
}
