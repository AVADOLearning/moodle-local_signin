<?php

/**
 * Enhanced authentication.
 *
 * @author Luke Carrier <luke.carrier@floream.com>
 * @copyright 2016 Floream Limited
 */

use local_signin\util;

defined('MOODLE_INTERNAL') || die;

/** @var admin_root $ADMIN */

 if ($ADMIN->fulltree) {
     $recoverymethods = util::get_password_recovery_methods();
     $choices = array_combine($recoverymethods, array_map(function($key) {
         return new lang_string("forgotmethod_{$key}", util::MOODLE_COMPONENT);
     }, $recoverymethods));
     $defaults = array_fill_keys($recoverymethods, true);

     $node = new admin_settingpage(
            util::MOODLE_COMPONENT,
            new lang_string('pluginname', util::MOODLE_COMPONENT));
     $ADMIN->add('authsettings', $node);

     $node->add(new admin_setting_configmulticheckbox(
             util::MOODLE_COMPONENT . '/' . util::SETTING_FORGOT_METHODS,
             new lang_string('forgotmethods',      util::MOODLE_COMPONENT),
             new lang_string('forgotmethods_desc', util::MOODLE_COMPONENT),
             $defaults, $choices));
 }
