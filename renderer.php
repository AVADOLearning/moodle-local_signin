<?php
/**
 * Etc
 *
 */
defined('MOODLE_INTERNAL') || die;

use local_signin\form\password_form;
use local_signin\form\username_form;
use local_signin\util;

class local_signin_renderer extends plugin_renderer_base {
    public function modal_dialogue_requirements() {
        global $PAGE;

        $module = version_compare(moodle_major_version(), '3.2', '>=') ? 'modalfactory' : 'yui';

        $PAGE->requires->js_call_amd(
            sprintf('%s/%s_form-lazy', util::MOODLE_COMPONENT, $module), 'init',
            array(array(
                'title' => 'Sign In',
                'body' => $this->modal_dialogue_content(),
                'triggerSignin' => '#btn-login',
                'triggerUsernameSubmit' => '#id_submitusername',
                'triggerPasswordSubmit' => '#id_submitpassword'
            )));
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
