<?php

/**
 * Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar
 * @copyright  BAVirtual.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Standard GPL and phpdocs
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/calendar/lib.php');

global $USER, $DB;

use html_writer;

// // Set up the page.
$categoryid = optional_param('category', null, PARAM_INT);
$courseid = optional_param('course', SITEID, PARAM_INT);
$course = get_course($courseid);
$pluginname = $course->shortname . ' ' . get_string('pluginname', 'local_booking');
$title = get_string('title', 'local_booking');

$url = new moodle_url('/local/booking/view.php');

$iscourse = $courseid != SITEID;

if ($iscourse) {
    $url->param('courseid', $courseid);
}

if ($categoryid) {
    $url->param('categoryid', $categoryid);
}

$PAGE->set_url($url);

$course = get_course($courseid);

if ($iscourse && !empty($courseid)) {
    navigation_node::override_active_url(new moodle_url('/course/view.php', array('id' => $course->id)));
} else if (!empty($categoryid)) {
    core_course_category::get($categoryid); // Check that category exists and can be accessed.
    $PAGE->set_category_by_id($categoryid);
    navigation_node::override_active_url(new moodle_url('/course/index.php', array('categoryid' => $categoryid)));
} else {
    $PAGE->set_context(context_system::instance());
}

require_login($course, false);

$url->param('courseid', $courseid);

$PAGE->navbar->add(userdate($time, get_string('progression','local_booking')));
$PAGE->set_pagelayout('base');  // otherwise use 'standard' layout
$PAGE->set_title($pluginname, 'local_booking');
$PAGE->set_heading($pluginname, 'local_booking');// . ' course id='  . $courseid);
$PAGE->add_body_class('path-local-booking');

$template = 'local_booking/session_booking';
$renderer = $PAGE->get_renderer('loca_booking');

echo $OUTPUT->header();
echo $renderer->start_layout();
echo html_writer::start_tag('div', array('class'=>'heightcontainer'));

list($data, $template) = get_progression_view();
echo $renderer->render_from_template($template, $data);

// list($data, $template) = get_bookings_view($calendar);
// echo $renderer->render_from_template($template, $data);

// list($data, $template) = get_students_view();
// echo $renderer->render_from_template($template, $data);

echo html_writer::end_tag('div');

echo $renderer->complete_layout();
echo $OUTPUT->footer();
