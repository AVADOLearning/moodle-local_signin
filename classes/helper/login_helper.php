<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\helper;

use context_system;

defined('MOODLE_INTERNAL') || die;

/**
 * Login helper.
 */
class login_helper {
    /**
     * Login status: cookies disabled.
     *
     * Raised during session testing.
     */
    const STATUS_COOKIES_DISABLED = 1;

    /**
     * Perform early setup.
     *
     * @return void
     */
    public static function bootstrap_early() {
        global $CFG;

        // Try to prevent searching for sites that allow sign-up.
        if (!isset($CFG->additionalhtmlhead)) {
            $CFG->additionalhtmlhead = '';
        }
        $CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';

        redirect_if_major_upgrade_required();
    }

    /**
     * Bootstrap the page.
     * 
     */
    public static function bootstrap_page() {
        global $CFG, $PAGE;

        $PAGE->https_required();
        $PAGE->set_url($CFG->httpswwwroot . '/login/index.php');
        $PAGE->set_context(context_system::instance());
    }

    /**
     * Get the flash message for the given status.
     *
     * @param integer $status
     *
     * @return string
     */
    public static function get_flash_message($status) {
        switch ($status) {
            case static::STATUS_COOKIES_DISABLED:
                return get_string('cookiesnotenabled');

            default:
                return null;
        }
    }
}
