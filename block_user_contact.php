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
 * Contact form block
 *
 * @package    contrib
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_user_contact extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_user_contact');
    }

    function instance_allow_multiple() {
        return false;
    }

    function has_config() {
        return true;
    }

    function applicable_formats() {
        return array('all' => true);
        //return array('course' => true, 'site' => true);
    }

    function instance_allow_config() {
        return true;
    }

    function specialization() {
        if (isset($this->config->defaulttitle)) { // local defaultlabel requested
            $this->title = format_string(get_string('blocktitle','block_user_contact'));
        } else {
            if (isset($this->config->title)) { // local defaultlabel requested
                $this->title = format_string($this->config->title);
            } else {
                $this->title = format_string(get_string('blocktitle','block_user_contact'));
            }
        }
    }

    function get_content() {
        global $USER, $CFG, $OUTPUT;

        require_once($CFG->dirroot.'/blocks/user_contact/lib.php');

        if($this->content !== NULL) {
            return $this->content;
        }

        if (empty($this->instance)) {
            // We're being asked for content without an associated instance
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass;
        //$this->content->header = $this->title;
        //$this->content->text = ''; //empty to start, will be populated below
        //$this->content->footer = '';

        $cid = $this->page->course->id; // course id
        $bid = $this->instance->id;     // block  id

        $allhiddenrecipients   = user_contact_getallhiddenrecipients($cid, $bid);
        $allstandardrecipients = user_contact_getallstandardrecipients($bid);

        if ( !($allhiddenrecipients || $allstandardrecipients) ) {
            $this->content->text  = format_text(get_string('block_misconfigured', 'block_user_contact'));
            $this->content->footer = format_text(get_string('block_misconfigured_footer', 'block_user_contact'));
        } else {
            // a blocco appena creato, $this->config non esiste
            // print_object($this->config);

            if (isset($this->config)) {
                /////////////////////////////////////////////////
                // esistono dei settings locali
                /////////////////////////////////////////////////

                //set displaytype to stored value
                $displaytype = $this->config->displaytype;

                //set defaultlabel to stored value
                if (isset($this->config->defaultlabel)) { // local defaultlabel requested
                    $defaultlabel = format_string(get_string('defaultlabel','block_user_contact'));
                } else {
                    $defaultlabel = format_string($this->config->label);
                }
                //set receipt to stored value
                $receipt = $this->config->receipt;

                //set welcometext to stored value
                if (isset($this->config->defaultwelcometext)) { // local defaultwelcometext requested
                    $welcometext = format_text(get_string('welcometext','block_user_contact'));
                } else {
                    $welcometext = format_text($this->config->welcometext['text']);
                }
            } else {
                /////////////////////////////////////////////////
                // non esistono dei settings locali: set defaults
                /////////////////////////////////////////////////

                //set displaytype to its default
                $displaytype = 2; // deafult

                //set defaultlabel to its default
                $defaultlabel = format_string(get_string('defaultlabel','block_user_contact'));

                //set receipt to its default
                if (isset($CFG->block_user_contact_receipt)) {
                    $receipt = $CFG->block_user_contact_receipt;
                } else {
                    $receipt = 0; // deafult
                }

                $welcometext = format_text(get_string('welcometext','block_user_contact'));
            }

            $debug = false;
            if ($debug) {
                echo 'Scrivo dalla riga '.__LINE__.' del file '.__FILE__.'<br />';
                echo '$cid = '.$cid.'<br />';
                echo '$bid = '.$bid.'<br />';
                echo '$rcp = '.$receipt.'<br />';

                echo 'count($allhiddenrecipients) = '.count($allhiddenrecipients).'<br />';
                echo 'count($allstandardrecipients) = '.count($allstandardrecipients).'<br />';
            }

            //check our configuration setting to see what format we should display
            // 0 == display a form button
            // 1 == display a link
            $options = array('sesskey'=>sesskey());
            if ($cid) $options['cid'] = $cid;
            if ($bid) $options['bid'] = $bid;
            if ($receipt) $options['rcp'] = $receipt;
            $address = new moodle_url('/blocks/user_contact/user_contact.php', $options);

            $this->content->text = $OUTPUT->box($welcometext, 'info');

            if ($displaytype == 1){
                $this->content->text .= $OUTPUT->box('<a href="'.$address.'">'.$defaultlabel.'</a>', 'info centerpara');
            } else {
                $this->content->text .= $OUTPUT->single_button($address, $defaultlabel);
            }
        }

        return $this->content;
    }
}
