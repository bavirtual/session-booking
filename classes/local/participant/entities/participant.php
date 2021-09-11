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
 * Class representing all student and instructor course participants
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\participant\entities;

use DateTime;
use local_booking\local\participant\data_access\participant_vault;

require_once($CFG->dirroot . "/lib/completionlib.php");

class participant implements participant_interface {

    /**
     * Get all active students.
     *
     * @return {Object}[]   Array of active students.
     */
    public function get_active_students(int $courseid = 0) {
        global $COURSE;

        $vault = new participant_vault();
        $studentcourseid = $courseid == 0 ? $COURSE->id : $courseid;

        return $vault->get_active_students($studentcourseid);
    }

    /**
     * Get all active instructors for the course.
     *
     * @return {Object}[]   Array of active instructors.
     */
    public function get_active_instructors(int $courseid = 0) {
        global $COURSE;

        $vault = new participant_vault();
        $instructorcourseid = $courseid == 0 ? $COURSE->id : $courseid;

        return $vault->get_active_instructors($instructorcourseid);
    }

    /**
     * Get all active instructors for the course.
     *
     * @return {Object}[]   Array of active instructors.
     */
    public function get_active_participants(int $courseid = 0) {

        $vault = new participant_vault();
        $participants = array_merge($vault->get_active_students($courseid), $vault->get_active_instructors($courseid));

        return $participants;
    }

    /**
     * Get students assigned to an instructor.
     *
     * @return {Object}[]   Array of students.
     */
    public function get_assigned_students() {
        global $COURSE, $USER;

        $vault = new participant_vault();
        $courseid = $COURSE->id;
        $userid = $USER->id;

        return $vault->get_assigned_students($courseid, $userid);
    }

    /**
     * Get a student
     *
     * @return {Object} A student object.
     */
    public function get_student($studentid) {
        $vault = new participant_vault();

        return $vault->get_student($studentid);
    }

    /**
     * Get grades for a specific student.
     *
     * @param int       $studentid  The student id.
     * @return {object}[]  A student booking.
     */
    public function get_grades($studentid) {
        $vault = new participant_vault();

        return $vault->get_grades($studentid);
    }

    /**
     * Returns whether the student complete
     * all sessons prior to the upcoming next
     * exercise.
     *
     * @param   int     The student id
     * @param   int     The course id
     * @param   int     The upcoming next exercise id
     * @return  bool    Whether the lessones were completed or not.
     */
    function get_lessons_complete($studentid, $courseid, $nextexercisesection) {
        $vault = new participant_vault();

        return $vault->get_lessons_complete($studentid, $courseid, $nextexercisesection);
    }

    /**
     * Returns the next upcoming exercise id
     * for the student and its associated course section.
     *
     * @param   int     The student id
     * @param   int     The course id
     * @return  array   The next exercise id and associated course section
     */
    function get_next_exercise($studentid, $courseid) {
        $vault = new participant_vault();

        return $vault->get_next_exercise($studentid, $courseid);
    }

    /**
     * Get student's enrolment date.
     *
     * @param int       $studentid  The student id in reference
     * @return DateTime $enroldate  The enrolment date of the student.
     */
    public function get_enrol_date(int $studentid) {
        global $COURSE;

        $vault = new participant_vault();

        $enrol = $vault->get_enrol_date($COURSE->id, $studentid);
        $enroldate = new DateTime('@' . $enrol->timecreated);

        return $enroldate;
    }

    /**
     * Suspends the student's enrolment to a course.
     *
     * @param int   $studentid  The student id in reference
     * @param int   $courseid   The course the student is being unenrolled from.
     * @return bool             The result of the suspension action.
     */
    public function set_suspend_status(int $studentid, int $courseid) {
        $vault = new participant_vault();

        return $vault->set_suspend_status($studentid, $courseid);
    }

    /**
     * Returns full username
     *
     * @return string  The full username with optional alternate info
     */
    public static function get_fullname(int $userid, bool $alternate = true) {
        $vault = new participant_vault();

        $fullusername = '';
        if ($userid != 0) {
            $userinfo = $vault->get_participant_name($userid);
            $fullusername = $alternate ? $userinfo->fullname : $userinfo->username;
        }

        return $fullusername;
    }

    /**
     * Returns participant's callsign user field
     *
     * @param int       The pilot user id
     * @return string   The participant callsign
     */
    public static function get_callsign(int $pilotid) {
        global $COURSE;
        $vault = new participant_vault();

        return $vault->get_customfield_data($COURSE->id, $pilotid, 'callsign');
    }
}