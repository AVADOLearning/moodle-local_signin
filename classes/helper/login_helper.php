<?php

/**
 * Login helper
 *
 * @author Earle Skinner <earle.skinner@avadolearning.com>
 * @copyright 2017 AVADO Learning
 */

namespace local_signin\helper;

global $CFG;

use \core\notification;
use moodle_url;

require_once "{$CFG->dirroot}/login/lib.php";

class login_helper
{
    /**
     * List of available authentication plugins
     *
     * @var array
     */
    protected $authplugins;

    /**
     * The auth currently associated to the current user
     *
     * @var \stdClass
     */
    protected $currentauth;

    /**
     * Login helper constructor
     *
     * @param bool $load_auth_plugins
     */
    public function __construct($load_auth_plugins = true)
    {
        global $SESSION, $USER;

        // Clear any notifications
        if (!isset($SESSION->notifications)) {
            unset($SESSION->notifications);
        }

        if (!$load_auth_plugins) {
            return;
        }

        $auths = get_enabled_auth_plugins(true);
        foreach ($auths as $auth) {
            $this->authplugins[] = get_auth_plugin($auth);
            if (isset($USER) &&
                isset($USER->auth) &&
                $USER &&
                $USER->auth &&
                $auth === $USER->auth) {
                $this->currentauth = $auth;
            }
        }
    }

    /**
     * Sets a specific auth plugin. Only use this function for testing purposes.
     *
     * To use in unit testing, create new instance with 'login_helper(false)'
     * so no auth plugins are automatically loaded, then have your instance
     * '->use_auth_plugin($plugin_object)'.
     *
     * @param $plugin
     *
     * @return void
     */
    public function use_auth_plugin($plugin)
    {
        $this->authplugins[] = $plugin;
        $this->currentauth = $plugin;
    }

    /**
     * Have any notifications been raised?
     *
     * @return bool
     */
    public function has_notifications()
    {
        global $SESSION;
        return isset($SESSION->notifications) && count($SESSION->notifications) > 0;
    }

    /**
     * Add additional meta tags to the page header
     */
    public function additional_meta_tags()
    {
        global $CFG;

        // Try to prevent searching for sites that allow sign-up.
        if (!isset($CFG->additionalhtmlhead)) {
            $CFG->additionalhtmlhead = '';
        }
        $CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';
    }

    /**
     * If the cancel parameter is true. redirect to the home page
     */
    public function handle_cancel_request()
    {
        $cancel = optional_param('cancel', 0, PARAM_BOOL);
        if ($cancel) {
            // Redirect to frontpage, needed for loginhttps
            redirect(new moodle_url('/'));
        }
    }

    /**
     * If the test session parameter matches the current user, redirect to the url in the session
     * Tests if sessions are working correctly
     */
    public function handle_testsession_request()
    {
        global $SESSION, $USER;

        $testsession = optional_param('testsession', 0, PARAM_INT);

        // login page requested session test
        if ($testsession) {
            if ($testsession == $USER->id) {
                if (isset($SESSION->wantsurl)) {
                    $url = new moodle_url($SESSION->wantsurl);
                } else {
                    $url = new moodle_url('/');
                }
                unset($SESSION->wantsurl);
                redirect($url);
            } else {
                notification::error(get_string("cookiesnotenabled"));
            }
        }
    }

    /**
     * If the session has timed out, write a notification
     */
    public function handle_session_timeout()
    {
        global $SESSION;

        /// Check for timed out sessions
        if (!empty($SESSION->has_timed_out)) {
            unset($SESSION->has_timed_out);
            set_moodle_cookie('');
            notification::error(get_string('sessionerroruser', 'error'));
        }
    }

    /**
     * The login process uses global variables
     * Reset them
     */
    public function reset_auth_global_vars()
    {
        global $frm, $user;
        $frm = false;
        $user = false;
    }

    /**
     * Allow each auth plugin to bootstrap the login page
     */
    public function auth_plugin_bootstrapper()
    {
        if (!$this->authplugins) {
            return;
        }
        foreach ($this->authplugins as $authplugin) {
            $authplugin->loginpage_hook();
        }
    }

    /**
     * Has the global variables been populated by the bootstrapper
     *
     * @return bool
     */
    public function is_auth_global_vars_populated()
    {
        global $frm, $user;
        return ($user !== false || $frm !== false) && !$this->has_notifications();
    }

