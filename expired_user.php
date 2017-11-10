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

require_once dirname(dirname(__DIR__)) . '/config.php';

$helper = new \local_signin\helper\login_helper();
list($is, $days, $warning, $url) = $helper->get_user_expiration_information();
if (!$is) {
    redirect(get_login_url());
}
$returnurl = $helper->get_test_session_url();

if (intval($days) > 0 && intval($days) < intval($warning)) {
    echo $OUTPUT->header();
    echo $OUTPUT->confirm(get_string('auth_passwordwillexpire', 'auth', $days), $url, $returnurl);
    echo $OUTPUT->footer();
} elseif (intval($days) < 0 ) {
    echo $OUTPUT->header();
    echo $OUTPUT->confirm(get_string('auth_passwordisexpired', 'auth'), $url, $returnurl);
    echo $OUTPUT->footer();
}
