<?php
    if (!$site = get_site()) {
        redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
    }

    $debug = false;
    if ($debug) {
        echo 'Scrivo dalla riga '.__LINE__.' del file '.__FILE__.'<br />';
        echo '$cid = '.$cid.'<br />';
        echo '$bid = '.$bid.'<br />';
        echo '$rcp = '.$rcp.'<br />';

        echo 'count($allhiddenrecipients) = '.count($allhiddenrecipients).'<br />';
        echo 'count($allstandardrecipients) = '.count($allstandardrecipients).'<br />';
    }

    //print_object($fromform);

    // definition of $messagehtml
    $messagehtml = get_string('commentreceived', 'block_user_contact', $fromform->cf_sendername);
    // <strong>Name Surame</strong> sent you a comment from
    // <strong>Nome Cognome</strong> ti ha inviato una comunicazione

    $a = new object();
    $a->sitename = $site->shortname;
    if ($cid == SITEID) {
        $messagehtml .= get_string('fromhompageof', 'block_user_contact', $a).'<br />';
        // <strong>Name Surame</strong> sent you a comment from the <strong>home page</strong> of <strong>M19</strong>
        // <strong>Nome Cognome</strong> ti ha inviato una comunicazione dalla <strong>home page</strong> di <strong>M19</strong>
    } else {
        $a->coursename = $course->shortname;
        $messagehtml .= get_string('fromcourse', 'block_user_contact', $a).'<br />';
        // Name Surame sent you a comment from course <strong>xxx</strong> of <strong>M19</strong>
        // nome cognome ti ha inviato una comunicazione dal corso <strong>xxx</strong> di <strong>M19</strong>
    }

    $messagehtml .=  '<br />'.format_text($fromform->cf_mailbody['text'],$fromform->cf_mailbody['format']);
    //print_object($fromform);
    // end of definition of $messagehtml

    // definition of $messagetext
    $messagetext =  str_replace('<br />', "\n", $messagehtml);
    $messagetext =  strip_tags($messagetext);
    // end of definition of $messagetext

    if (false) {
        echo 'Scrivo dalla riga '.__LINE__.' del file '.__FILE__.'<br />';
        echo '$CFG->block_user_contact_subject_prefix = '.$CFG->block_user_contact_subject_prefix.'<br />'; // [M20]

        echo '<hr />';
        echo '$rcp = '.$rcp.'<br />';
        echo '<hr />';

        echo '<div>$messagetext = </div>';
        echo '<textarea cols="50" rows="7">';
        echo $messagetext;
        echo '</textarea>';

        echo '<div>$messagehtml = </div>';
        echo '<textarea cols="50" rows="7">';
        echo $messagehtml;
        echo '</textarea>';

        echo '<hr />';
        echo '<div>$messagetext = ';
        echo $messagetext;
        echo '</div>';
        echo '<div>$messagehtml = ';
        echo $messagehtml;
        echo '</div>';
        echo '<hr />';
        die;
    }

    //sender infos
    $from = new object;
    $from->firstname = $fromform->cf_sendername;
    $from->lastname = '';
    $from->email = $fromform->cf_senderemail;
    $from->maildisplay = true;
    $from->mailformat  = $fromform->cf_sendermailformat;

    //define the subject starting from the pre-defined prefix
    if ($cid == SITEID) {
        // as far as I understand, the next if is useless because
        // it was defined a default for $CFG->block_user_contact_subject_prefix
        if (!isset($CFG->block_user_contact_subject_prefix)) {
            $CFG->block_user_contact_subject_prefix = '['.strip_tags($site->shortname).'] ';
        }
        $subject = $CFG->block_user_contact_subject_prefix.$fromform->cf_mailsubject;
    } else {
        //set the subject to start with [shortname]
        $subject = '['.$course->shortname.'] '.$fromform->cf_mailsubject;
    }


if (!$debug) {
    //send emails
    $fullnamesender = fullname($from);
    if ($allhiddenrecipients) {
        foreach ($allhiddenrecipients as $thisrecipient) {
            $fullnamerecipient = fullname($thisrecipient);
            $property = 'cf_teacher'.$thisrecipient->id;
            if ( isset($fromform->{$property}) ) {
                if ( email_to_user($thisrecipient, $from, $subject, $messagetext, $messagehtml) ) {
                    add_to_log($cid, 'user_contact', 'send mail', '', 'To: '.$fullnamerecipient.'; From: '.$fullnamesender.'; Subject: '.$subject);
                } else {
                    echo "Error in blocks/user_contact/sendmessage.php: Could not send out mail from $from->firstname to fullname($thisrecipient) ($user->email)\n";
                    add_to_log($cid, 'user_contact', 'send mail failure', '', 'To: '.$fullnamerecipient.'; From: '.$fullnamesender.'; Subject:'.$subject);
                }
            }
        }
    }

    if ($allstandardrecipients) {
        foreach ($allstandardrecipients as $thisrecipient) {
            $fullnamerecipient = fullname($thisrecipient);
            $property = 'cf_teacher'.$thisrecipient->id;
            if ( isset($fromform->{$property}) ) {
                if ( email_to_user($thisrecipient, $from, $subject, $messagetext, $messagehtml) ) {
                    add_to_log($cid, 'user_contact', 'send mail', '', 'To: '.$fullnamerecipient.'; From: '.$fullnamesender.'; Subject: '.$subject);
                } else {
                    echo "Error in blocks/user_contact/sendmessage.php: Could not send out mail from $from->firstname to fullname($thisrecipient) ($user->email)\n";
                    add_to_log($cid, 'user_contact', 'send mail failure', '', 'To: '.$fullnamerecipient.'; From: '.$fullnamesender.'; Subject:'.$subject);
                }
            }
        }
    }

    if ( $rcp == 1 ) {
        $subject = get_string('receipt', 'block_user_contact').$subject;
        if ( email_to_user($from, $from, $subject, $messagetext, $messagehtml) ) {
            add_to_log($cid, 'user_contact', 'send mail', '', 'To: '.$fullnamesender.'; From: '.$fullnamesender.'; Subject: '.$subject);
        } else {
            echo "Error in blocks/user_contact/sendmessage.php: Could not send out mail from $from->firstname to fullname($thisrecipient) ($user->email)\n";
            add_to_log($cid, 'user_contact', 'send mail failure', '', 'To: '.$fullnamesender.'; From: '.$fullnamesender.'; Subject:'.$subject);
        }
    }
} // end of: if (!$debug)

?>