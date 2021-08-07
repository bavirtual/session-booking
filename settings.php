<?php
// This file is part of Moodle - https://moodle.org/
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
 * Adds admin settings for the plugin.
 *
 * @package     local_booking
 * @category    admin
 * @copyright   2021 Mustafa Hajjar
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// if ($hassiteconfig) {
//     $ADMIN->add('localplugins', new admin_category('local_booking_settings', new lang_string('pluginname', 'local_booking')));
//     $settingspage = new admin_settingpage('managelocalbooking', new lang_string('pluginname', 'local_booking'));

//     if ($ADMIN->fulltree) {
//         // hours in the day 24-hour format
//         $options = array();
//         for ($i = 0; $i <= 23; $i++) {
//             $options[] = substr('00'. $i, -2).':00';
//         }
//         // first allowable session time
//         $settingspage->add(new admin_setting_configselect('local_booking/firstsession',
//             new lang_string('firstsession', 'local_booking'), new lang_string('firstsessiondesc', 'local_booking'),
//             8, $options)
//         );
//         // last allowable session time
//         $settingspage->add(new admin_setting_configselect('local_booking/lastsession',
//             new lang_string('lastsession', 'local_booking'), new lang_string('lastsessiondesc', 'local_booking'),
//             23, $options)
//         );

//         // booking recording weeks ahead
//         $settingspage->add(new admin_setting_configtext('local_booking/weeksahead',
//             new lang_string('weeksahead', 'local_booking'), new lang_string('weeksaheaddesc', 'local_booking'),
//             3, PARAM_INT)
//         );

//         // show local time next to Zulu time
//         $settingspage->add(new admin_setting_configcheckbox('local_booking/showlocaltime',
//             new lang_string('showlocaltime', 'local_booking'), new lang_string('showlocaltimedesc', 'local_booking'),
//             1)
//         );
//     }

//     $ADMIN->add('localplugins', $settingspage);
// }