<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

namespace local_signin\admin;

use admin_setting;
use html_writer;

defined('MOODLE_INTERNAL') || die;

require_once "{$CFG->libdir}/adminlib.php";

/**
 * Static HTML for settings pages.
 */
class setting_static extends admin_setting {
    /**
     * Content.
     *
     * @var string
     */
    protected $content;

    /**
     * @override \admin_setting
     */
    public function __construct($name, $visiblename, $content, $defaultsetting=null) {
        parent::__construct($name, $visiblename, null, null);

        $this->content = $content;
    }

    /**
     * @override \admin_setting
     */
    public function get_setting() {}

    /**
     * @override \admin_setting
     */
    public function output_html($data, $query='') {
        return html_writer::tag('h3', $this->visiblename)
             . $this->content;
    }

    /**
     * @override \admin_setting
     */
    public function write_setting($data) {}
}
