<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\helper;

use context_system;
use core_user;
use local_signin\util;
use moodle_url;

defined('MOODLE_INTERNAL') || die;

require_once "{$CFG->dirroot}/login/lib.php";

/**
 * Password recovery helper.
 *
 * Password recovery in Moodle is a three stage process:
 *
 * 1. Send an email containing a recovery token to the user's registered email
 *    address, validating that the person triggering the request has access to
 *    the registered user's mailbox.
 * 2. When the user supplies the token, ensure the request is within its
 *    lifetime and allow the user to alter their password via a form.
 * 3. Apply the password change, validating the token's validity once again.
 */
class recovery_helper {
    /**
     * Recovery status: no recovery email sent.
     *
     * A matching user could not be found, so no email was sent. This is not a
     * technical failure, but rather indicates user error.
     *
     * @var integer
     */
    const STATUS_NONE_SENT = 0;

    /**
     * Recovery status: directions sent.
     *
     * A matching user was found, but the user was either not permitted to
     * change their own password or doing so is not supported by their
     * authentication method.
     *
     * @var integer
     */
    const STATUS_DIRECTIONS_SENT = 1;

    /**
     * Recovery status: token sent.
     *
     * A matching user was found, and a password reset request sent. This means
     * either a new reset request was created or an existing one was resent.
     *
     * @var integer
     */
    const STATUS_TOKEN_SENT = 2;

    /**
     * Recovery status: already sent.
     *
     * @var integer
     */
    const STATUS_ALREADY_SENT = 3;

    /**
     * Recovery status: error.
     *
     * This status is indicative of a technical failure sending an email.
     *
     * @var integer
     */
    const STATUS_ERROR = 4;

    /**
     * Recovery status: no reset request with a matching token found.
     *
     * @var integer
     */
    const STATUS_NO_RESET_RECORD = 5;

    /**
     * Recovery status: reset record exists, but has expired.
     *
     * @var integer
     */
    const STATUS_RESET_RECORD_EXPIRED = 6;

    /**
     * Recovery status: reset is forbidden.
     *
     * The user's authentication method is disabled or the user has the
     * "nologin" authentication method.
     *
     * @var integer
     */
    const STATUS_FORBIDDEN = 7;

    /**
     * Recovery status: attempting to change the guest user's password.
     *
     * @var integer
     */
    const STATUS_IS_GUEST_USER = 8;

    /**
     * Recovery status: reset token is valid.
     *
     * @var integer
     */
    const STATUS_VALID_TOKEN = 9;

    /**
     * Recovery status: an error occurred updating the user's password.
     *
     * @var integer
     */
    const STATUS_ERROR_UPDATING_PASSWORD = 10;

    /**
     * Recovery status: password successfully reset.
     *
     * @var integer
     */
    const STATUS_PASSWORD_RESET = 11;

    /**
     * SQL query: retrieve a reset record with the specified token.
     *
     * @var string
     */
    const SQL_RESET_RECORD_BY_TOKEN = <<<SQL
SELECT
    u.*,
    upr.token         AS resettoken,
    upr.timerequested AS resettimerequested,
    upr.id            AS resetid
FROM {user} u
INNER JOIN {user_password_resets} upr
    ON upr.userid = u.id
WHERE upr.token = ?
SQL;

    /**
     * Begin recovery of a user's password.
     *
     * This is the first of two stages of the recovery process, where an email
     * containing a token is sent to the user. Supplying us with the token in
     * the second stage allows us to validate the user's access to their inbox.
     *
     * @param \stdClass|null $user
     *
     * @return integer One of the STATUS_* constants.
     */
    public static function begin_recovery($user) {
        global $DB;

        $context = context_system::instance();

        if (!$user || !$user->confirmed) {
            // No matching user, or user hasn't confirmed email address
            return static::STATUS_NONE_SENT;
        }

        $auth = get_auth_plugin($user->auth);
        if (!$auth->can_reset_password() || !is_enabled_auth($user->auth)
                || !has_capability('moodle/user:changeownpassword', $context, $user->id)) {
            // User isn't able to change their own password
            if (send_password_change_info($user)) {
                return static::STATUS_DIRECTIONS_SENT;
            } else {
                return static::STATUS_ERROR;
            }
        } else {
            // We can send a reset email, but we need to ensure there isn't one
            // already in progress.
            $cutoff          = time() - static::get_reset_timeout();
            $resetinprogress = $DB->get_record(
                    'user_password_resets', array('userid' => $user->id));

            if ($resetinprogress && $resetinprogress->timerequested < $cutoff) {
                // An existing password reset requests exists, but has expired.
                // This is unlikely to be hit in production as expired requests
                // are cleaned up by the cron.
                $DB->delete_records(
                        'user_password_resets',
                        array('id' => $resetinprogress->id));
                $resetinprogress = null;
            }

            if (!$resetinprogress) {
                // No existing reset in progress
                $resetrecord = core_login_generate_password_reset($user);
            } elseif (!$resetinprogress->timererequested) {
                // Existing, valid request, and the first time the user has
                // re-requested.
                $resetinprogress->timererequested = time();
                $DB->update_record('user_password_resets', $resetinprogress);

                $resetrecord = $resetinprogress;
            } else {
                // Already sent
                return static::STATUS_ALREADY_SENT;
            }

            return static::send_token_email($user, $resetrecord)
                    ? static::STATUS_TOKEN_SENT : static::STATUS_ERROR;
        }
    }

