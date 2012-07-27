<?php // $Id: user_contact.php,v 1.1 2011/02/08 18:47:29 arborrow Exp $
    //global $CFG, $USER;

    require_once('../../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once('user_contact_form.php');
    require_once($CFG->dirroot.'/blocks/user_contact/lib.php');

    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad', 'error');
    }

    $cid = optional_param('cid', 0, PARAM_INT); // course ID
    $bid = optional_param('bid', 0, PARAM_INT); // block  ID
    $rcp = optional_param('rcp', 0, PARAM_INT); // was receipt requested?

    // if you are reloading the page without resending right parameters,
    // stop here your work and redirect to the home page.
    //if ($cid == 0)
    //    redirect($CFG->wwwroot.'/index.php');

    $debug = false;
    if ($debug) {
        echo 'Scrivo dalla riga '.__LINE__.' del file '.__FILE__.'<br />';
        echo 'course ID: $cid = '.$cid.'<br />';
        echo 'block  ID: $bid = '.$bid.'<br />';
        echo '$rcp = '.$rcp.'<br />';
        //die;
    }
    if (! $course = $DB->get_record('course', array('id'=>$cid))) {
        print_error('coursemisconf');
    }

    //----------------------------------------------------------------------------
// Initialize $PAGE, compute blocks
    $PAGE->set_url('/blocks/user_contact/user_contact_form.php', array('cid' => $cid, 'bid' => $bid, 'rcp' => $rcp));
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
    $PAGE->set_title($course->shortname.': user_contact');
    $PAGE->set_heading($course->fullname);
    $PAGE->set_pagelayout('course');
    //$PAGE->set_pagetype('course-view-' . $course->format);


    //print_object($PAGE);
    if ($cid == SITEID) { // home page
        //$PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php?id='.$cid));
        $PAGE->navbar->add(get_string('pluginname','block_user_contact'));
    } else {
        $countcategories = $DB->count_records('course_categories');
        if ($countcategories > 1 || ($countcategories == 1 && $DB->count_records('course') > 200)) {
            $PAGE->navbar->add(get_string('categories'));
        } else {
            $PAGE->navbar->add(get_string('courses'), new moodle_url('/course/category.php?id='.$course->category));
            $PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php?id='.$cid));
            $PAGE->navbar->add(get_string('pluginname','block_user_contact'));
        }
    }

    //----------------------------------------------------------------------------
    $allhiddenrecipients   = user_contact_getallhiddenrecipients($cid,$bid);
    $allstandardrecipients = user_contact_getallstandardrecipients($bid);

    $mform = new block_user_contact_form($CFG->wwwroot.'/blocks/user_contact/user_contact.php');
    if ($mform->is_cancelled()) {
        // submission was canceled. Return back.
        $returnurl = ($cid == SITEID) ? new moodle_url('/index.php') : new moodle_url('/course/view.php', array('id'=>$cid));
        redirect($returnurl,get_string('usercanceled','block_user_contact'));
    } else if ($fromform = $mform->get_data()) {
        // form was successfully submitted. Now send.
        include_once('sendmessage.php');

        $returnurl = ($cid == SITEID) ? new moodle_url('/index.php') : new moodle_url('/course/view.php', array('id'=>$cid));
        redirect($returnurl,get_string('messagesent','block_user_contact'));
    } else {
    //this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    //or on the first display of the form.
    //put data you want to fill out in the form into array $toform here then :
        echo $OUTPUT->header();
        echo $OUTPUT->box(get_string('welcome_info', 'block_user_contact'));
        $mform->display();
    /// Finish the page
        echo $OUTPUT->footer();
    }
