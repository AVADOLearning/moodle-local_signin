<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

defined('MOODLE_INTERNAL') || die;

// Component metadata
$string['pluginname'] = 'Enhanced authentication';

// Administrative settings
$string['statuses']              = 'Recovery statuses';
$string['statuses_desc']         = '
<p>A range of statuses are used within the enhanced authentication plugin to track the status of a user\'s request. These status identifiers are exposed in the log report.</p>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Constant</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code>0</code></td>
            <td><code>STATUS_NONE_SENT</code></td>
            <td>A matching user could not be found, so no email was sent. This is not a technical failure, but rather indicates user error.</td>
        </tr>
        <tr>
            <td><code>1</code></td>
            <td><code>STATUS_DIRECTIONS_SENT</code></td>
            <td>A matching user was found, but the user was either not permitted to change their own password or doing so is not supported by their authentication method.</td>
        </tr>
        <tr>
            <td><code>2</code></td>
            <td><code>STATUS_TOKEN_SENT</code></td>
            <td>A matching user was foumd, and a password reset request sent. This means either a new reset request was created or an existing one was resent.</td>
        </tr>
        <tr>
            <td><code>3</code></td>
            <td><code>STATUS_ALREADY_SENT</code></td>
            <td>A password reset request had already been sent and resent. Since it has not yet expired, no action was taken.</td>
        </tr>
        <tr>
            <td><code>4</code></td>
            <td><code>STATUS_ERROR</code></td>
            <td>This status is indicative of a technical failure sending an email.</td>
        </tr>
        <tr>
            <td><code>5</code></td>
            <td><code>STATUS_NO_RESET_RECORD</code></td>
            <td>No reset request with a matching reset token could be found. This is likely an indication that the user either incorrectly copied the URL from the email into their browser, or that the line in the email was somehow truncated. It could also mean that the token had been used or had expired and been removed from the password reset requests table before the user attempted to use it.</td>
        </tr>
        <tr>
            <td><code>6</code></td>
            <td><code>STATUS_RESET_RECORD_EXPIRED</code></td>
            <td>The user has waited too long to act on the reset token email and the token had expired. The old token has been removed and the user must re-attempt the password reset from the beginning.</td>
        </tr>
        <tr>
            <td><code>7</code></td>
            <td><code>STATUS_FORBIDDEN</code></td>
            <td>The user\'s authentication method is disabled or the user has the "nologin" authentication method.</td>
        </tr>
        <tr>
            <td><code>8</code></td>
            <td><code>STATUS_IS_GUEST_USER</code></td>
            <td>It is not coherent/possible to change the guest user\'s password.</td>
        </tr>
        <tr>
            <td><code>9</code></td>
            <td><code>STATUS_VALID_TOKEN</code></td>
            <td>Used internally only; the user\'s password reset token was successfully matched to an active reset record.</td>
        </tr>
        <tr>
            <td><code>10</code></td>
            <td><code>STATUS_ERROR_UPDATING_PASSWORD</code></td>
            <td>An unknown error occurred in the user\'s authentication method attempting to reset the user\'s password.</td>
        </tr>
        <tr>
            <td><code>11</code></td>
            <td><code>STATUS_PASSWORD_RESET</code></td>
            <td>Whew, finally! Password successfully reset.</td>
        </tr>
    </tbody>
</table>';
$string['forgotmethods']         = 'Forgot password recovery methods';
$string['forgotmethods_desc']    = 'Determines which user criteria can be used to locate a Moodle user for password recovery.';
$string['forgotmethod_email']    = 'Email address';
$string['forgotmethod_username'] = 'Username';

// Flash messages
$string['emailpasswordconfirmsent'] = 'An email should have been sent to your registered email address, containing easy instructions to confirm and complete this password change. If you continue to have difficulty, please contact the site administrator.';

// Event descriptions
$string['event_password_reset_request_attempt']  = 'A password reset request for \'{$a->username}\' ({$a->userid}) was requested; with status \'{$a->status}\'.';
$string['event_password_reset_request_complete'] = 'A password reset request for \'{$a->username}\' ({$a->userid}) was completed; with status \'{$a->status}\'.';

// Username form settings
$string['form_username_label']             = 'Username';
$string['form_username_placeholder']       = 'Username';
$string['form_username_class']             = 'input-block-level';
$string['form_username_button_label']      = 'Proceed';
$string['form_username_button_class']      = 'waves-button-input';
$string['form_username_remusername_class'] = 'rememberpass';
$string['form_username_remusername_label'] = 'Remember username';
$string['form_username_not_provided']      = 'Please provide a user name.';
$string['form_username_not_found_valid']   = 'This username does not exist or it is not active.';

// Password form settings
$string['form_password_label']        = 'Password';
$string['form_password_placeholder']  = 'Password';
$string['form_password_class']        = 'input-block-level';
$string['form_password_button_label'] = 'Log In';
$string['form_password_button_class'] = 'waves-button-input';

// Wrong username settings
$string['form_password_changeusername_class'] = 'changeuser';
$string['form_password_changeusername_label'] = 'Change your username?';

// Forgotten username or password form settings
$string['form_userpass_forgot_class'] = 'forgotpass';
$string['form_username_forgot_label'] = 'Forgotten your username?';
$string['form_password_forgot_label'] = 'Forgotten your password?';
$string['email_required'] = 'An email address is required.';
$string['username_required'] = 'An username is required.';
$string['form_page_title'] = 'Forgotten username or password';

