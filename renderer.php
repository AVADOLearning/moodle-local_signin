<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

use local_signin\util;

defined('MOODLE_INTERNAL') || die;

/**
 * Enhanced authentication renderer.
 */
class local_signin_renderer extends plugin_renderer_base {
    /**
     * Render the login form.
     *
     * @return string
     */
    public function login_form() {
        $context = (object) array(
            'action'  => new moodle_url('/local/signin/login.php'),
            'sesskey' => sesskey(),
        );

        return $this->render_from_template(
                util::MOODLE_COMPONENT . '/' . util::TEMPLATE_LOGIN_FORM,
                $context);
    }
}
