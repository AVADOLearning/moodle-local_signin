<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\domainfinder;

use dml_missing_record_exception;
use Exception;
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
class user_default_domain
{
    /**
     * User details.
     *
     * @var string
     */
    public $userdetail;

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
    public function __construct($userdetail = null, $domain = null)
    {
        $this->userdetail = $userdetail;
        $this->domain = $domain;
    }

    /**
     * Resolve the correct domain for the user.
     *
     * @param string $input
     *
     * @return user_default_domain
     */
    public static function get($input)
    {
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

        $where = 'LOWER(username) = LOWER(:username) OR LOWER(email) = LOWER(:email)';
        $params = array(
            'username' => $input,
            'email' => $input,
        );

        try {
            // If the username is found in the database, set up the result accordingly.
            $user = $DB->get_record_select(
                'user', $where, $params, '*', MUST_EXIST);

            /** @var default_domain_finder $domainfinder */
            $domainfinder = (is_object($class)) ? $class : new $class($user);

            if (strtolower(trim($user->username)) !== strtolower(trim($input)) &&
                !$domainfinder->allow_email_authentication()
            ) {
                return $result;
            }

            if (strpos($input, '@') > 0) {
                $result->userdetail = $user->email;
            } else {
                $result->userdetail = $user->username;
            }

            try {
                // If the user has a brand default domain (via a cohort), update $result accordingly.
                $result->domain = $domainfinder->get_user_domain();
            } catch (Exception $e) {
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
