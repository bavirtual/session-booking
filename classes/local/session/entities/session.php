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

namespace local_booking\local\session\entities;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a course exercise session.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session implements session_interface {

    /**
     * @var grade $graded The session grade object.
     */
    protected $grade;

    /**
     * @var booking $booking The session booking object.
     */
    protected $booking;

    /**
     * @var string $status The session status.
     */
    protected $status;

    /**
     * @var array $sessiondate The date of this session.
     */
    protected $sessiondate;

    /**
     * Constructor.
     *
     * @param grade             $grade          The session grade object.
     * @param booking           $booking        The session booking object.
     * @param string            $status         The session status.
     * @param array             $sessiondate    The date of this session.
     */
    public function __construct(
        $grade = null,
        $booking = null,
        $status = '',
        $sessiondate = []
    ) {
        $this->grade = $grade;
        $this->booking = $booking;
        $this->status = $status;
        $this->sessiondate = $sessiondate;
    }

    public function get_grade() {
        return $this->grade;
    }

    public function get_booking() {
        return $this->booking;
    }

    public function get_status() {
        return $this->status;
    }

    public function get_sessiondate() {
        return $this->sessiondate;
    }

    public function hasgrade() {
        return $this->grade !== null;
    }

    public function hasbooking() {
        return $this->booking !== null;
    }

    public function empty() {
        return (!$this->hasbooking() && !$this->hasgrade());
    }
}
