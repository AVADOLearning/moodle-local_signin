<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\recovery_method;

use local_signin\util;

defined('MOODLE_INTERNAL');

/**
 * Abstract password recovery method.
 *
 * This class provides utility methods for use within implementations.
 */
class abstract_recovery_method {
    /**
     * @override \local_signin\recovery_method\recovery_method
     */
    public function get_visible_name() {
        $name = $this->get_name();

        return get_string("forgotmethod_{$name}", util::MOODLE_COMPONENT);
    }
}
