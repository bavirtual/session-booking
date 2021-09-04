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
 * Contains logentry class for displaying a logbook entry form.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/booking/lib.php");

use renderer_base;
use core\external\exporter;
use moodle_url;

/**
 * Class for displaying a logbook entry.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logentry_exporter extends exporter {

    /**
     * @var logentry_interface $logentry
     */
    protected $logentry;

    /**
     * Constructor.
     *
     * @param array $data       The form data.
     * @param array $related    The related data.
     */
    public function __construct($data, $related = []) {
        parent::__construct($data, $related);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'id' => [
                'type' => PARAM_INT
            ],
            'studentid' => [
                'type' => PARAM_INT
            ],
            'studentname' => [
                'type' => PARAM_RAW
            ],
            'flightdate' => [
                'type' => PARAM_INT
            ],
            'flightmins' => [
                'type' => PARAM_INT
            ],
            'solomins' => [
                'type' => PARAM_INT
            ],
            'sessionmins' => [
                'type' => PARAM_INT
            ],
            'picname' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'sicname' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'aircraft' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'pirep' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'callsign' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'fromicao' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'toicao' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'visible' => [
                'type' => PARAM_INT
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
            'url' => [
                'type' => PARAM_URL,
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
        $values['url'] = (new moodle_url('\booking\view'))->out(false);

        return $this->related;
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return [
            'context' => 'context',
            'courseid' => 'courseid',
            'exerciseid' => 'exerciseid',
        ];
    }
}
