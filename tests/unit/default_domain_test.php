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

use bmext_signindomain\default_domain_finder;
use local_signin\domainfinder\user_default_domain;

defined('MOODLE_INTERNAL') || die();

global $CFG, $USER;
require_once($CFG->dirroot . '/auth/manual/auth.php');
require_once($CFG->dirroot . '/local/signin/classes/helper/login_helper.php'); // Include the code to test.

/**
 * Test case for local_signin.
 *
 * @group local_signin
 */
class local_signin_default_domain_testcase extends advanced_testcase {
    protected $user;

    public function setUp() {
        $generator = $this->getDataGenerator();
        $this->user = $generator->create_user();
    }

    // Not sure why this doesn't work -- method is invoked, but not counted
    // in verifyMockObjects() post-run. Needs analysis.
    //public function test_invokes_configured_domain_finder() {
    //    global $CFG;
    //
    //    $domainfinder = $this->createMock(default_domain_finder::class);
    //    $domainfinder->expects($this->once())
    //        ->method('get_user_domain')
    //        ->with(array($this->user->username))
    //        ->willReturn('example.com');
    //
    //    $this->resetAfterTest();
    //    $CFG->local_signin_domainfinder = $domainfinder;
    //
    //    user_default_domain::get($this->user->username);
    //}
}
