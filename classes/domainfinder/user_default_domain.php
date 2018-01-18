<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\domainfinder;

use dml_missing_record_exception;
use local_signin\interfaces\static_default_domain;
use local_signin\interfaces\default_domain_finder;
use local_signin\util;

defined('MOODLE_INTERNAL') || die;

/**
 * User's default domain (value object).
 *
 * The notion of a default domain is useful in environments where users can sign
 * in to a platform accessible over multiple domains via a branded SSO service
 * or other portal. It allows Moodle to detect users attempting to login via the
 * wrong domain and correct it for them.
 */
class user_default_domain {
    /**
     * Username.
     *
     * @var string
     */
    public $username;

    /**
     * Email address.
     *
     * @var string
     */
    public $email;

    /**
     * Correct domain.
     *
     * @var string
     */
    public $domain;

    /**
     * Initialiser.
     *
     * @param string|null $username
     * @param string|null $email
     * @param string|null $domain
     */
    public function __construct($username=null, $email=null, $domain=null) {
        $this->username = $username;
        $this->email    = $email;
        $this->domain   = $domain;
    }

    /**
     * Resolve the correct domain for the user.
     *
     * @param string $input
     *
     * @return user_default_domain
     */
    public static function get($input) {
        global $CFG, $DB;

        $result = new user_default_domain(
            null,
            null,
            parse_url($CFG->wwwroot, PHP_URL_HOST)
        );

        // Assign a domain finder (either the one stored in $CFG, or the default one).
        $class = property_exists($CFG, 'local_signin_domainfinder')
                ? $CFG->local_signin_domainfinder
                : static_default_domain_finder::class;
        $domainfinder = (is_object($class)) ? $class : new $class();
        /** @var default_domain_finder $domainfinder */

        $where =  'LOWER(username) = LOWER(:username)';
        $params = array('username' => $input);
        if ($CFG->authloginviaemail) {
            $where .= " OR LOWER(email) = LOWER(:email)";
            $params['email'] = $input;
        }

        try {
            // If the username is found in the database, set up the result accordingly.
            $user = $DB->get_record_select(
                    'user', $where, $params, '*', MUST_EXIST);
            $result->username = $user->username;
            $result->email = $user->email;
            try {
                // If the user has a brand default domain (via a cohort), update $result accordingly.
                $result->domain = $domainfinder->get_user_domain($user);
            } catch (dml_missing_record_exception $e) {
                // No default, so give the "default" login domain.
                $result->domain = $CFG->local_signin_defaultdomain;
            }
        } catch (dml_missing_record_exception $e) {
            /* We didn't find a matching record -- allow the user to remain on
             * the current domain. */
        }

        return $result;
    }
}