    /**
     * Does the user needs to reset their password?
     *
     * @return array(bool, string)
     */
    public function user_needs_to_restore_account()
    {
        global $frm, $user;
        if (!isset($user) &&
            isset($frm) &&
            isset($frm->username) &&
            is_restored_user($frm->username)) {
            return array(true, $frm->username);
        } else {
            return array(false, '');
        }

    }

    /**
     * Does the user need to confirm their account?
     * @param string $username
     * @return array(bool, string)
     * @throws \dml_exception
     */
    public function user_needs_to_confirm_account($username = '')
    {
        global $DB;
        $user = $DB->get_record('user', array('username' => $username));
        if (isset($user) && $user && !$user->confirmed) {
            return array(true, $user->email);
        } else {
            return array(false, '');
        }
    }

    /**
     * Authenticate the user
     *
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function authenticate()
    {
        global $CFG, $frm, $SESSION, $user;

        if ($this->has_notifications()) {
            $user = false;
            return false;
        }

        if ($frm && isset($frm->username)) {
            $frm->username = trim(\core_text::strtolower($frm->username));

            if (is_enabled_auth('none')) {
                if ($frm->username !== clean_param($frm->username, PARAM_USERNAME)) {
                    notification::error(sprintf('%s: %s',
                        get_string('username'),
                        get_string("invalidusername")));
                    $user = false;
                    return false;
                }
            }

            // Can't log in as guest if guest button is disabled
            if ($user && $frm->username == 'guest' && empty($CFG->guestloginbutton)) {
                $user = false;
                $frm = false;
            }

            // Set password for guest and authenticate them.
            // Only authenticate non-guests if they have a set and non-empty password.
            if ($frm->username == 'guest') {
                $frm->password = 'guest';
            }

            if (isset($frm->password) && $frm->password) {
                $user = authenticate_user_login($frm->username, $frm->password);
                if (!$user) {
                    notification::error(get_string("invalidlogin"));
                }
            }
        }

        if ($user) {
            // Language setup
            if (isguestuser($user)) {
                // no predefined language for guests - use existing session or default site lang
                unset($user->lang);
            } else {
                if (!empty($user->lang)) {
                    // unset previous session language - use user preference instead
                    unset($SESSION->lang);
                }
            }

            // If the user needs to be confirmed, exit early
            list($confirm, $email) = $this->user_needs_to_confirm_account();
            if ($confirm) {
                return false;
            }

            // Let's get them all set up.
            complete_user_login($user);
            unset_user_preference('login_failed_count_since_success', $user);
            return true;
        }

        return false;
    }

    /**
     * Set the remember cookie with the current user's username
     *
     */
    public function set_remember_me()
    {
        global $frm, $CFG, $USER;

        if (!empty($CFG->nolastloggedin)) {
            // do not store last logged in user in cookie
            // auth plugins can temporarily override this from loginpage_hook()
            // do not save $CFG->nolastloggedin in database!
            return;
        }
        if (isset($frm) && isset($frm->rememberme) && $frm->rememberme) {
            // sets the username cookie
            set_moodle_cookie($USER->username);
        } else {
            // no permanent cookies, delete old one if exists
            set_moodle_cookie('');
        }
    }

