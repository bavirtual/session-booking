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
 * This module is responsible for handle calendar day and upcoming view.
 * Improvised from core_calendar.
 *
 * @module     local_booking/calendar_view
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
        'jquery',
        'core/notification',
        'local_booking/selectors',
        'local_booking/events',
        'local_booking/view_manager',
        'local_booking/booking_actions'
    ],
    function(
        $,
        Notification,
        Selectors,
        BookingEvents,
        ViewManager,
        BookingActions
    ) {

        var registerEventListeners = function(root, type) {
            var body = $('body');

            var reloadFunction = 'reloadCurrent' + type.charAt(0).toUpperCase() + type.slice(1);

            body.on(BookingEvents.created, function() {
                ViewManager[reloadFunction](root);
            });
            body.on(BookingEvents.deleted, function() {
                ViewManager[reloadFunction](root);
            });
            body.on(BookingEvents.updated, function() {
                ViewManager[reloadFunction](root);
            });

            var eventFormPromise = BookingActions.registerLogentryFormModal(root);
            BookingActions.registerEditListeners(root, eventFormPromise);
        };

        return {
            init: function(root, type) {
                root = $(root);

                ViewManager.init(root, type);
                registerEventListeners(root, type);
            }
        };
    });
