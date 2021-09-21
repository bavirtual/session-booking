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
 * Class for data access of course participants
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\participant\data_access;

use DateTime;

require_once($CFG->dirroot . "/lib/completionlib.php");

class participant_vault implements participant_vault_interface {

    /**
     * Process user enrollments table name.
     */
    const DB_USER = 'user';

    /**
     * Process modules table name.
     */
    const DB_MODULES = 'modules';

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
     * Process user assignment grades table name.
     */
    const DB_GRADES = 'assign_grades';

    /**
     * Process quiz table name.
     */
    const DB_QUIZ = 'quiz';

    /**
     * Process quiz grades table name.
     */
    const DB_QUIZ_GRADES = 'quiz_grades';

    /**
     * Process lesson completion in timer table.
     */
    const DB_LESSON_TIMER = 'lesson_timer';

    /**
     * Get all active students from the database.
     *
     * @return {Object}[]          Array of database records.
     */
    public function get_active_students(int $courseid) {
        global $DB;

        $sql = 'SELECT u.id AS userid, ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname', '" "', 'u.alternatename') . ' AS fullname,
                    ud.data AS simulator, ue.timemodified AS enroldate,
                    en.courseid AS courseid, u.lastlogin AS lastlogin
                FROM {' . self::DB_USER . '} u
                INNER JOIN {' . self::DB_ROLE_ASSIGN . '} ra on u.id = ra.userid
                INNER JOIN {' . self::DB_ROLE . '} r on r.id = ra.roleid
                INNER JOIN {' . self::DB_USER_DATA . '} ud on ra.userid = ud.userid
                INNER JOIN {' . self::DB_USER_FIELD . '} uf on uf.id = ud.fieldid
                INNER JOIN {' . self::DB_USER_ENROL . '} ue on ud.userid = ue.userid
                INNER JOIN {' . self::DB_ENROL . '} en on ue.enrolid = en.id
                WHERE en.courseid = ' . $courseid . '
                    AND ra.contextid = ' . \context_course::instance($courseid)->id .'
                    AND r.shortname = "student"
                    AND uf.shortname = "simulator"
                    AND ue.status = 0
                    AND u.id NOT IN (
                        SELECT userid
                        FROM {' . self::DB_GROUPS_MEM . '} gm
                        INNER JOIN {' . self::DB_GROUPS . '} g on g.id = gm.groupid
                        WHERE g.name = "' . LOCAL_BOOKING_ONHOLDGROUP . '"
                        OR g.name = "' . LOCAL_BOOKING_GRADUATESGROUP . '"
                        )';

