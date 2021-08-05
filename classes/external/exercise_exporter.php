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

/**
 * Class for displaying the day on month view.
 *
 * @package   core_calendar
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exercise_exporter extends exporter {

    /**
     * Constructor.
     *
     * @param mixed $data An array of exercise data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {
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

        return [
            'popovertitle'  => $this->get_popover_title(),
            'graded'        => $this->data['graded'],
            'booked'        => $this->get_booking(),
            'tentative'     => $this->get_booking(),
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
     * Get the title for this popover.
     *
     * @return string
     */
    protected function get_booking() {

        return true;
    }

    /**
     * Get the title for this popover.
     *
     * @return string
     */
    protected function get_popover_title() {
        $title = null;

        if ($this->data->instructorname !== '') {
            $title = $this->data->booked ? get_string('sessionbookedby', 'local_booking') :
                get_string('sessiongradededby', 'local_booking') . ' ' . $this->data->instructorfullname;
        }

        return $title;
    }
}
