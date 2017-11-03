<?php

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/config.php';

use local_signin\form\password_form;
use local_signin\form\username_form;

global $CFG, $PAGE, $OUTPUT;

$context = context_system::instance();
$PAGE->set_url($CFG->httpswwwroot . "/local/signin/tests/resources/login_javascript.php");
$PAGE->set_context($context);
$PAGE->set_pagelayout('login');

echo $OUTPUT->header();

$username_form = new username_form();
$password_form = new password_form(new moodle_url('/local/signin/index.php'));

$templatecontext = new stdClass();
$templatecontext->username_form = $username_form->render();
$templatecontext->password_form = $password_form->render();

echo $OUTPUT->render_from_template('local_signin/login', $templatecontext);

echo $OUTPUT->footer();
