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

namespace local_booking\local\logbook\entities;

use DateTime;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a log entry.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logentry implements logentry_interface {

    /**
     * @var int $id The id of this log entry.
     */
    protected $id;

    /**
     * @var int $exercise The course exercise id of this log entry.
     */
    protected $exercisid;

    /**
     * @var int $aricraft The flight aircraft typeof this log entry.
     */
    protected $aircraft;

    /**
     * @var int $flighttimemins The flight duration minutes of this log entry.
     */
    protected $flighttimemins;

    /**
     * @var int $soloflighttimemins The solo flight duration minutes of this log entry.
     */
    protected $soloflighttimemins;

    /**
     * @var int $sessiontimemins The session duration minutes of this log entry.
     */
    protected $sessiontimemins;

    /**
     * @var int $picid The user id of the primary in command of this log entry.
     */
    protected $picid;

    /**
     * @var int $sicid The user id of the secondary in command of this log entry.
     */
    protected $sicid;

    /**
     * @var string $pirep The PIREP of this log entry.
     */
    protected $pirep;

    /**
     * @var string $callsign The flight callsign of this log entry.
     */
    protected $callsign;

    /**
     * @var string $fromicao The flight departure airport ICAO of this log entry.
     */
    protected $fromicao;

    /**
     * @var string $toicao The flight arrival airport ICAO of this log entry.
     */
    protected $toicao;

    /**
     * @var DateTime $logentrydate The date timestamp of this log entry.
     */
    protected $logentrydate;

    /**
     * Get the id for the logbook entry.
     *
     * @param int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get the course exercise id for the log entry.
     *
     * @return int
     */
    public function get_exerciseid() {
        return $this->exerciseid;
    }

    /**
     * Get the aircraft typeof the log entry.
     *
     * @return int
     */
    public function get_aircraft() {
        return $this->aircraft;
    }

    /**
     * Get the flight duration minutes of the log entry.
     *
     * @return int
     */
    public function get_flighttimemins() {
        return $this->flighttimemins;
    }

    /**
     * Get the solo flight duration minutes of the log entry.
     *
     * @return int
     */
    public function get_soloflighttimemins() {
        return $this->soloflighttimemins;
    }

    /**
     * Get the session duration minutes of the log entry.
     *
     * @return int
     */
    public function get_sessiontimemins() {
        return $this->sessiontimemins;
    }

    /**
     * Get the pilot in command user id of the log entry.
     *
     * @return int
     */
    public function get_picid() {
        return $this->picid;
    }

    /**
     * Get the pilot in command name of the log entry.
     *
     * @return string
     */
    public function get_picname() {
        return get_fullusername($this->picid);
    }

    /**
     * Get the secondary in command user id of the log entry.
     *
     * @return int
     */
    public function get_sicid() {
        return $this->sicid;
    }

    /**
     * Set the secondary in command name of the log entry.
     *
     * @return string
     */
    public function get_sicname() {
        return get_fullusername($this->sicid);
    }

    /**
     * Get the PIREP string of log entry.
     *
     * @return string $pirep
     */
    public function get_pirep() {
        return $this->pirep;
    }

    /**
     * Get the flight callsign of the log entry.
     *
     * @return string
     */
    public function get_callsign() {
        return $this->callsign;
    }

    /**
     * Get the flight departure airport ICAO of the log entry.
     *
     * @return string
     */
    public function get_fromicao() {
        return $this->fromicao;
    }

    /**
     * Get the flight arrival airport ICAO of the log entry.
     *
     * @return string
     */
    public function get_toicao() {
        return $this->toicao;
    }

    /**
     * Get the date timestamp of the log entry.
     *
     * @return int
     */
    public function get_logentrydate() {
        return $this->logentrydate;
    }

    /**
     * Set the id for the logbook entry.
     *
     * @param int
     */
    public function set_id($id) {
        return $this->id;
    }

    /**
     * Set the course exercise id for the log entry.
     *
     * @param int $exerciseid
     */
    public function set_exerciseid(int $exerciseid) {
        $this->exercisid = $exerciseid;
    }

    /**
     * Set the aircraft typeof the log entry.
     *
     * @return string
     */
    public function set_aircraft(string $aircraft) {
        $this->aircraft = $aircraft;
    }

    /**
     * Set the flight duration minutes of the log entry.
     *
     * @param int
     */
    public function set_flighttimemins(int $flighttimemins) {
        $this->flightmins = $flighttimemins;
    }

    /**
     * Set the solo flight time duration minutes of the log entry.
     *
     * @param int
     */
    public function set_soloflighttimemins(int $soloflighttimemins) {
        $this->soloflighttimemins = $soloflighttimemins;
    }

    /**
     * Set the session duration minutes of the log entry.
     *
     * @param int
     */
    public function set_sessiontimemins(int $sessiontimemins) {
        $this->sessiontimemins = $sessiontimemins;
    }

    /**
     * Set the pilot in command user id of the log entry.
     *
     * @param int $picid
     */
    public function set_picid(int $picid) {
        $this->picid = $picid;
    }

    /**
     * Set the pilot in command name of the log entry.
     *
     * @param string $picname
     */
    public function set_picname(string $picname) {
        $this->picname = $picname;
    }

    /**
     * Set the secondary in command user id of the log entry.
     *
     * @param int $sicid
     */
    public function set_sicid(int $sicid) {
        $this->sicid = $sicid;
    }

    /**
     * Set the secondary in command name of the log entry.
     *
     * @param string $sicname
     */
    public function set_sicname(string $sicname) {
        $this->sicname = $sicname;
    }

    /**
     * Set the PIREP string of log entry.
     *
     * @param string $pirep
     */
    public function set_pirep(string $pirep) {
        $this->pirep = $pirep;
    }

    /**
     * Set the flight callsign of the log entry.
     *
     * @param string $callsign
     */
    public function set_callsign(string $callsign) {
        $this->callsign = $callsign;
    }

    /**
     * Set the flight departure airport ICAO of the log entry.
     *
     * @param string $fromicao
     */
    public function set_fromicao(string $fromicao) {
        $this->fromicao = $fromicao;
    }

    /**
     * Set the flight arrival airport ICAO of the log entry.
     *
     * @return string
     */
    public function set_toicao(string $toicao) {
        $this->toicao = $toicao;
    }

    /**
     * Set the date timestamp of the log entry.
     *
     * @param DateTime $logentrydate
     */
    public function set_logentrydate(DateTime $logentrydate) {
        $this->logentrydate = $logentrydate;
    }
}
