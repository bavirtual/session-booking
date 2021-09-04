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

namespace local_booking\local\logbook\data_access;

use DateTime;
use local_booking\local\logbook\entities\logentry;

class logbook_vault implements logbook_vault_interface {

    /** Bookings table name for the persistent. */
    const DB_LOGBOOKS = 'local_booking_logbooks';

    /**
     * Get a student's logbook.
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $studentid  The student id associated with the logbook.
     * @param int $studentid    $studentid of the student for this logbook entry.
     * @return logentries[]     Array of logentry_interfaces.
     */
    public function get_logbook(int $courseid, int $studentid) {
        global $DB;

        $logbook = [];
        $logentryrecs = $DB->get_records(static::DB_LOGBOOKS, ['userid'=>$studentid]);
        foreach ($logentryrecs as $logentryrec) {
            $logentry = new logentry();
            $logentry->set_id($logentryrec->id);
            $logentry->set_exerciseid($logentryrec->exerciseid);
            $logentry->set_aircraft($logentryrec->aircraft);
            $logentry->set_flighttimemins($logentryrec->flighttimemins);
            $logentry->set_soloflighttimemins($logentryrec->soloflighttimemins);
            $logentry->set_sessiontimemins($logentryrec->sessiontimemins);
            $logentry->set_picid($logentryrec->picid);
            $logentry->set_picname((!empty($logentryrec->picid) ? get_fullusername($logentryrec->picid) : ''));
            $logentry->set_sicid($logentryrec->sicid);
            $logentry->set_sicname((!empty($logentryrec->sicid) ? get_fullusername($logentryrec->sicid) : ''));
            $logentry->set_pirep($logentryrec->pirep);
            $logentry->set_fromicao($logentryrec->fromicao);
            $logentry->set_toicao($logentryrec->toicao);
            $logentry->set_logentrydate(new DateTime('@' . $logentryrec->timemodified));

            $logbook[] = $logentry;
        }

        return $logbook;
    }

    /**
     * Get a specific logbook entry.
     *
     * @param int $courseid     The course id.
     * @param int $student      The student id.
     * @param int $logentry     The logentry id.
     * @return logentry         A logentry_insterface.
     */
    public function get_logentry(int $courseid, int $studentid, int $logentryid) {
        global $DB;

        $logentryrec = $DB->get_record(static::DB_LOGBOOKS, ['id'=>$logentryid]);

        $logentry = new logentry();
        $logentry->set_id($logentryrec->id);
        $logentry->set_exerciseid($logentryrec->exerciseid);
        $logentry->set_aircraft($logentryrec->aircraft);
        $logentry->set_flighttimemins($logentryrec->flighttimemins);
        $logentry->set_soloflighttimemins($logentryrec->soloflighttimemins);
        $logentry->set_sessiontimemins($logentryrec->sessiontimemins);
        $logentry->set_picid($logentryrec->picid);
        $logentry->set_picname((!empty($logentryrec->picid) ? get_fullusername($logentryrec->picid) : ''));
        $logentry->set_sicid($logentryrec->sicid);
        $logentry->set_sicname((!empty($logentryrec->sicid) ? get_fullusername($logentryrec->sicid) : ''));
        $logentry->set_pirep($logentryrec->pirep);
        $logentry->set_fromicao($logentryrec->fromicao);
        $logentry->set_toicao($logentryrec->toicao);
        $logentry->set_logentrydate(new DateTime('@' . $logentryrec->timemodified));

        return $logentry;
    }

    /**
     * Create a student's logbook entry
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $studentid  The student id associated with the logbook.
     * @param logentry  $logentry   A logbook entry of the student.
     * @return int      The log entry id.
     */
    public function create_logentry(int $courseid, int $studentid, logentry $logentry) {
        global $DB;

        return $DB->insert_record(static::DB_LOGBOOKS, $logentry);
    }

    /**
     * Update a student's logbook entry
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $studentid  The student id associated with the logbook.
     * @param logentry  $logentry A logbook entry of the student.
     * @return bool     result of the database update operation.
     */
    public function update_logentry(int $courseid, int $studentid, logentry $logentry){
        global $DB;

        return $DB->update_record(static::DB_LOGBOOKS, $logentry);
    }
}