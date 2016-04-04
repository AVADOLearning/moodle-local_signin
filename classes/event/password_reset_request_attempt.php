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
 * Password reset attempted.
 *
 * A user has, either successfully or unsuccesfully, attempted to reset their
 * password.
 */
class password_reset_request_attempt extends base {
    /**
     * @override \core\event\base
     */
    protected function init() {
        $this->context = context_system::instance();

        $this->data['crud']     = 'r';
        $this->data['edulevel'] = static::LEVEL_OTHER;
    }
}
