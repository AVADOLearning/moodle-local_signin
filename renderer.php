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
     * @param moodleform|null $usernameform
     * @param moodleform      $passwordform
     *
     * @return string
     */
    public function login_form($usernameform, $passwordform) {
        $context = (object) array(
            'username_form' => $usernameform ? $usernameform->render() : null,
            'password_form' => $passwordform->render(),
        );
        return $this->render_from_template('local_signin/login_form', $context);
    }

    /**
     * Render the password form.
     *
     * @param moodleform $passwordform
     *
     * @return string
     */
    public function password_form($passwordform) {
        return $this->login_form(null, $passwordform);
    }

    /**
     * Render the forgot password form.
     *
     * @param moodleform $forgotform
     *
     * @return string
     */
    public function forgot_password_form($forgotform) {
        $context = (object) array(
            'forgot_form' => $forgotform->render(),
        );
        return $this->render_from_template(
                'local_signin/forgot_password_form', $context);
    }
}