    /**
     * Send a password reset token to a user.
     *
     * @param \stdClass $user        Record from the user table.
     * @param \stdClass $resetrecord Record from the user_password_resets table.
     *
     * @return boolean
     */
    protected static function send_token_email($user, $resetrecord) {
        global $CFG;

        $site        = get_site();
        $supportuser = core_user::get_support_user();

        $resettimeout     = static::get_reset_timeout();
        $resettimeoutmins = $resettimeout / MINSECS;

        $link = static::get_reset_url();
        $link->param('token', $resetrecord->token);
        $link = $CFG->httpswwwroot . $link->out_as_local_url(false);

        $data = (object) array(
            'firstname'    => $user->firstname,
            'lastname'     => $user->lastname,
            'username'     => $user->username,
            'sitename'     => format_string($site->fullname),
            'link'         => $link,
            'admin'        => generate_email_signoff(),
            'resetminutes' => $resettimeoutmins,
        );

        $message = get_string('emailresetconfirmation', '', $data);
        $subject = get_string('emailresetconfirmationsubject', '', format_string($site->fullname));

        return email_to_user($user, $supportuser, $subject, $message);
    }

    /**
     * Validate that the specified token is valid.
     *
     * @param string $token
     *
     * @return integer One of the STATUS_* constants.
     */
    public static function validate_token($token) {
        global $CFG;

        $now           = time();
        $resettimeout  = static::get_reset_timeout();
        $cutoff        = $now - $resettimeout;
        $ancientcutoff = $cutoff - DAYSECS;

        $reset = static::get_reset_info($token);

        if (!$reset || $reset->resettimerequested < $ancientcutoff) {
            // No (recent) reset record.
            return static::STATUS_NO_RESET_RECORD;
        }

        if ($reset->resettimerequested < $cutoff) {
            // Reset record exists, but has expired.
            return static::STATUS_RESET_RECORD_EXPIRED;
        }

        if ($reset->auth === 'nologin' || !is_enabled_auth($reset->auth)) {
            // User can't log in, so password resets are forbidden.
            return static::STATUS_FORBIDDEN;
        }

        if (isguestuser($reset)) {
            return static::STATUS_IS_GUEST_USER;
        }

        // We cleared security -- time to show the user the password change
        // form.
        return static::STATUS_VALID_TOKEN;
    }

    /**
     * Get the user record and user password reset information.
     *
     * @param string $token
     *
     * @return \stdClass A DML record containing all of the user fields and the
     *                   reset request information
     *                   (reset{token,timerequested,resetid}).
     */
    public static function get_reset_info($token) {
        global $DB;

        return $DB->get_record_sql(
                static::SQL_RESET_RECORD_BY_TOKEN, array($token));
    }

    /**
     * Get the URL of the reset process.
     *
     * @param mixed[] $params Optional GET parameters.
     *
     * @return \moodle_url
     */
    public static function get_reset_url($params=null) {
        return new moodle_url('/local/signin/forgot.php', $params);
    }

    /**
     * Get the flash message for the given status.
     *
     * @param $status
     *
     * @return null
     */
    public static function get_flash_message($status) {
        switch ($status) {
            case static::STATUS_NONE_SENT:
                return get_string('emailpasswordconfirmnotsent');
            case static::STATUS_NO_RESET_RECORD:
                return get_string('noresetrecord');
            case static::STATUS_RESET_RECORD_EXPIRED:
                $resettimeoutmins = recovery_helper::get_reset_timeout() / MINSECS;
                return get_string('resetrecordexpired', null, $resettimeoutmins);

            case static::STATUS_ALREADY_SENT:
                return get_string('emailalreadysent');

            case static::STATUS_DIRECTIONS_SENT:
            case static::STATUS_TOKEN_SENT:
                return get_string('emailpasswordconfirmsent', util::MOODLE_COMPONENT);

            default:
                return null;
        }
    }

    /**
     * Is the specified status considered successful or not?
     *
     * Should be used when outputting status information to users with
     * {@link get_flash_message()} and {@link core_renderer::notification()}.
     *
     * @param string $status
     *
     * @return boolean
     */
    public static function is_successful($status) {
        return in_array($status, array(
            static::STATUS_DIRECTIONS_SENT,
            static::STATUS_TOKEN_SENT,
            static::STATUS_VALID_TOKEN,
            static::STATUS_PASSWORD_RESET,
        ));
    }

    /**
     * Get the reset timeout.
     *
     * Unfortunately, we can't rely on the platorm to have a sane default for
     * critical values, so we'll handle it being set to falsy values ourselves.
     *
     * @return integer
     */
    public static function get_reset_timeout() {
        global $CFG;

        return $CFG->pwresettime ? $CFG->pwresettime : 1800;
    }

    /**
     * @param $reset
     * @param $password
     * @return mixed
     * @throws \coding_exception
     */
    public static function set_password($reset, $password) {
        global $DB;

        $DB->delete_records(
                'user_password_resets', array('id' => $reset->resetid));

        /** @var \auth_plugin_base $auth */
        $auth = get_auth_plugin($reset->auth);
        if (!$auth->user_update_password($reset, $password)) {
            return static::STATUS_ERROR_UPDATING_PASSWORD;
        }

        login_unlock_account($reset);

        unset_user_preference('auth_forcepasswordchange', $reset);
        unset_user_preference('create_password',          $reset);

        return static::STATUS_PASSWORD_RESET;
    }
}
