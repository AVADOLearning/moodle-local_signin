<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\event;

use context_system;
use core\event\base;

defined('MOODLE_INTERNAL') || die;

/**
 * Password reset attempt complete.
 *
 * Either successfully or unsuccessfully, the second stage of the password reset
 * process was completed.
 */
class password_reset_request_complete extends base {
    /**
     * @override \core\event\base
     */
    protected function init() {
        $this->context = context_system::instance();

        $this->data['crud']     = 'd';
        $this->data['edulevel'] = static::LEVEL_OTHER;

        $this->data['objecttable'] = 'user_password_resets';
    }
}