        return $DB->get_records_sql($sql);
    }

    /**
     * Get all active instructors for the course from the database.
     *
     * @return {Object}[]          Array of database records.
     */
    public function get_active_instructors(int $courseid) {
        global $DB;

        $sql = 'SELECT DISTINCT u.id AS userid, ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname', '" "', 'u.alternatename') . ' AS fullname,
                    ud.data AS simulator, ue.timemodified AS enroldate,
                    en.courseid AS courseid, u.lastlogin AS lastlogin
                FROM {' . self::DB_USER . '} u
                INNER JOIN {' . self::DB_ROLE_ASSIGN . '} ra on u.id = ra.userid
                INNER JOIN {' . self::DB_ROLE . '} r on r.id = ra.roleid
                INNER JOIN {' . self::DB_USER_ENROL . '} ue on ra.userid = ue.userid
                INNER JOIN {' . self::DB_ENROL . '} en on ue.enrolid = en.id
                INNER JOIN {' . self::DB_USER_DATA . '} ud on u.id = ud.userid
                INNER JOIN {' . self::DB_USER_FIELD . '} uf on uf.id = ud.fieldid
                WHERE en.courseid = ' . $courseid . '
                    AND ra.contextid = ' . \context_course::instance($courseid)->id .'
                    AND r.shortname IN ("instructor", "seniorinstructor", "flighttrainingmanager")
                    AND uf.shortname = "simulator"
                    AND ue.status = 0';

        return $DB->get_records_sql($sql);
    }

    /**
     * Get students assigned to an instructor from the database.
     *
     * @param int $courseid The course in context
     * @param int $userid   The instructor user id
     * @return {Object}[]   Array of database records.
     */
    public function get_assigned_students(int $courseid, int $userid) {
        global $DB;

        $sql = 'SELECT u.id AS userid, ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname', '" "', 'u.alternatename') . ' AS fullname,
                    ud.data AS simulator, ue.timemodified AS enroldate,
                    en.courseid AS courseid, u.lastlogin AS lastlogin
                FROM {' . self::DB_USER . '} u
                INNER JOIN {' . self::DB_ROLE_ASSIGN . '} ra on u.id = ra.userid
                INNER JOIN {' . self::DB_ROLE . '} r on r.id = ra.roleid
                INNER JOIN {' . self::DB_USER_DATA . '} ud on ra.userid = ud.userid
                INNER JOIN {' . self::DB_USER_FIELD . '} uf on uf.id = ud.fieldid
                INNER JOIN {' . self::DB_USER_ENROL . '} ue on ud.userid = ue.userid
                INNER JOIN {' . self::DB_ENROL . '} en on ue.enrolid = en.id
                INNER JOIN {' . self::DB_GROUPS_MEM . '} gm on ue.userid = gm.userid
                INNER JOIN {' . self::DB_GROUPS . '} g on g.id = gm.groupid
                WHERE en.courseid = ' . $courseid . '
                    AND ra.contextid = ' . \context_course::instance($courseid)->id .'
                    AND r.shortname = "student"
                    AND uf.shortname = "simulator"
                    AND ue.status = 0
                    AND g.courseid = ' . $courseid . '
                    AND g.name= "' . get_fullusername($userid, false) . '";';

        return $DB->get_records_sql($sql);
    }

    /**
     * Get grades for a specific student.
     *
     * @param int       $studentid  The student id.
     * @return grade[]  A student grades.
     */
    public function get_student_assignment_grades($studentid) {
        global $DB;

        // Get the student's grades
        $sql = 'SELECT cm.id AS exerciseid, ag.assignment AS assignid,
                    ag.userid, ag.grade, 0 AS totalgrade, ag.timemodified AS gradedate,
                    u.id AS instructorid, ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname', '" "', 'u.alternatename') . ' AS instructorname,
                    m.name AS exercisetype
                FROM {' . self::DB_GRADES . '} ag
                INNER JOIN {' . self::DB_COURSE_MODS . '} cm ON ag.assignment = cm.instance
                INNER JOIN {' . self::DB_MODULES . '} m ON m.id = cm.module
                INNER JOIN {' . self::DB_USER . '} u ON ag.grader = u.id
                WHERE m.name = "assign" AND ag.userid = ' . $studentid . '
                ORDER BY cm.section';

        return $DB->get_records_sql($sql);
    }

    /**
     * Get grades for a specific student.
     *
     * @param int       $studentid  The student id.
     * @return grade[]  A student quizes.
     */
    public function get_student_quizes_grades($studentid) {
        global $DB;

        // Get the student's grades
        $sql = 'SELECT cm.id AS exerciseid, qg.quiz AS assignid,
                    qg.userid, qg.grade, q.grade AS totalgrade,
                    qg.timemodified AS gradedate,
                    0 AS instructorid, \'\' AS instructorname,
                    m.name AS exercisetype
                FROM {' . self::DB_QUIZ_GRADES . '} qg
                INNER JOIN {' . self::DB_QUIZ . '} q on q.id = qg.quiz
                INNER JOIN {' . self::DB_COURSE_MODS . '} cm on qg.quiz = cm.instance
                INNER JOIN {' . self::DB_MODULES . '} as m ON m.id = cm.module
                WHERE m.name = \'quiz\' AND qg.userid = ' . $studentid . '
                ORDER BY cm.section;';

        return $DB->get_records_sql($sql);
    }

    /**
     * Get student's enrolment date.
     *
     * @param int       $studentid  The student id in reference
     * @return DateTime $enroldate  The enrolment date of the student.
     */
    public function get_enrol_date(int $courseid, int $studentid) {
        global $DB;

        $sql = 'SELECT ue.timecreated
                FROM {' . self::DB_USER_ENROL . '} ue
                INNER JOIN {' . self::DB_ENROL . '} e ON e.id = ue.enrolid
                WHERE ue.userid = ' . $studentid . '
                AND e.courseid = ' . $courseid;

        return $DB->get_record_sql($sql);
    }

    /**
     * Suspends the student's enrolment to a course.
     *
     * @param int   $courseid   The course the student is being unenrolled from.
     * @param int   $studentid  The student id in reference
     * @return bool             The result of the suspension action.
     */
    public function set_suspend_status(int $courseid, int $studentid) {
        global $DB;

        $sql = 'UPDATE {' . static::DB_USER_ENROL . '} ue
                INNER JOIN {' . static::DB_ENROL . '} e ON e.id = ue.enrolid
                SET ue.status = 1
                WHERE ue.userid = ' . $studentid . '
                AND e.courseid = ' . $courseid;

        return $DB->execute($sql);
    }

    /**
     * Returns full username
     *
     * @return object  The full participant username
     */
    public static function get_participant_name(int $userid) {
        global $DB;

        // Get the full user name
        $sql = 'SELECT ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname', '" "', 'u.alternatename') . ' AS fullname,
                    ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname') . ' AS username
                FROM {' . DB_USER . '} u
                WHERE u.id = ' . $userid;

        return $DB->get_record_sql($sql);
    }

    /**
     * Returns custom field value from the user's profile
     *
     * @param int $courseid         The course id
     * @param int $participantid    The user id
     * @param string $field         The field name associated with the rquested data
     * @return string               The full participatn username
     */
    public static function get_customfield_data(int $courseid, int $userid, string $field) {
        global $DB;

        // Look for ATO category
        $category = $DB->get_record('user_info_category', array('name'=>get_booking_config('ATO')));
        $categoryid = 0;

        if (empty($category)) {
            return '';
        } else {
            $categoryid = $category->id;
        }

        $sql = "SELECT uid.data
                FROM {" . self::DB_USER_DATA . "} uid
                INNER JOIN {" . self::DB_USER_FIELD . "} uif ON uif.id = uid.fieldid
                WHERE uif.shortname = '" . $field . "'
                AND uid.userid = " . $userid . "
                AND uif.categoryid = " . $categoryid;

        $customfieldobj = $DB->get_record_sql($sql);

        return $customfieldobj->data;
    }

    /**
     * Returns the timestamp of the last
     * graded session.
     *
     * @param   int The user id
     * @param   int The course id
     * @return  stdClass The record containing timestamp of the last grading
     */
    function get_last_graded_date($userid, $courseid) {
        global $DB;

        // Get the student's grades
        $sql = 'SELECT timemodified
                FROM {' . self::DB_GRADES . '} ag
                INNER JOIN {' . self::DB_COURSE_MODS . '} cm ON cm.instance = ag.assignment
                WHERE grader = ' . $userid . '
                AND cm.course = ' . $courseid . '
                ORDER BY timemodified DESC
                LIMIT 1';

        return $DB->get_record_sql($sql);
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
        global $DB;

        // Get the student's grades
        $sql = 'SELECT cm.id, cm.course, cm.module, cm.instance, cm.section
                FROM {' . self::DB_COURSE_MODS .'} cm
                INNER JOIN {' . self::DB_MODULES . '} as m ON m.id = cm.module
                WHERE cm.course = ' . $courseid . '
                AND cm.section <= ' . $nextexercisesection . '
                AND m.name = "lesson"
                AND cm.instance NOT IN (SELECT lt.lessonid
                                        FROM {' . self::DB_LESSON_TIMER . '} lt
                                        WHERE lt.userid = ' . $studentid . '
                                        AND lt.completed = ' . COMPLETION_COMPLETE . ')
                ORDER BY cm.section asc';

        $lessons_incomplete = $DB->get_records_sql($sql);

        return count($lessons_incomplete) == 0;
    }

    /**
     * Returns the next upcoming exercise id
     * for the student and its associated course section.
     *
     * @param   int     The course id
     * @param   int     The student id
     * @return  array   The next exercise id and associated course section
     */
    function get_next_student_exercise($courseid, $studentid) {
        global $DB;

        // Get first record of exercises not completed yet
        $sql = 'SELECT cm.id AS nextexerciseid, cm.section AS section
                FROM {' . self::DB_COURSE_MODS .'} cm
                INNER JOIN {' . self::DB_MODULES . '} m ON m.id = cm.module
                WHERE cm.course = ' . $courseid . '
                    AND m.name = "assign"
                    AND cm.instance NOT IN (SELECT ag.assignment
                                            FROM {' . self::DB_GRADES . '} ag
                                            WHERE ag.userid = ' . $studentid .')
                ORDER BY cm.section asc
                LIMIT 1';

        $rs = $DB->get_records_sql($sql);

        return [current($rs)->nextexerciseid, current($rs)->section];
    }
}