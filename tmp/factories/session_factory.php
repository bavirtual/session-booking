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
 * Session factory class.
 *
 */
class session_factory extends event_abstract_factory {

    protected function apply_component_action(event_interface $event) {
        $callbackapplier = $this->actioncallbackapplier;
        $callbackresult = $callbackapplier($event);

        if (!$callbackresult instanceof event_interface) {
            throw new invalid_callback_exception(
                'Event factory action callback applier must return an instance of event_interface');
        }

        return $callbackresult;
    }

    protected function expose_event(event_interface $event) {
        $callbackapplier = $this->visibilitycallbackapplier;
        $callbackresult = $callbackapplier($event);

        if (!is_bool($callbackresult)) {
            throw new invalid_callback_exception('Event factory visibility callback applier must return true or false');
        }

        return $callbackresult === true ? $event : null;
    }
}
