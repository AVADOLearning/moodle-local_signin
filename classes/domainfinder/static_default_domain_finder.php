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
 * Static default domain finder.
 *
 * Fall back on parsing the domain from the active wwwroot.
 */
class static_default_domain_finder implements default_domain_finder {
    /**
     * @inheritdoc user_domain_interface
     */
    public function __construct(stdClass $user) {
    }

    /**
     * @inheritdoc user_domain_interface
     */
    public function allow_email_authentication() {
        global $CFG;
        return $CFG->authloginviaemail;
    }

    /**
     * @inheritdoc user_domain_interface
     */
    public function get_user_domain() {
        global $CFG;
        return parse_url($CFG->wwwroot, PHP_URL_HOST);
    }
}
