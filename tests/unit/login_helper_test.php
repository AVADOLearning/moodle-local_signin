<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests.
 *
 * @author Stefan Liute
 * @author Jonathan Shad
 *
 * @package local_signin
 * @category test
 * @copyright 2017 AVADO Learning Ltd.
 */

use local_signin\helper\login_helper;

defined('MOODLE_INTERNAL') || die();

global $CFG, $USER;
require_once($CFG->dirroot . '/auth/manual/auth.php');
require_once($CFG->dirroot . '/local/signin/classes/helper/login_helper.php'); // Include the code to test.

/**
 * Test case for local_signin.
 *
 * @group local_signin
 */
class local_signin_login_helper_testcase extends advanced_testcase {
    public function test_user_needs_to_change_their_password() {
        $this->resetAfterTest();

        // Set the global user.
        $this->setUser($this->getDataGenerator()->create_user());

        // Create a mock of an existing auth plugin.
        // Leave the original constructor in place, so we can assign config parameters.
        $authmock = $this->getMockBuilder(auth_plugin_manual::class)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            //->disallowMockingUnknownTypes() // Only works with Moodle 3.2 and newer.
            ->getMock();
        // Stub existing method so that all passwords are always expired.
        $authmock->method('password_expire')->willReturn(-1);
        // Assign config parameter 'expiration'.
        $authmock->config->expiration = 1;

        // Instantiate the tested plugin and have it use the mock auth plugin.
        $signinhelper = new login_helper(false);
        $signinhelper->use_auth_plugin($authmock);

        // Check that the plugin instance will correctly determine that a password is expired.
        $this->assertTrue($signinhelper->user_needs_to_change_their_password());
    }
}
