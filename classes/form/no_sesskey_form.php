<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\form;

use moodleform;
use stdClass;

defined('MOODLE_INTERNAL') || die;
/** @var stdClass $CFG */

require_once "{$CFG->libdir}/formslib.php";

/**
 * Sesskey-less form.
 *
 * An ordinary Moodle form with sesskey validation disabled during submission
 * processing. Use of this class poses a potential security hazard (cross site
 * request forgery) when used for potentially destructive operations -- think
 * through your use case carefully.
 */
abstract class no_sesskey_form extends moodleform {
    /**
     * @inheritdoc moodleform
     */
    function _process_submission($method) {
        global $USER;

        $oldignoresesskey = property_exists($USER, 'ignoresesskey')
                && $USER->ignoresesskey;
        try {
            $USER->ignoresesskey = true;
            parent::_process_submission($method);
        } finally {
            $USER->ignoresesskey = $oldignoresesskey;
        }
    }
}
