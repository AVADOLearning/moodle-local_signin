<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

defined('MOODLE_INTERNAL') || die;

$plugin->component = 'local_signin';

$plugin->version = 2016040101;
$plugin->maturity = MATURITY_ALPHA;

$plugin->requires = 2014051200;
$plugin->dependencies = array('local_recoveraccount' => 2019052000); // Plugin depends on local recoveryaccount for unlocking users locked account
