<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin;

use local_signin\recovery_method\email_recovery_method;
use local_signin\recovery_method\username_recovery_method;

defined('MOODLE_INTERNAL') || die;

class util {
    /**
     * Component name.
     *
     * @var string
     */
    const MOODLE_COMPONENT = 'local_signin';

    /**
     * Setting: forgot password lookup methods.
     *
     * @var string
     */
    const SETTING_FORGOT_METHODS = 'forgotmethods';

    /**
     * Setting: recovery status.
     *
     * @var string
     */
    const SETTING_STATUSES = 'statuses';

    /**
     * Recovery method class name format.
     *
     * @var string
     */
    const RECOVERY_METHOD_CLASS_FORMAT = '\local_signin\recovery_method\%s_recovery_method';

    const ELEMENT_WRAPPER = '<div class="%s"><a href="%s">%s</a></div>';

    /**
     * Recovery methods.
     *
     * @var \local_signin\recovery_method\recovery_method[]
     */
    protected static $RECOVERY_METHODS;

    /**
     * Recovery method names.
     *
     * @var string[]
     */
    protected static $RECOVERY_METHOD_NAMES = array(
        'email',
        'username',
    );

    /**
     * Initialise password recovery methods if not already prepared.
     *
     * @return void
     */
    protected static function maybe_init_password_recovery_methods() {
        if (static::$RECOVERY_METHODS) {
            return;
        }

        static::$RECOVERY_METHODS = array();
        foreach (static::$RECOVERY_METHOD_NAMES as $name) {
            $class = sprintf(static::RECOVERY_METHOD_CLASS_FORMAT, $name);
            static::$RECOVERY_METHODS[$name] = new $class();
        }
    }

    /**
     * Obtain the named password recovery method.
     *
     * @param string $name
     *
     * @return \local_signin\recovery_method\recovery_method
     */
    public static function get_password_recovery_method($name) {
        static::maybe_init_password_recovery_methods();

        return static::$RECOVERY_METHODS[$name];
    }

    /**
     * Forgot password recovery methods.
     *
     * @return string[]
     */
    public static function get_password_recovery_methods() {
        static::maybe_init_password_recovery_methods();

        return array_map(function($method) {
            return $method->get_name();
        }, static::$RECOVERY_METHODS);
    }

    public static function lang_string($id) {
        return get_string($id, util::MOODLE_COMPONENT);
    }
}
