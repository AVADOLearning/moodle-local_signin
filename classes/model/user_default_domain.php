<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\model;

use dml_missing_record_exception;
use local_signin\interfaces\static_default_domain;
use local_signin\interfaces\user_domain_interface;

defined('MOODLE_INTERNAL') || die;

class user_default_domain {
    public $username;
    public $email;
    public $domain;

    public function __construct($username=null, $email=null, $domain=null) {
        $this->username = $username;
        $this->email    = $email;
        $this->domain   = $domain;
    }

    public static function get($input) {
        global $CFG, $DB;

        $result = new user_default_domain(
            null,
            null,
            parse_url($CFG->wwwroot, PHP_URL_HOST)
        );

        $class = property_exists($CFG, 'local_signin_userdomain') ?
            $CFG->local_signin_userdomain :
            static_default_domain::class;
        /** @var user_domain_interface $domain_interface */
        $domain_interface = new $class();

        $where =  "username = :username";
        $params = array('username' => $input);
        if ($CFG->authloginviaemail) {
            $where .= " OR email = :email";
            $params['email'] = $input;
        }

        try {
            $user = $DB->get_record_select('user', $where, $params, '*', MUST_EXIST);
            $result->username = $user->username;
            $result->email = $user->email;
            $result->domain = $domain_interface->get_user_domain($user);
        } catch (dml_missing_record_exception $e) {
            // Do nothing.
        }
        return $result;
    }
}
