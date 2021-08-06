<?php

/**
 * Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar
 * @copyright  BAVirtual.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \local_booking\external\progression_exporter;

/**
 * Get the calendar view output.
 *
 * @param   \calendar_information $calendar The calendar being represented
 * @param   string  $view The type of calendar to have displayed
 * @param   bool    $includenavigation Whether to include navigation
 * @param   bool    $skipevents Whether to load the events or not
 * @param   int     $lookahead Overwrites site and users's lookahead setting.
 * @return  array[array, string]
 */
function get_progression_view($courseid, $categoryid) {
    global $PAGE;

    $renderer = $PAGE->get_renderer('local_booking');

    $template = 'local_booking/progress_detailed';
    $data = [
        'courseid' => $courseid,
        'categoryid' => $categoryid,
    ];

    $progression = new progression_exporter($data, ['context' => \context_system::instance()]);
    $data = $progression->export($renderer);

    return [$data, $template];
}

// /**
//  * Get the calendar view output.
//  *
//  * @param   \calendar_information $calendar The calendar being represented
//  * @param   string  $view The type of calendar to have displayed
//  * @param   bool    $includenavigation Whether to include navigation
//  * @param   bool    $skipevents Whether to load the events or not
//  * @param   int     $lookahead Overwrites site and users's lookahead setting.
//  * @return  array[array, string]
//  */
// function get_bookings_view(\calendar_information $calendar) {
//     global $PAGE;

//     $renderer = $PAGE->get_renderer('local_booking');
//     $type = \core_calendar\type_factory::get_calendar_instance();

//     $date = new \DateTime('now', core_date::get_user_timezone_object(99));
//     $eventlimit = 0;
//     $calendardate = $type->timestamp_to_date_array(time());

//     $tstart = $type->convert_to_timestamp($calendardate['year'], $calendardate['mon'], 1);
//     $date->setTimestamp($tstart);
//     $date->modify('+7 days');
//     $template = 'local_booking/progress_detail';

//     // We need to extract 1 second to ensure that we don't get into the next day.
//     $date->modify('-1 second');
//     $tend = $date->getTimestamp();


//     list($userparam, $groupparam, $courseparam, $categoryparam) = array_map(function($param) {
//         // If parameter is true, return null.
//         if ($param === true) {
//             return null;
//         }

//         // If parameter is false, return an empty array.
//         if ($param === false) {
//             return [];
//         }

//         // If the parameter is a scalar value, enclose it in an array.
//         if (!is_array($param)) {
//             return [$param];
//         }

//         // No normalisation required.
//         return $param;
//     }, [$calendar->users, $calendar->groups, $calendar->courses, $calendar->categories]);

//     $events = \core_calendar\local\api::get_events(
//         $tstart,
//         $tend,
//         null,
//         null,
//         null,
//         null,
//         $eventlimit,
//         null,
//         $userparam,
//         $groupparam,
//         $courseparam,
//         $categoryparam,
//         true,
//         true,
//         function ($event) {
//             if ($proxy = $event->get_course_module()) {
//                 $cminfo = $proxy->get_proxied_instance();
//                 return $cminfo->uservisible;
//             }

//             if ($proxy = $event->get_category()) {
//                 $category = $proxy->get_proxied_instance();

//                 return $category->is_uservisible();
//             }

//             return true;
//         }
//     );

//     $related = [
//         'events' => $events,
//         'cache' => new events_related_objects_cache($events),
//         'type' => $type,
//     ];

//     $week = new active_bookings_exporter($calendar, $type, $related);
//     $week->set_initialeventsloaded(!$skipevents);
//     $data = $week->export($renderer);
//     $data->viewingmonth = true;

//     return [$data, $template];
// }


/**
 * This function extends the navigation with the booking item
 *
 * @param global_navigation $navigation The global navigation node to extend
 */

function local_booking_extend_navigation(global_navigation $navigation) {
    global $COURSE;

    $systemcontext = context_system::instance();

    if (has_capability('local/booking:addinstance', $systemcontext)) {
    // $node = $navigation->find('booking', navigation_node::TYPE_CUSTOM);
        $node = $navigation->find('booking', navigation_node::NODETYPE_LEAF);
        if (!$node && $COURSE->id!==SITEID) {
            $parent = $navigation->find($COURSE->id, navigation_node::TYPE_COURSE);
            $node = navigation_node::create(get_string('booking', 'local_booking'), new moodle_url('/local/booking/view.php', array('courseid'=>$COURSE->id)));
            $node->key = 'booking';
            $node->type = navigation_node::NODETYPE_LEAF;
            $node->forceopen = true;
            $node->icon = new  pix_icon('i/emojicategorytravelplaces', '');  // e/table_props  e/split_cells

            $parent->add_node($node);
        }
    }
}
