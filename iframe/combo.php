<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

use local_signin\util;

require_once dirname(dirname(dirname(__DIR__))) . '/config.php';

/** @var \core_renderer $OUTPUT */
/** @var \moodle_page   $PAGE */

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('popup');

$PAGE->requires->js_call_amd(
        util::MOODLE_COMPONENT . '/' . util::AMD_IFRAME_SENDER, 'init');

/** @var \local_signin_renderer $renderer */
$renderer = $PAGE->get_renderer(util::MOODLE_COMPONENT);

echo
    $OUTPUT->header(),
    $renderer->login_form(),
    $OUTPUT->footer();
