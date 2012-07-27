<?php
global $SITE;
$settings->add(new admin_setting_configtext('block_user_contact_subject_prefix', get_string('subject_prefix', 'block_user_contact'),
                   get_string('subject_prefix_info', 'block_user_contact'), '['. strip_tags($SITE->shortname) .']',PARAM_RAW));

$settings->add(new admin_setting_configcheckbox('block_user_contact_receipt', get_string('receipt_enable', 'block_user_contact'),
                   get_string('receipt_info', 'block_user_contact'), 0));
?>