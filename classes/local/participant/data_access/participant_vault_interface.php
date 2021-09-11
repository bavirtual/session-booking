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
     * Get a student record from the database.
     *
     * @return {Object}[]   An array of student record.
     */
    public function get_student($studentid);

    /**
     * Get all active students from the database.
     *
     * @return {Object}[]          Array of database records.
     */
    public function get_active_students(int $courseid = 0);

    /**
     * Get all active instructors for the course from the database.
     *
     * @return {Object}[]          Array of database records.
     */
    public function get_active_instructors(int $courseid = 0);

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
    public function get_grades($studentid);

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
    function get_lessons_complete($studentid, $courseid, $nextexercisesection);

    /**
     * Returns the next upcoming exercise id
     * for the student and its associated course section.
     *
     * @param   int     The student id
     * @param   int     The course id
     * @return  array   The next exercise id and associated course section
     */
    function get_next_exercise($studentid, $courseid);

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
