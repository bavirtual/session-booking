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

namespace local_booking\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/externallib.php');
require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/local/booking/lib.php');

use DateTime;
use local_booking\local\participant\data_access\participant_vault;
use local_booking\local\slot\data_access\slot_vault;
use local_booking\local\session\data_access\booking_vault;
use local_booking\local\message\notification;

/**
 * A schedule task for student and instructor status cron.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'local_booking');
    }

    /**
     * Run session booking cron.
     */
    public function execute() {
        $participantsvault = new participant_vault();
        $bookingvault = new booking_vault();
        $slotsvault = new slot_vault();

        // get course list
        $courseshortnames = explode(',', get_config('local_booking', 'coursesusing'));

        // evaluate for each course using the Session Booking plugin
        for ($i = 0; $i < count($courseshortnames); $i++) {
            $courseid = array_key_first(\core_course_external::get_courses_by_field('shortname', $courseshortnames[$i]));
            $message = new notification();

            // get wait days
            $today = new DateTime('@' . time());
            $waitdays = get_config('local_booking', 'nextsessionwaitdays') ? get_config('local_booking', 'nextsessionwaitdays') : LOCAL_BOOKING_DAYSFROMLASTSESSION;

            // get active students
            $activestudents = $participantsvault->get_active_students($courseid);

            // consider on-hold and suspension candidates
            foreach ($activestudents as $student) {
                // on-hold date is 3x wait period from last session
                $lastsessiondatets = $slotsvault->get_last_posted_slot($student->userid);
                $lastsessiondate = new DateTime('@' . $lastsessiondatets);
                $onholddate = $lastsessiondate;
                date_add($onholddate, date_interval_create_from_date_string(($waitdays * 3) . ' days'));
                $onholdwarningdate = $onholddate;
                date_add($onholdwarningdate, date_interval_create_from_date_string('7 days'));
                $suspenddate = $onholddate;
                // Suspension (unenrolment) date is 9x wait period from last session
                date_add($suspenddate, date_interval_create_from_date_string(($waitdays * 9) . ' days'));

                // notify student a week before being placed on-hold
                if ($today['yday'] == getdate($onholdwarningdate->getTimestamp())['yday']) {
                    $message->send_onhold_warning($student->userid, $onholddate, $courseid, $courseshortnames[$i]);
                }

                // place student on-hold and send notification
                if ($today['yday'] == getdate($onholddate->getTimestamp())['yday']) {
                    // add student to on-hold group
                    $onholdgroupid = groups_get_group_by_name($courseid, LOCAL_BOOKING_ONHOLDGROUP);
                    groups_add_member($onholdgroupid, $student->userid);

                    // send notification of upcoming placement on-hold
                    $message->send_onhold_notification($student->userid, $lastsessiondate, $suspenddate, $courseid, $courseshortnames[$i]);
                }

                // suspend when passed on-hold by 9x wait days
                if ($today['yday'] == getdate($suspenddate->getTimestamp())['yday']) {

                    // unenrol the student from the course
                    if ($participantsvault->set_suspend_status($student->userid, $courseid)) {
                        // send notification of unenrolment from the course
                        $message->send_suspension_notification($student->userid, $lastsessiondate, $courseid, $courseshortnames[$i]);
                    }
                }
            }

            // get instructors
            $instructors = $participantsvault->get_active_instructors($courseid);
            foreach ($instructors as $instructor) {
                // consider instructor activity
                $lastsessiondatets = $bookingvault->get_last_booked_session($instructor->userid);
                $lastsessiondate = new DateTime('@' . $lastsessiondatets);

                // get days since last session
                $interval = $lastsessiondate->diff(new DateTime('@' . $today[0]));
                // check if 3x waitdays has past without a booking
                if (($waitdays * 3) % $interval->format('%d') == 0) {
                    // send notification to the instructor of a session overdue since last
                    $message->send_session_overdue_notification($instructor->userid, $lastsessiondate, $courseid, $courseshortnames[$i]);
                }

            }
        }

        return true;
    }
}
