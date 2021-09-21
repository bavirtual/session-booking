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

global $USER;

// Set up the page.
$categoryid = optional_param('category', null, PARAM_INT);
$courseid = optional_param('course', SITEID, PARAM_INT);
$action =  optional_param('action', null, PARAM_RAW);
$view =  optional_param('view', 'user', PARAM_RAW);
$studentid = optional_param('userid', $USER->id, PARAM_INT);
$exerciseid = optional_param('exid', 0, PARAM_INT);
$course = get_course($courseid);
$pluginname = $course->shortname . ' ' . get_booking_config('ATO') . ' ' . get_string('pluginname', 'local_booking');
$title = get_string('weeklytitle', 'local_booking');
$week = get_string('week', 'local_booking');
$time = optional_param('time', 0, PARAM_INT);

$url = new moodle_url('/local/booking/availability.php');

// If a day, month and year were passed then convert it to a timestamp. If these were passed
// then we can assume the day, month and year are passed as Gregorian, as no where in core
// should we be passing these values rather than the time. This is done for BC.
if (!empty($day) && !empty($mon) && !empty($year)) {
    if (checkdate($mon, $day, $year)) {
        $time = make_timestamp($year, $mon, $day);
    }
}

if (empty($time)) {
    $time = time();
}

$iscoursecalendar = $courseid != SITEID;

if ($iscoursecalendar) {
    $url->param('course', $courseid);
}

if ($categoryid) {
    $url->param('categoryid', $categoryid);
}

$url->param('view', 'user');
$url->param('time', $time);

$PAGE->set_url($url);

$course = get_course($courseid);

if ($iscoursecalendar && !empty($courseid)) {
    navigation_node::override_active_url(new moodle_url('/course/view.php', array('id' => $course->id)));
} else if (!empty($categoryid)) {
    core_course_category::get($categoryid); // Check that category exists and can be accessed.
    $PAGE->set_category_by_id($categoryid);
    navigation_node::override_active_url(new moodle_url('/course/index.php', array('categoryid' => $categoryid)));
} else {
    $PAGE->set_context(context_system::instance());
}

require_login($course, false);

$url->param('course', $courseid);

$calendar = calendar_information::create($time, $courseid, $categoryid);

$PAGE->navbar->add(userdate($time, get_string('strftimeweekinyear','local_booking')));
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pluginname, 'local_booking');
$PAGE->set_heading($pluginname, 'local_booking');// . ' course id='  . $courseid);
$PAGE->add_body_class('path-availability');

$template = 'local_booking/calendar_week';
$renderer = $PAGE->get_renderer('core_calendar');
$calendar->add_sidecalendar_blocks($renderer, true, null);

echo $OUTPUT->header();
echo $renderer->start_layout();
echo html_writer::start_tag('div', array('class'=>'heightcontainer'));

// action data for booking view
$actiondata = [
    'action'        => $action,
    'studentid'     => $studentid,
    'exerciseid'    => $exerciseid,
    ];

list($data, $template) = get_weekly_view($calendar, $actiondata, $view);
echo $renderer->render_from_template($template, $data);

echo html_writer::end_tag('div');

// list($data, $template) = calendar_get_footer_buttons($calendar);
// echo $renderer->render_from_template($template, $data);
// echo $OUTPUT->addElement('submit', 'savebutton', get_string('savebutton', 'local_booking'));

echo $renderer->complete_layout();
echo $OUTPUT->footer();
