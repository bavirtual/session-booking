<?php

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $USER, $DB;

use html_writer;
use \core_customfield\api;
use \core_customfield\category_controller;
use \core_customfield\field_controller;
use \core_customfield\event\field_created;

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

// $courses = get_courses();

// foreach ($courses as $course) {
//     if ($course->id != SITEID) {
//         $subscriber = new subscriber_info($course->id);
//         var_dump($course->shortname);
//         var_dump($subscriber);
//     }
// }


// Add ATO custom category and fields for all courses
// create_course_customfields();

// Delete ATO custom category and fields for all courses
// delete_course_customfields();

//echo 'DONE'.PHP_EOL;

echo html_writer::end_tag('div');
echo $renderer->complete_layout();
echo $OUTPUT->footer();



/**
 * Add to user profile ATO category and custom fields.
 */
function create_course_customfields() {
    // get handler for course custom fields
    $handler = \core_customfield\handler::get_handler('core_course', 'course');

    // check if course category exists for ATO
    $categories = api::get_categories_with_fields('core_course', 'course', 0);
    $category = null;
    $categoryexists = false;
    $categoryid = -1;
    $categorysortorder = -1;
    foreach ($categories as $coursecategory) {
        $categoryid = $coursecategory->get('id');
        $categorysortorder = $coursecategory->get('sortorder');
        if ($coursecategory->get('name') == get_booking_config('ATO')) {
            $categoryexists = true;
            $category = $coursecategory;
            continue;
        }
    }

    // create course category for ATO if it doesn't exist
    if (!$categoryexists) {
        $category = category_controller::create(0, new \stdClass(), $handler);
        $category->set('name', get_booking_config('ATO'));
        $category->set('descriptionformat', 0);
        $category->set('sortorder', $categorysortorder + 1);
        $category->set('component', 'core_course');
        $category->set('area', 'course');
        $category->set('contextid', 1);
        api::save_category($category);
    }

    // create course Session Booking custom fields for ATO
    save_course_customfield($category, 'checkbox', 'subscribed', get_string('useplugin', 'local_booking'),',"checkbydefault":"0"');
    save_course_customfield($category, 'text', 'homeicao',  get_string('homeicao', 'local_booking'),
        ',"defaultvalue":"","displaysize":50,"maxlength":1333,"ispassword":"0","link":""');
    save_course_customfield($category, 'textarea', 'aircrafticao', get_string('trainingaircraft', 'local_booking'), ',"defaultvalue":"","defaultvalueformat":"1"',
        get_string('trainingaircraftdesc', 'local_booking'));
    save_course_customfield($category, 'textarea', 'exercisetitles', get_string('exercisetitles', 'local_booking'), ',"defaultvalue":"","defaultvalueformat":"1"',
        get_string('exercisetitlesdesc', 'local_booking'));
}

/**
 * Persist course custom field.
 */
function save_course_customfield($category, $type, $shortname, $name, $configdata = '', $description = '') {
    $fieldexists = false;
    $field = null;
    $fieldsortorder = -1;
    $fields = $category->get_fields();
    // check if course custom field exists for ATO category
    foreach ($fields as $coursefield) {
        $fieldsortorder = $coursefield->get('sortorder');
        if ($coursefield->get('shortname') == $shortname) {
            $fieldexists = true;
            $field = $coursefield;
            continue;
        }
    }

    // create a field
    if (!$fieldexists) {
        $fieldrec = new \stdClass();
        $fieldrec->type = $type;
        $field = field_controller::create(0, $fieldrec, $category);
        // $field->set('type', $type);
        $field->set('shortname', $shortname);
        $field->set('name', $name);
        $field->set('description', !empty($description) ? '<p dir="ltr" style="text-align:left;">' . $description . '</p>' : '');
        $field->set('descriptionformat', 1);
        $field->set('sortorder', $fieldsortorder + 1);
        $field->set('configdata', '{"required":"0","uniquevalues":"0","locked":"0","visibility":"2"' . $configdata . '}');
        $field->save();
        field_created::create_from_object($field)->trigger();
    }
}


/**
 * Delete to ATO custom category and fields for all courses
 */
function delete_course_customfields() {
    // get all categories and fields.
    $categories = api::get_categories_with_fields('core_course', 'course', 0);
    foreach ($categories as $coursecategory) {
        // delete ATO category and associated fields
        if ($coursecategory->get('name') == get_booking_config('ATO')) {
            // Delete custom ATO category
            api::delete_category($coursecategory);
        }
    }
}
