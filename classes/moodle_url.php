<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin;

defined('MOODLE_INTERNAL') || die;

class moodle_url extends \moodle_url {
    public function set_host($host) {
        $this->host = $host;
    }
}
