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
 * Contains event class for displaying the day on month view.
 *
 * @package   local_booking
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use core\external\exporter;
use local_booking\local\session\entities\session;
use local_booking\local\session\entities\grade;
use local_booking\local\session\entities\booking;

/**
 * Class for displaying the day on month view.
 *
 * @package   core_calendar
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session_exporter extends exporter {

    /**
     * @var /strClass $session An object containing session info.
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param mixed $data An array of exercise data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {
        $type = \core_calendar\type_factory::get_calendar_instance();

// $data['grades'][1]->instructorid,
        $grade = null;
        if (count($data['grades']) > 0) {
            if (array_search($data['exerciseid'], array_column($data['grades'], 'exerciseid')) !== false) {
                $grade = new grade(
                        $data['exerciseid'],
                        $data['grades'][1]->instructorid,
                        $data['grades'][1]->instructorname,
                        $data['studentid'],
                        $data['studentname'],
                        $type->timestamp_to_date_array($data['grades'][1]->timemodified),
                        $data['grades'][1]->grade);
            }
        }

        $booking = null;

        $sessionstatus = $grade !== null ? 'graded' : ($booking !== null ? $booking->get_status() : '');
        $timestamp = $grade !== null ? $grade->get_gradedate()[0] : ($booking !== null ? $booking->get_bookingdate()[0] : time());
        $sessiondate = new \DateTime('@' . $timestamp);

        $this->session = new session($grade, $booking, $sessionstatus, $sessiondate);
        $sessionempty = $this->session->empty();

        $data = [
            'sessionstatus' => $sessionstatus,
            'sessiondate'   => $sessiondate->format('d/m/y'),
            'sessionempty'  => $sessionempty,
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
            'sessionstatus' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'sessiondate' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'sessionempty' => [
                'type' => PARAM_BOOL,
                'default' => true,
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
            'popovertitle' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
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
            'popovertitle'  => $this->get_popover_title(),
            'graded'        => $this->session->hasgrade(),
            'booked'        => $this->session->hasbooking() && $this->session->booking->confirmed(),
            'tentative'     => $this->session->hasbooking() && !$this->session->booking->confirmed(),
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

    /**
     * Get the title for this popover.
     *
     * @return string
     */
    protected function get_popover_title() {
        $title = null;

        if (!$this->session->empty()) {
            if ($this->session->hasgrade()) {
                $title = get_string('sessiongradeddby', 'local_booking') . ' ' . $this->session->get_grade()->get_gradername();
            } else if ($this->session->hasbooking()) {
                $title = get_string('sessionbookedby', 'local_booking') . ' ' . $this->session->get_booking()->get_instructorname();
            }
        }

        return $title;
    }
}
