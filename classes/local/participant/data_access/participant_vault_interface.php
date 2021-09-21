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
 * Class interface for data access of course participants
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\participant\data_access;

defined('MOODLE_INTERNAL') || die();

interface participant_vault_interface {

    /**
     * Get all active students from the database.
     *
     * @return {Object}[]          Array of database records.
     */
    public function get_active_students(int $courseid);

    /**
     * Get all active instructors for the course from the database.
     *
     * @return {Object}[]          Array of database records.
     */
    public function get_active_instructors(int $courseid);

    /**
     * Get students assigned to an instructor from the database.
     *
     * @param int $courseid The course in context
     * @param int $userid   The instructor user id
     * @return {Object}[]   Array of database records.
     */
    public function get_assigned_students(int $courseid, int $userid);

    /**
     * Get grades for a specific student.
     *
     * @param int       $studentid  The student id.
     * @return grade[]              A student booking.
     */
    public function get_student_assignment_grades($studentid);

    /**
     * Get grades for a specific student.
     *
     * @param int       $studentid  The student id.
     * @return grade[]  A student quizes.
     */
    public function get_student_quizes_grades($studentid);

    /**
     * Get student's enrolment date.
     *
     * @param int       $studentid  The student id in reference
     * @return DateTime $enroldate  The enrolment date of the student.
     */
    public function get_enrol_date(int $courseid, int $studentid);

    /**
     * Suspends the student's enrolment to a course.
     *
     * @param int   $courseid   The course the student is being unenrolled from.
     * @param int   $studentid  The student id in reference
     * @return bool             The result of the suspension action.
     */
    public function set_suspend_status(int $courseid, int $studentid);

    /**
     * Returns full username
     *
     * @return string  The full participatn username
     */
    public static function get_participant_name(int $userid);

    /**
     * Returns custom field value from the user's profile
     *
     * @param int $courseid         The course id
     * @param int $participantid    The participant id
     * @param string $field         The field name associated with the rquested data
     * @return string               The full participatn username
     */
    public static function get_customfield_data(int $courseid, int $participantid, string $field);
}
