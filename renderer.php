<?php
/**
 * Etc
 *
 */
defined('MOODLE_INTERNAL') || die;

use local_signin\form\password_form;
use local_signin\form\username_form;

class local_signin_renderer extends plugin_renderer_base {
    public function modal_dialogue_requirements() {
        $this->page->requires->js_call_amd(
            'local_signin/lightbox', 'init');
    }
    public function modal_dialogue_content() {
        $username_form = new username_form();
        $password_form = new password_form(new moodle_url('/local/signin/index.php'));
        $templatecontext = (object) array(
            'username_form' => $username_form->render(),
            'password_form' => $password_form->render(),
        );
        return $this->render_from_template(
            'local_signin/lightbox', $templatecontext);
    }
}
