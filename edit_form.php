<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form for editing Blog tags block instances.
 *
 * @package   moodlecore
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing Blog tags block instances.
 *
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_user_contact_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG;

        // Fields for editing user_contact settings.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // config_title
        $a = get_string('blocktitle', 'block_user_contact');
        $objgroup = array();
        $objgroup[] =& $mform->createElement('text', 'config_title');
        $objgroup[] =& $mform->createElement('checkbox', 'config_defaulttitle', '', get_string('emptyfordefault', 'block_user_contact', $a));
        $mform->addGroup($objgroup, 'title_group', get_string('configtitle', 'block_user_contact'), ' ', false);
        $mform->disabledIf('title_group', 'config_defaulttitle', 'checked');
        $mform->setDefault('config_title', get_string('pluginname', 'block_user_contact'));
        $mform->setDefault('config_defaulttitle', 0);
        $mform->setType('config_title', PARAM_MULTILANG);

        // config_label
        $a = get_string('defaultlabel', 'block_user_contact');
        $objgroup = array();
        $objgroup[] =& $mform->createElement('text', 'config_label');
        $objgroup[] =& $mform->createElement('checkbox', 'config_defaultlabel', '', get_string('emptyfordefault', 'block_user_contact', $a));
        $mform->addGroup($objgroup, 'label_group', get_string('configlabel', 'block_user_contact'), ' ', false);
        $mform->disabledIf('label_group', 'config_defaultlabel', 'checked');
        $mform->setDefault('config_label', get_string('defaultlabel', 'block_user_contact'));
        $mform->setDefault('config_defaultlabel', 0);
        $mform->setType('config_label', PARAM_MULTILANG);

        // config_welcometext
        $a = get_string('welcometext', 'block_user_contact');
        $editoroptions = array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->block->context);
        $objgroup = array();
        $objgroup[] =& $mform->createElement('editor', 'config_welcometext', '', null, $editoroptions);
        $objgroup[] =& $mform->createElement('checkbox', 'config_defaultwelcometext', '', get_string('emptyfordefault', 'block_user_contact', $a));
        $mform->addGroup($objgroup, 'welcometext_group', get_string('configwelcometext', 'block_user_contact'), ' ', false);
        $mform->disabledIf('welcometext_group', 'config_defaultwelcometext', 'checked');
        $mform->setDefault('config_welcometext', get_string('welcometext', 'block_user_contact'));
        $mform->setDefault('config_defaultwelcometext', 0);
        $mform->setType('config_welcometext', PARAM_RAW);

        // config_displaytype
        $options = array();
        $options[] = get_string('displayasabutton', 'block_user_contact');
        $options[] = get_string('displayasalink', 'block_user_contact');
        $mform->addElement('select', 'config_displaytype', get_string('configdisplaytype', 'block_user_contact'), $options);
        $mform->setDefault('config_displaytype', get_string('welcometext','block_user_contact'));

        // config_receipt
        $options = array();
        $options[] = get_string('receipt_disable', 'block_user_contact');
        $options[] = get_string('receipt_enable', 'block_user_contact');
        $mform->addElement('select', 'config_receipt', get_string('configreceipt', 'block_user_contact'), $options);
        $default = (isset($CFG->block_user_contact_receipt)) ? $CFG->block_user_contact_receipt : 0;
        $mform->setDefault('config_receipt', $default);
    }

    function set_data($defaults) {
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $defaults->config_welcometext['text'] = $this->block->config->welcometext['text'];
            $defaults->config_welcometext['itemid'] = file_get_submitted_draft_itemid('config_welcometext');
            $defaults->config_welcometext['format'] = $this->block->config->welcometext['format'];
        } else {
            $defaults->config_welcometext['text'] = get_string('welcometext', 'block_user_contact');
            $defaults->config_welcometext['itemid'] = file_get_submitted_draft_itemid('config_welcometext');
            $defaults->config_welcometext['format'] = FORMAT_HTML;
        }
        // have to delete text here, otherwise parent::set_data will empty content of editor
        unset($this->block->config->text);

        //defaulttitle
        $defaults->config_defaulttitle = isset($this->block->config->defaulttitle) ? 1 : 0;
        unset($this->block->config->defaulttitle);

        //defaultlabel
        $defaults->config_defaultlabel = isset($this->block->config->defaultlabel) ? 1 : 0;
        unset($this->block->config->defaultlabel);

        //defaultwelcometext
        $defaults->config_defaultwelcometext = isset($this->block->config->defaultwelcometext) ? 1 : 0;
        unset($this->block->config->defaultwelcometext);

        parent::set_data($defaults);
        // restore $text
    }
}
