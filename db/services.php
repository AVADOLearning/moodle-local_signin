<?php

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
 * Web service to check the correct domain for a user and redirect if needed.
 *
 * @package    local_signin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$functions = array(
    'local_signin_check_domain' => array( // local_PLUGINNAME_FUNCTIONNAME is the name of the web service function that the client will call.
        'classname'     => 'local_signin\external',
        'methodname'    => 'check_domain',
        'classpath'     => 'local/signin/classes/external.php',
        'description'   => 'Webservice API to check the correct domain for a user and redirect if needed.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => false,
    )
);

$services = array(
    'local_signin_check_domain_service' => array(
        'functions'        => array ('local_signin_check_domain'),
        'restrictedusers'  => 0,
        'enabled'          => 1, // if 0, then token linked to this service won't work
    )
);
