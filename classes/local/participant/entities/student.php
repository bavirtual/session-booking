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
 * Class representing all student course participants
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\participant\entities;

use DateTime;
use moodle_exception;
use local_booking\local\slot\data_access\slot_vault;
use local_booking\local\slot\entities\slot;

class student extends participant {

    /**
     * Process user enrollments table name.
     */
    const SLOT_COLOR = '#00e676';

    /**
     * @var array $slots The student posted timeslots.
     */
    protected $slots;

    /**
     * @var string $slotcolor The slot color for the student slots.
     */
    protected $slotcolor;

    /**
     * @var string $nextlesson The student's next upcoming lesson.
     */
    protected $nextlesson;

    /**
     * Constructor.
     *
     * @param int $courseid The course id.
     * @param int $studentid The student id.
     */
    public function __construct(int $courseid, int $studentid, string $studentname = '', int $enroldate = 0) {
        parent::__construct($courseid, $studentid);
        $this->username = $studentname;
        $this->enroldate = $enroldate;
        $this->slotcolor = self::SLOT_COLOR;
    }

    /**
     * Save a student list of slots
     *
     * @param array $params The year, and week.
     * @return bool $result The result of the save transaction.
     */
    public function save_slots(array $params) {
        global $DB;
        $vault = new slot_vault();

        $slots = $params['slots'];
        $year = $params['year'];
        $week = $params['week'];

        // start transaction
        $transaction = $DB->start_delegated_transaction();

        // remove all week/year slots for the user to avoid updates
        $result = $vault->delete_slots($this->courseid, $year, $week, $this->userid);

        if ($result) {
            foreach ($slots as $slot) {
                $newslot = new slot(0,
                    $this->userid,
                    $this->courseid,
                    $slot['starttime'],
                    $slot['endtime'],
                    $year,
                    $week,
                );

                // add each slot.
                $result = $result && $vault->save_slot($newslot);
            }
        }

        if ($result) {
            $transaction->allow_commit();
        } else {
            $transaction->rollback(new moodle_exception(get_string('slotssaveunable', 'local_booking')));
        }

        return $result;
    }

    /**
     * Delete student slots
     *
     * @param array $params The year, and week.
     * @return bool $result The result of the save transaction.
     */
    public function delete_slots(array $params) {
        global $DB;
        $vault = new slot_vault();

        $year = $params['year'];
        $week = $params['week'];

        // start transaction
        $transaction = $DB->start_delegated_transaction();

        // remove all week/year slots for the user to avoid updates
        $result = $vault->delete_slots($this->courseid, $this->userid, $year, $week);
        if ($result) {
            $transaction->allow_commit();
        } else {
            $transaction->rollback(new moodle_exception(get_string('slotsdeleteunable', 'local_booking')));
        }

        return $result;
    }

    /**
     * Get grades for a specific student from
     * assignments and quizes.
     *
     * @return {object}[]  A student grades.
     */
    public function get_grades() {
        // join both graded assignments and attempted quizes into one grades array
        $assignments = $this->vault->get_student_assignment_grades($this->userid);
        $quizes = $this->vault->get_student_quizes_grades($this->userid);
        $grades = $assignments + $quizes;

        return $grades;
    }

    /**
     * Return student slots for a particular week/year.
     *
     * @return array array of days
     */
    public function get_slots($weekno, $year) {
        $slotvault = new slot_vault();
        $this->slots = $slotvault->get_slots($this->userid, $weekno, $year);

        // add student's slot color to each slot
        foreach ($this->slots as $slot) {
            $slot->slotcolor = $this->slotcolor;
        }
        return $this->slots;
    }

    /**
     * Get student slots color.
     *
     * @return string $slotcolor;
     */
    public function get_slotcolor() {
        return $this->slotcolor;
    }

    /**
     * Return student's next lesson.
     *
     * @return string $nextlesson
     */
    public function get_next_lesson() {
        return $this->nextlesson;
    }

    /**
     * Returns the timestamp of the first
     * nonbooked availability slot for
     * the student.
     *
     * @return  DateTime
     */
    public function get_first_slot_date() {
        $vault = new slot_vault();

        $firstsession = $vault->get_first_posted_slot($this->userid);
        $sessiondatets = !empty($firstsession) ? $firstsession->starttime : time();
        $sessiondate = new DateTime('@' . $sessiondatets);

        return $sessiondate;
    }

    /**
     * Returns whether the student complete
     * all sessons prior to the upcoming next
     * exercise.
     *
     * @param   int     The upcoming next exercise id
     * @return  bool    Whether the lessones were completed or not.
     */
    public function get_lessons_complete($nextexercisesection) {
        return $this->vault->get_lessons_complete($this->userid, $this->courseid, $nextexercisesection);
    }

    /**
     * Returns the next upcoming exercise id
     * for the student and its associated course section.
     *
     * @return int The next exercise id and associated course section
     */
    public function get_next_exercise() {
        return $this->vault->get_next_student_exercise($this->courseid, $this->userid);
    }

    /**
     * Set the student's slot color.
     *
     * @param string $slotcolor
     */
    public function set_slot_color(string $slotcolor) {
        $this->slotcolor = $slotcolor;
    }

    /**
     * Set the student's next lesson.
     *
     * @param string $nextlesson
     */
    public function set_next_lesson(string $nextlesson) {
        $this->nextlesson = $nextlesson;
    }
}