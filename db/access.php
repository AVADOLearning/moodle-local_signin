<?php

/**
 * Local Signin
 *
 * @package local_signin
 *
 * @author Ajay Viswambharan <ajay.viswambharan@avadolearning.com>
 * @copyright Avado Learning Limited 2019
 */

defined('MOODLE_INTERNAL') || die;

$capabilities = array(
    'local/signin:manage' => array(
        'riskbitmask'   => RISK_DATALOSS,
        'captype'       => 'manage',
        'contextlevel'  => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
    )
);
