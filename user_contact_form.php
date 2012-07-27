<?php // $Id: user_contact_form.php,v 1.1 2011/02/08 18:47:29 arborrow Exp $

require_once('../../config.php');
require_once($CFG->libdir.'/formslib.php');

class block_user_contact_form extends moodleform {
    function definition() {
        global $CFG, $USER, $cid, $bid, $rcp, $allhiddenrecipients, $allstandardrecipients;

        $mform =& $this->_form;

        //From: http://docs.moodle.org/en/Development:Coding
        // Use 'sesskey' mechanism to protect form handling routines from attack.
        /* Basic example of use: when form is generated, include <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>" />. */
        // When you process the form check with if (!confirm_sesskey()) { print_error('confirmsesskeybad');}

        $debug = false;
        if ($debug) {
            echo '****** Written from line '.__LINE__.' of '.__FILE__.' ********<br />';
            echo '$cid = '.$cid.'<br />';
            echo '$bid = '.$bid.'<br />';
            echo '$rcp = '.$rcp.'<br />';
            if ($allhiddenrecipients) {
                $countallhiddenrecipients = count($allhiddenrecipients);
            } else {
                $countallhiddenrecipients = 0;
            }
            echo 'count($allhiddenrecipients) = '.$countallhiddenrecipients.'<br />';

            if ($allstandardrecipients) {
                $countallstandardrecipients = count($allstandardrecipients);
            } else {
                $countallstandardrecipients = 0;
            }
            echo 'count($allstandardrecipients) = '.$countallstandardrecipients;
        }
        // what am I supposed to do if a guy is hiddenrecipient and standardrecipient too?

        // no fieldset needed
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->addElement('hidden', 'cid', $cid);
        $mform->addElement('hidden', 'bid', $bid);
        $mform->addElement('hidden', 'rcp', $rcp);

        // fieldset sender
        $mform->addElement('header', 'sender', get_string('sender', 'block_user_contact'));
        if (isloggedin() && (!isguestuser($USER))) {
            // static_sendername
            $mform->addElement('static', 'static_sendername', get_string('name'),fullname($USER));
            $mform->addElement('hidden', 'cf_sendername', fullname($USER));

            // static_senderemail
            $mform->addElement('static', 'static_senderemail', get_string('email'),$USER->email);
            $mform->addElement('hidden', 'cf_senderemail', $USER->email);
            $mform->addElement('hidden', 'cf_sendermailformat', $USER->mailformat);
        } else {
            // cf_opensendername
            $mform->addElement('text', 'cf_sendername', get_string('name'));
            $mform->addRule('cf_sendername', get_string('missingremoteusername','block_user_contact'), 'required', null, 'client');

            // cf_opensenderemail
            $mform->addElement('text', 'cf_senderemail', get_string('email'));
            $mform->addRule('cf_senderemail', get_string('missingremoteuseremail','block_user_contact'), 'required', null, 'client');
            $mform->addElement('hidden', 'cf_sendermailformat', '1');
        }


        // no fieldset needed
        if ($allhiddenrecipients) {
            foreach ($allhiddenrecipients as $recipient) {
                $mform->addElement('hidden', 'cf_teacher'.$recipient->id, '1');
                $mform->addElement('hidden', 'cf_emailteacher'.$recipient->id, $recipient->email);
            }
        }


        // fieldset recipients
        if ($allstandardrecipients) {
            $mform->addElement('header', 'recipients', get_string('recipients', 'block_user_contact'));
            foreach ($allstandardrecipients as $recipient) {
                $mform->addElement('checkbox', 'cf_teacher'.$recipient->id, fullname($recipient));
                $mform->addElement('hidden', 'cf_emailteacher'.$recipient->id, $recipient->email);
            }
            if (count($allstandardrecipients) == 1) {
                $mform->setDefault('cf_teacher'.$recipient->id, '1');
            }
        }


        // fieldset email
        $mform->addElement('header', 'email', get_string('email', 'block_user_contact'));

        // cf_mailsubject
        $mform->addElement('text', 'cf_mailsubject', get_string('mailsubject','block_user_contact'));
        $mform->addRule('cf_mailsubject', get_string('missingmailsubject','block_user_contact'), 'required', null, 'client');

       // cf_mailbody
       $textfieldoptions = array('trusttext'=>false, 'subdirs'=>true, 'maxfiles'=>3, 'maxbytes'=>1024);
       $mform->addElement('editor', 'cf_mailbody', get_string('mailbody','block_user_contact'), null, $textfieldoptions);
       $mform->setType('cf_mailbody', PARAM_RAW);
       $mform->addRule('cf_mailbody', get_string('missingmailbody','block_user_contact'), 'required', null, 'client');
       // $mform->addHelpButton('cf_mailbody', 'userdescription');
       $mform->addHelpButton('cf_mailbody', 'emailbody', 'block_user_contact');

        // recaptcha
        if (user_contact_recaptchaenabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('recaptcha', 'auth'), array('https' => $CFG->loginhttps));
            $mform->setHelpButton('recaptcha_element', array('recaptcha', get_string('recaptcha', 'auth')));
            $mform->addRule('recaptcha_element', get_string('missingrecaptcha','block_user_contact'), 'required', null, 'client');
        }

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons(true, get_string('sendemail','block_user_contact'));
    }

    function definition_after_data() {
    }

    function validation($data) {
        global $allhiddenrecipients, $allstandardrecipients;
//print_object($data);

        $errors = array();
        if (!confirm_sesskey()) {
            print_error('confirmsesskeybad');
        }

        if (!isloggedin()) {
            if (! validate_email($data['cf_senderemail'])) {
                $errors['cf_senderemail'] = get_string('invalidemail');
            }
        }

        if ($allhiddenrecipients) {
            $offset = count($allhiddenrecipients);
        } else {
            $offset = 0;
        }
        // if there are not $allstandardrecipients (alias !isset($allstandardrecipients)) there is nothing to check
        // so all is right ($allright = true)
        $allright = empty($allstandardrecipients);
        if (!$allright) {
            $i = $offset;
            foreach ($allstandardrecipients as $recipient) {
                if ( isset($data['cf_teacher'.$recipient->id]) ) {
                    $allright = true;
                    continue;
                }
            }
        }

        if (!$allright) {
            foreach ($allstandardrecipients as $recipient) {
                $errors['cf_teacher'.$recipient->id] = get_string('missingrecipient','block_user_contact');
            }
        }

        // controllare il recaptcha
        if (user_contact_recaptchaenabled()) {
            $recaptcha_element = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['recaptcha_challenge_field'])) {
                $challenge_field = $this->_form->_submitValues['recaptcha_challenge_field'];
                $response_field = $this->_form->_submitValues['recaptcha_response_field'];
                if (true !== ($result = $recaptcha_element->verify($challenge_field, $response_field))) {
                    $errors['recaptcha_element'] = $result;
                }
            } else {
                $errors['recaptcha_element'] = get_string('missingrecaptchachallengefield');
            }
        }

        return $errors;
    }
}
?>