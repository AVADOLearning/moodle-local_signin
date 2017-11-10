<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\interfaces;

use local_signin\model\user_default_domain;

defined('MOODLE_INTERNAL') || die;

class static_default_domain implements user_domain_interface {

    /**
     * @override user_domain_interface
     */
    public function get_user_domain($user) {
        global $CFG;
        return parse_url($CFG->wwwroot, PHP_URL_HOST);
    }
}
