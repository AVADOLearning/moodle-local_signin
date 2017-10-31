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
 * Test case for keyinfo.
 *
 * @group filter_keyinfo
 */
class local_signin_login_helper_testcase extends advanced_testcase {
    public function test_user_needs_to_change_their_password() {
        $this->resetAfterTest();

        // Set the global user.
        global $USER;
        $user = $this->getDataGenerator()->create_user();
        $USER = $user;

        // Create a mock of an existing auth plugin.
        // Leave the original constructor in place, so we can assign config parameters.
        $mock_auth_plugin = $this->getMockBuilder(auth_plugin_manual::class)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        // Stub existing method so that all passwords are always expired.
        $mock_auth_plugin->method('password_expire')->willReturn(-1);
        // Assign config parameter 'expiration'.
        $mock_auth_plugin->config->expiration = 1;

        // Instantiate the tested plugin and have it use the mock auth plugin.
        $signin_helper = new login_helper(false);
        $signin_helper->use_auth_plugin($mock_auth_plugin);

        // Check that the plugin instance will correctly determine that a password is expired.
        $this->assertTrue($signin_helper->user_needs_to_change_their_password());
    }
}
