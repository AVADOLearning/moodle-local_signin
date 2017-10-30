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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/signin/classes/helper/login_helper.php'); // Include the code to test.

/**
 * Test case for keyinfo.
 *
 * @group filter_keyinfo
 */
class local_signin_password_expiration_testcase extends advanced_testcase {
    public function test_sample() {
        $this->assertEquals(0, 0);
    }
}
