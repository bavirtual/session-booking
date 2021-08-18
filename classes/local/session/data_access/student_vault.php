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

namespace local_booking\local\session\data_access;

class student_vault implements student_vault_interface {


    /**
     * Process user enrollments table name.
     */
    const DB_USER = 'user';

    /**
     * Process user role table name.
     */
    const DB_ROLE = 'role';

    /**
     * Process user role assignment table name.
     */
    const DB_ROLE_ASSIGN = 'role_assignments';

    /**
     * Process user info data table name for the simulator.
     */
    const DB_USER_DATA = 'user_info_data';

    /**
     * Process user info data table name for the simulator.
     */
    const DB_USER_FIELD = 'user_info_field';

    /**
     * Process user enrollments table name.
     */
    const DB_USER_ENROL = 'user_enrolments';

    /**
     * Process  enrollments table name.
     */
    const DB_ENROL = 'enrol';

    /**
     * Process groups table name for on-hold group.
     */
    const DB_GROUPS = 'groups';

    /**
     * Process groups members table name for on-hold students.
     */
    const DB_GROUPS_MEM = 'groups_members';

    /**
     * Process groups members table name for on-hold students.
     */
    const DB_COURSE_MODS = 'course_modules';

    /**
     * Process user enrollments table name.
     */
    const DB_GRADES = 'assign_grades';

    /**
     * Get all active students from the database.
     *
     * @return {Object}[]          Array of database records.
     */
    public function get_students() {
        global $DB, $COURSE;

        $sql = 'SELECT u.id AS userid, ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname', '" "', 'u.alternatename') . ' AS fullname,
                    ud.data AS simulator, ue.timemodified AS enroldate
                FROM {' . self::DB_USER . '} u
                INNER JOIN {' . self::DB_ROLE_ASSIGN . '} ra on u.id = ra.userid
                INNER JOIN {' . self::DB_ROLE . '} r on r.id = ra.roleid
                INNER JOIN {' . self::DB_USER_DATA . '} ud on ra.userid = ud.userid
                INNER JOIN {' . self::DB_USER_FIELD . '} uf on uf.id = ud.fieldid
                INNER JOIN {' . self::DB_USER_ENROL . '} ue on ud.userid = ue.userid
                INNER JOIN {' . self::DB_ENROL . '} en on ue.enrolid = en.id
                WHERE en.courseid = ' . $COURSE->id . '
                    AND ra.contextid = ' . \context_course::instance($COURSE->id)->id .'
                    AND r.shortname = "student"
                    AND uf.shortname = "simulator"
                    AND ue.status = 0
                    AND u.id != (
                        SELECT userid
                        FROM {' . self::DB_GROUPS_MEM . '} gm
                        INNER JOIN {' . self::DB_GROUPS . '} g on g.id = gm.groupid
                        WHERE g.name = "OnHold"
                        )';

        return $DB->get_records_sql($sql);
    }

    /**
     * Get grades for a specific student.
     *
     * @param int       $studentid  The student id.
     * @return grade[]              A student booking.
     */
    public function get_grades($studentid) {
        global $DB;

        // Get the student's grades
        $sql = 'SELECT cm.id AS exerciseid, ag.assignment AS assignid,
                    ag.userid, ag.grade, ag.timemodified AS gradedate,
                    u.id AS instructorid, ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname', '" "', 'u.alternatename') . ' AS instructorname
                FROM {' . self::DB_GRADES . '} ag
                INNER JOIN {' . self::DB_COURSE_MODS . '} cm on ag.assignment = cm.instance
                INNER JOIN {' . self::DB_USER . '} u on ag.grader = u.id
                WHERE cm.module = 1 AND ag.userid = ' . $studentid . '
                ORDER BY cm.section';

        return $DB->get_records_sql($sql);

    }
}