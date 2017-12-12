<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Enhanced authentication renderer.
 */
class local_signin_renderer extends plugin_renderer_base {
    /**
     * Render the login forms.
     *
     * @param $usernameform
     * @param $passwordform
     *
     * @return string
     */
    public function login($usernameform, $passwordform) {
        $context = (object) array(
            'username_form' => $usernameform->render(),
            'password_form' => $passwordform->render(),
        );
        return $this->render_from_template('local_signin/login', $context);
    }
}

