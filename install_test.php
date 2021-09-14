<?php

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $USER, $DB;

use html_writer;
use local_booking\local\subscriber\subscriber_info;

// check if user profile category exists for ATO
// create user profile category for ATO

// check if user profile field exists for ATO category
// create user profile Session Booking custom fields for ATO


$courseid = optional_param('courseid', SITEID, PARAM_INT);

$url = new moodle_url('/local/booking/install_test.php');
$title = get_string('pluginname', 'local_booking');

require_login($courseid);

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($title, 'local_booking');
$PAGE->set_heading($title, 'local_booking');

$renderer = $PAGE->get_renderer('local_booking');

echo $OUTPUT->header();
echo $renderer->start_layout();
echo html_writer::start_tag('div', array('class'=>'heightcontainer'));

$courses = get_courses();

foreach ($courses as $course) {
    if ($course->id != SITEID) {
        $subscriber = new subscriber_info($course->id);
        var_dump($course->shortname);
        var_dump($subscriber);
    }
}

//echo 'DONE'.PHP_EOL;

echo html_writer::end_tag('div');
echo $renderer->complete_layout();
echo $OUTPUT->footer();


