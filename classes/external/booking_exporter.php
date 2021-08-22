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

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use \local_booking\local\session\entities\action;
use \local_availability\local\slot\data_access\slot_vault;
use DateTime;

/**
 * Class for displaying instructor's booked sessions view.
 *
 * @package   local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_exporter extends exporter {

    /**
     * Constructor.
     *
     * @param mixed $data An array of student progress data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {
        $booking = $data['booking'];
        $action = new action('cancel', $booking->studentid, $booking->exerciseid);
        $sessiondate = $this->get_session_date($booking->slotid);

        $data = [
        'bookingid'   => $booking->id,
        'studentid'   => $booking->studentid,
        'studentname' => get_fullusername($booking->studentid),
        'exerciseid'  => $booking->exerciseid,
        'exercise'    => get_exercise_name($booking->exerciseid),
        'sessiondate' => $sessiondate->format('D M j'),
        'sessiontime' => $sessiondate->format('H:i'),
        'actionname'  => $action->get_name(),
        'actionurl'   => $action->get_url()->out(false),
        ];

        parent::__construct($data, $related);
    }

    protected static function define_properties() {
        return [
            'bookingid' => [
                'type' => PARAM_INT,
            ],
            'studentid' => [
                'type' => PARAM_INT,
            ],
            'studentname' => [
                'type' => PARAM_RAW,
            ],
            'exerciseid' => [
                'type' => PARAM_INT,
            ],
            'exercise' => [
                'type' => PARAM_RAW,
            ],
            'sessiondate' => [
                'type' => PARAM_RAW,
            ],
            'sessiontime' => [
                'type' => PARAM_RAW,
            ],
            'actionname' => [
                'type' => PARAM_RAW,
            ],
            'actionurl' => [
                'type' => PARAM_RAW,
            ],
        ];
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return array(
            'context' => 'context',
        );
    }


    /**
     * Returns a list of objects that are related.
     *
     * @return DateTime
     */
    protected static function get_session_date(int $slotid) {
        $vault = new slot_vault();
        $slot = $vault->get_slot($slotid);

        $sessiondate = new DateTime('@' . $slot->starttime);

        return $sessiondate;
    }
}