    /**
     * Check if the user's account is about to expire
     *
     * @return bool
     */
    public function user_needs_to_change_their_password()
    {
        global $USER;

        if (!$this->currentauth) {
            return false;
        }

        if (!empty($this->currentauth->config->expiration) and $this->currentauth->config->expiration == 1) {
            $days2expire = $this->currentauth->password_expire($USER->username);
            if (intval($days2expire) > 0 && intval($days2expire) < intval($this->currentauth->config->expiration_warning)) {
                return true;
            } elseif (intval($days2expire) < 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve all expiration information about the user
     *
     * @return array(bool, int, int, string)
     * @throws \moodle_exception
     */
    public function get_user_expiration_information()
    {
        global $USER;

        if (!isset($USER)) {
            return array(false, 0, 0, '');
        }

        if (!$this->currentauth ||
            empty($this->currentauth->config->expiration) ||
            $this->currentauth->config->expiration != 1) {
            return array(false, 0, 0, '');
        }
        $is = true;
        $days = intval($this->currentauth->password_expire($USER->username));
        $warning = intval($this->currentauth->config->expiration_warning);

        $url = new moodle_url('/');
        if ($this->currentauth->can_change_password()) {
            $passwordchangeurl = $this->currentauth->change_password_url();
            if (!$passwordchangeurl) {
                $url = new moodle_url('/login/change_password.php');
            }
        } else {
            $url = new moodle_url('/login/change_password.php');
        }

        return array($is, $days, $warning, $url);
    }

    /**
     * Provides the correct URL for login.
     *
     * @return mixed|string
     */
    public function get_login_url()
    {
        global $CFG;

        $url = "{$CFG->wwwroot}/local/signin/index.php";

        if (property_exists($CFG, 'loginhttps') && $CFG->loginhttps) {
            $url = str_replace('http:', 'https:', $url);
        }

        return $url;
    }

    /**
     * Determine the url to return the user to after they have logged in
     *
     * @return moodle_url
     * @throws \moodle_exception
     */
    public function get_test_session_url()
    {
        global $USER;

        return new moodle_url(
            $this->get_login_url(), array('testsession' => $USER->id));
    }

    /**
     * Set the return URL in the session if the parameter exists.
     *
     * @return void
     * @throws \coding_exception
     */
    public function set_wants_url()
    {
        global $SESSION;

        $url = optional_param('returnurl', '', PARAM_PATH);
        if ($url) {
            $SESSION->wantsurl = $url;
        }
    }

    /**
     * Determine where a user should be redirected after they have been logged in,
     * via function in login/lib.php.
     *
     * @return string
     */
    public function get_return_url()
    {
        return core_login_get_return_url();
    }

    /**
     * Redirect to the log out page
     */
    public function redirect_to_logout_page()
    {
        redirect(new moodle_url('/login/logout.php'));
    }

    /**
     * Create a new global form variable to save the login information
     */
    public function create_new_user_object()
    {
        global $frm;

        if (!isset($frm) || !is_object($frm)) {
            $frm = new \stdClass();
        }
    }

    /**
     * Set the username form parameters into the global frm variable
     *
     * @param \stdClass|null $data
     */
    public function set_userform_params_in_auth_global_vars($data = null)
    {
        global $frm;

        if (!$data) {
            return;
        }

        $frm->username = $data->username;
        $frm->rememberme = property_exists($data, 'rememberme') && $data->rememberme;
    }

    /**
     * Get the username form parameters from the global frm variable
     *
     * @return array(string, integer, url)
     */
    public function get_userform_params_from_auth_global_vars()
    {
        global $frm;

        if (isset($frm) && $frm && property_exists($frm, 'username') && $frm->username) {
            return array(
                $frm->username,
                property_exists($frm, 'rememberme') && $frm->rememberme,
            );
        }

        return array('', 0, '');
    }

    /**
     * Get the username from a querystring or a cookie.
     *
     * @return mixed|string
     * @throws \coding_exception
     */
    public function get_username_from_querystring_or_cookie()
    {
        if ($this->authplugins[0]->authtype !== 'shibboleth') {  // See bug 5184
            if (!empty($_GET["username"])) {
                return clean_param($_GET["username"], PARAM_RAW); // we do not want data from _POST here
            } else {
                return get_moodle_cookie();
            }
        }
        return '';
    }

    /**
     * Set the password form parameters into the global frm variable
     *
     * @param \stdClass|null $data
     */
    public function set_passform_params_in_auth_global_vars($data)
    {
        global $frm;

        if (!$data) {
            return;
        }

        $frm->username = $data->username;
        $frm->password = $data->password;
        $frm->rememberme = $data->rememberme;
    }

    /**
     * Verifies that the username is already set in global vars.
     *
     * @return bool
     */
    public function is_username_set_in_auth_global_vars()
    {
        global $frm;
        return isset($frm->username) && $frm->username && strlen($frm->username) > 0;
    }

    /**
     * Verifies that the user is already logged in.
     *
     * @return bool
     * @throws \coding_exception
     */
    public function is_user_already_loggedin()
    {
        return isloggedin() and !isguestuser();
    }

    /**
     * Clear the username from
     */
    public function clear_username_in_auth_global_vars()
    {
        global $frm;
        $frm = null;
        set_moodle_cookie('');
    }

    /**
     * Get user failed login count
     *
     * @param $userName
     * @return int|null
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_user_login_failCount($userName)
    {
        global $DB;
        $userTemp = $DB->get_record('user', ['username' => $userName]);
        return get_user_preferences('login_failed_count_since_success', null, $userTemp);
    }

    /**
     * Check whether helpdesk feature is enabled
     *
     * @return int/bool
     */
    public function is_helpdesk_enabled()
    {
        global $DB;
        try {
            return (int) $DB->get_field('helpdesk_settings', 'value', ['name' => 'showgethelp']);
        } catch (\dml_exception $e) {
            return false;
        }
    }
}
