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

namespace local_booking\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/externallib.php');
require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/local/booking/lib.php');

use DateTime;
use local_booking\local\participant\entities\participant;
use local_booking\local\session\entities\booking;
use local_booking\local\slot\entities\slot;
use local_booking\local\message\notification;
use local_booking\local\subscriber\subscriber;

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

        // get course list
        $sitecourses = get_courses();

        foreach ($sitecourses as $sitecourse) {
            if ($sitecourse->id != SITEID) {
                // check if the course is using Session Booking
                $course = new subscriber($sitecourse->id);
                if (!empty($course->subscribed) && $course->subscribed) {
                    mtrace('    Course id: ' . $sitecourse->id);
                    $message = new notification();

                    // get wait days
                    $today = getdate(time());
                    $waitdays = get_config('local_booking', 'nextsessionwaitdays') ? get_config('local_booking', 'nextsessionwaitdays') : LOCAL_BOOKING_DAYSFROMLASTSESSION;

                    // get active students
                    $activestudents = $course->get_active_students($sitecourse->id);

                    // consider on-hold and suspension candidates
                    foreach ($activestudents as $student) {
                        $studentname = get_fullusername($student->userid);

                        // get on-hold date, otherwise use last login for on-hold comparison
                        $lastsession = slot::get_last_posting($sitecourse->id, $student->userid);
                        $lastsessiondate = new DateTime('@' . (!empty($lastsession) ? $lastsession->starttime : $student->lastlogin));
                        $onholddate = new DateTime('@' . $lastsessiondate->getTimestamp());
                        // on-hold date is 3x wait period from last session
                        date_add($onholddate, date_interval_create_from_date_string(($waitdays * LOCAL_BOOKING_ONHOLDWAITMULTIPLIER) . ' days'));

                        // on-hold warning date: 7 days before on-hold date
                        $onholdwarningdate = $onholddate;
                        date_add($onholdwarningdate, date_interval_create_from_date_string('7 days'));

                        // Suspension (unenrolment) date is 9x wait period from last session
                        $suspenddate = new DateTime('@' . $onholddate->getTimestamp());
                        date_add($suspenddate, date_interval_create_from_date_string(($waitdays * LOCAL_BOOKING_SUSPENDWAITMULTIPLIER) . ' days'));

                        // notify student a week before being placed on-hold
                        if ($today['yday'] == getdate($onholdwarningdate->getTimestamp())['yday']) {
                            mtrace('        Notifying student becoming on-hold in a week...');

                            $message->send_onhold_warning($student->userid, $onholddate, $sitecourse->id, $sitecourse->shortname);
                        }

                        // place student on-hold and send notification
                        if ($today['yday'] == getdate($onholddate->getTimestamp())['yday']) {

                            // add student to on-hold group
                            $onholdgroupid = groups_get_group_by_name($sitecourse->id, LOCAL_BOOKING_ONHOLDGROUP);
                            groups_add_member($onholdgroupid, $student->userid);

                            // send notification of upcoming placement on-hold
                            if ($message->send_onhold_notification($student->userid, $lastsessiondate, $suspenddate, $sitecourse->id, $sitecourse->shortname)) {
                                mtrace('        Placed \'' . $studentname . '\' on-hold (notified)...');
                            }
                        }

                        // suspend when passed on-hold by 9x wait days
                        if ($today['yday'] == getdate($suspenddate->getTimestamp())['yday']) {

                            // send notification of unenrolment from the course
                            if ($message->send_suspension_notification($student->userid, $lastsessiondate, $sitecourse->id, $sitecourse->shortname)) {
                                mtrace('        Suspended \'' . $studentname . '\' (notified)...');
                                // unenrol the student from the course
                                $participant = new participant($sitecourse->id, $student->userid);
                                if ($participant->set_suspend_status()) {
                                    mtrace('        Notifying student of being suspended...');
                                }
                            }
                        }
                    }

                    // get instructors
                    $instructors = $course->get_active_instructors($sitecourse->id);
                    $isoverdue = false;

                    // consider inactive instructors
                    foreach ($instructors as $instructor) {
                        $instructorname = get_fullusername($instructor->userid);
                        mtrace('    Instructor: ' . $instructorname);

                        // get instructor last booked session, otherwise use the last login for date compare
                        $lastsession = booking::get_last_session($instructor->userid, true);
                        if (!empty($lastsession)) {
                            $lastsessiondate = new DateTime('@' . (!empty($lastsession) ? $lastsession->lastbookedsession : $instructor->lastlogin));

                            // get days since last session
                            $interval = $lastsessiondate->diff(new DateTime('@' . $today[0]));
                            $dayssincelast = $interval->format('%d') != 0 ? $interval->format('%d') : $waitdays * LOCAL_BOOKING_INSTRUCTORINACTIVEMULTIPLIER;

                            // check if 3x waitdays has past without a booking
                            $isoverdue = $dayssincelast % ($waitdays * LOCAL_BOOKING_INSTRUCTORINACTIVEMULTIPLIER) == 0 &&
                                $dayssincelast >= ($waitdays * LOCAL_BOOKING_INSTRUCTORINACTIVEMULTIPLIER);
                            $status = get_string('emailoverduestatus', 'local_booking', $lastsessiondate);
                        } else {
                            $isoverdue = true;
                            $status = get_string('emailoverduenobooking', 'local_booking');
                        }
                        if ($isoverdue) {
                            mtrace('        Notifying instructor \'' . $instructorname . '\' of inactivity (retry=' . ($dayssincelast / $waitdays) . ')...');
                            // send notification to the instructor of a session overdue since last
                            $message->send_session_overdue_notification($instructor->userid, $status, $sitecourse->id, $sitecourse->shortname);
                        }
                    }
                }
            }
        }

        return true;
    }
}
