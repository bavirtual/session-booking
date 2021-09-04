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
 * The mform for updating a logbook entry
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\logbook\forms;

defined('MOODLE_INTERNAL') || die();

 /**
  * Always include formslib
  */
require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The mform class for creating and editing a logbook entry
 *
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update extends create {
    /**
     * Add the repeat elements for the form when editing an existing event.
     *
     * @param MoodleQuickForm $mform
     */
    protected function add_event_repeat_elements($mform) {
        $logentry = $this->_customdata['logentry'];

        $mform->addElement('hidden', 'repeatid');
        $mform->setType('repeatid', PARAM_INT);

        if (!empty($logentry->repeatid)) {
            $group = [];
            $group[] = $mform->createElement('radio', 'repeateditall', null, get_string('repeateditall', 'calendar',
                    $logentry->eventrepeats), 1);
            $group[] = $mform->createElement('radio', 'repeateditall', null, get_string('repeateditthis', 'calendar'), 0);
            $mform->addGroup($group, 'repeatgroup', get_string('repeatedevents', 'calendar'), '<br />', false);

            $mform->setDefault('repeateditall', 1);
            $mform->setAdvanced('repeatgroup');
        }
    }
}
