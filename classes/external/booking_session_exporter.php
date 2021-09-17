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

use renderer_base;
use core\external\exporter;
use local_booking\local\slot\data_access\slot_vault;
use local_booking\local\session\entities\session;
use local_booking\local\session\entities\grade;

/**
 * Class for displaying each session in progression view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_session_exporter extends exporter {

    /**
     * @var /strClass $session An object containing session info.
     */
    protected $session;

    /**
     * @var grade $this->grade An object containing grade info.
     */
    protected $grade;

    /**
     * Constructor.
     *
     * @param mixed $data An array of exercise data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {
        $type = \core_calendar\type_factory::get_calendar_instance();

        $this->grade = null;
        $booking = null;

        // Get student's grade for this session if available
        if (count($data['grades']) > 0) {
            if (array_search($data['exerciseid'], array_column($data['grades'], 'exerciseid')) !== false) {
                $this->grade = new grade(
                        $data['exerciseid'],
                        $data['grades'][$data['exerciseid']]->exercisetype,
                        $data['grades'][$data['exerciseid']]->instructorid,
                        $data['grades'][$data['exerciseid']]->instructorname,
                        $data['studentid'],
                        $data['studentname'],
                        $type->timestamp_to_date_array($data['grades'][$data['exerciseid']]->gradedate),
                        $data['grades'][$data['exerciseid']]->grade);
            }
        }

        // Get the student's booking for this session if available
        if (empty($this->grade) && count($data['booking']) > 0) {
            $bookings = array_reverse($data['booking']);
            $bookingobj = array_pop($bookings);
            if ($bookingobj->exerciseid == $data['exerciseid']) {
                $booking = $bookingobj;
            }
        }

        // collect session information
        $sessionstatus = '';
        $sessionstatustooltip = '';
        $sessiondate = new \DateTime('@' . time());
        if ($this->grade !== null) {
            $sessionstatus = 'graded';
            $sessiondate = new \DateTime('@' . $this->grade->get_gradedate()[0]);
            $this->gradeinfo = [
                'instructor'  => $this->grade->get_gradername(),
                'sessiondate' => $sessiondate->format('j M \'y'),
                'grade'       => intval($this->grade->get_finalgrade())
            ];
            $sessionstatustooltip = $this->grade->get_exercisetype() == 'assign' ? get_string('sessiongradeddby', 'local_booking', $this->gradeinfo) :
                get_string('sessiongradeexampass', 'local_booking', $this->gradeinfo);
        } else if ($booking !== null) {
            $slotvault = new slot_vault();
            $sessionstatus = $booking->confirmed ? 'booked' : 'tentative';
            $infostatus = $booking->confirmed ? 'statusbooked' : 'statustentative';
            $sessiondate = $slotvault->get_session_date($booking->slotid);
            $bookinginfo = [
                'instructor'    => get_fullusername($booking->userid),
                'sessiondate'   => !empty($sessiondate) ? $sessiondate->format('j M \'y') : 'null',
                'bookingstatus' => ucwords(get_string($infostatus, 'local_booking')),
            ];
            $sessionstatustooltip = get_string('sessionbookedby', 'local_booking', $bookinginfo);
        }

        $this->session = new session($this->grade, $booking, $sessionstatus, $sessiondate);

        $data = [
            'studentid'     => $data['studentid'],
            'exerciseid'    => $data['exerciseid'],
            'sessionstatus' => $sessionstatus,
            'sessiondate'   => !$this->session->empty() ? (!empty($sessiondate) ? $sessiondate->format('j M \'y') : 'null') : '',
            'sessionts'     => !$this->session->empty() ? (!empty($sessiondate) ? $sessiondate->getTimestamp() : 0) : 0,
            'sessionempty'  => $this->session->empty(),
            'sessionstatustooltip'  => $sessionstatustooltip,
            'logentryid'    => !empty($data['logentry']) ? $data['logentry']->get_id() : 0,
        ];
        parent::__construct($data, $related);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'studentid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'exerciseid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'sessionstatus' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'sessiondate' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'sessionts' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'sessionempty' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
            'sessionstatustooltip' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'logentryid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }
    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'graded' => [
                'type' => PARAM_BOOL,
            ],
            'booked' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'tentative' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'haslogentry' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
            'logentrymissing' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(renderer_base $output) {

        $return = [
            'graded'        => $this->session->hasgrade(),
            'booked'        => $this->session->hasbooking() && $this->session->get_booking()->confirmed,
            'tentative'     => $this->session->hasbooking() && !$this->session->get_booking()->confirmed,
            'haslogentry'   => $this->session->hasgrade() && $this->grade->get_exercisetype() != 'quiz',
            'logentrymissing' => empty($this->data['logentryid']) && $this->session->hasgrade() && $this->grade->get_exercisetype() != 'quiz'
        ];

        return $return;
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
}
