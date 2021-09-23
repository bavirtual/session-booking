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
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Standard GPL and phpdocs
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $USER, $DB;

// Set up the page.
$categoryid = optional_param('categoryid', null, PARAM_INT);
$courseid = optional_param('courseid', SITEID, PARAM_INT);
$course = get_course($courseid);
$title = $course->shortname . ' ' . get_string('pluginname', 'local_booking');
$title = get_string('pluginname', 'local_booking');

$url = new moodle_url('/local/booking/view.php');

$iscourse = $courseid != SITEID;

if ($iscourse) {
    $url->param('courseid', $courseid);
}

if ($categoryid) {
    $url->param('categoryid', $categoryid);
}

$PAGE->set_url($url);

if ($iscourse && !empty($courseid)) {
    navigation_node::override_active_url(new moodle_url('/course/view.php', array('id' => $courseid)));
} else if (!empty($categoryid)) {
    core_course_category::get($categoryid); // Check that category exists and can be accessed.
    $PAGE->set_category_by_id($categoryid);
    navigation_node::override_active_url(new moodle_url('/course/index.php', array('categoryid' => $categoryid)));
} else {
    $PAGE->set_context(context_system::instance());
}


$context = context_course::instance($courseid);

require_login($course, false);
require_capability('local/booking:view', $context);

$url->param('courseid', $courseid);

// RobinHerbots-Inputmask library to mask flight times in the Log Book modal form
$PAGE->requires->jquery();
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/node_modules/inputmask/dist/jquery.inputmask.min.js'), true);

$PAGE->navbar->add(userdate(time(), get_string('strftimedate')));
$PAGE->set_pagelayout('standard');
$PAGE->set_title($title, 'local_booking');
$PAGE->set_heading($title, 'local_booking');
$PAGE->add_body_class('path-local-booking');

$renderer = $PAGE->get_renderer('local_booking');

echo $OUTPUT->header();
echo $renderer->start_layout();
echo html_writer::start_tag('div', array('class'=>'heightcontainer'));

list($data, $template) = get_bookings_view($courseid);
echo $renderer->render_from_template($template, $data);

list($data, $template) = get_students_view($courseid);
echo $renderer->render_from_template($template, $data);

if (has_capability('local/booking:participationview', $context)) {
    list($data, $template) = get_participation_view($courseid);
    echo $renderer->render_from_template($template, $data);
}

echo html_writer::end_tag('div');
echo $renderer->complete_layout();
echo $OUTPUT->footer();
