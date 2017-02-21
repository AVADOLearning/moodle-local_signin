<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

use local_signin\helper\login_helper;

require_once dirname(dirname(__DIR__)) . '/config.php';

/** @var \moodle_page $PAGE */

login_helper::bootstrap_early();

$testsession = optional_param('testsession', 0,     PARAM_INT);
$anchor      = optional_param('anchor',      '',    PARAM_RAW);
$cancel      = optional_param('cancel',      false, PARAM_BOOL);

login_helper::bootstrap_page();
