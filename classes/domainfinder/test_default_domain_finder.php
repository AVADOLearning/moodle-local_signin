<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\domainfinder;

use dml_missing_record_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die;

/**
 * Test default domain finder.
 *
 * Used by the test suite to ensure that users are redirected when the domain
 * finder tells us they're in the wrong place.
 */
class test_default_domain_finder implements default_domain_finder {
    /**
     * User record.
     *
     * @var stdClass
     */
    protected $user;

    /**
     * @inheritdoc default_domain_finder
     */
    public function __construct(stdClass $user) {
        $this->user = $user;
    }

    /**
     * @inheritdoc user_domain_interface
     */
    public function allow_email_authentication() {
        global $CFG;
        return $CFG->authloginviaemail;
    }

    /**
     * @inheritdoc user_domain_interface
     */
    public function get_user_domain() {
        switch ($this->user->username) {
            case 'student2':
                return 'redirected.one';
            default:
                throw new dml_missing_record_exception('user');
        }
    }
}
