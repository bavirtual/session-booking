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
 * This module is responsible for handling progression booking activity
 *
 * @module     local_booking/booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
            'jquery',
            'local_booking/view_manager',
            'local_booking/logbook_actions',
            'local_booking/events',
            'local_booking/selectors',
        ],
        function(
            $,
            ViewManager,
            LogbookActions,
            LogbookEvents,
            Selectors,
        ) {

    var SELECTORS = {
        PROGRESSION_WRAPPER: ".progressionwrapper",
        CANCEL_BUTTON: "[data-region='cancel-button']",
        SESSION_ENTRY: "[data-region='session-entry']",
    };

    /**
     * Listen to and handle any calendar events fired by the calendar UI.
     *
     * @method registerBookingEventListeners
     * @param {object} root The calendar root element
     * @param {object} logentryFormModalPromise A promise reolved with the event form modal
     */
     var registerBookingEventListeners = function(root, logentryFormModalPromise) {
        var body = $('body');

        body.on(LogbookEvents.created, function() {
            ViewManager.reloadCurrentMonth(root);
        });
        body.on(LogbookEvents.updated, function() {
            ViewManager.reloadCurrentMonth(root);
        });

        LogbookActions.registerEditListeners(root, logentryFormModalPromise);
    };

    /**
     * Register event listeners for the module.
     *
     * @param {object} root The calendar root element
     */
     var registerEventListeners = function(root) {

        var eventFormPromise = LogbookActions.registerLogentryFormModal(root),
            contextId = $(SELECTORS.PROGRESSION_WRAPPER).data('context-id');
        registerBookingEventListeners(root, eventFormPromise);

        if (contextId) {
            // Listen the click on the progression table of sessions.
            root.on('click', SELECTORS.SESSION_ENTRY, function(e) {

                var target = $(e.target);

                var sessionDate = $(this).attr('data-session-date');
                var studentId = $(this).attr('data-student-id');
                eventFormPromise.then(function (modal) {
                    var wrapper = target.closest(Selectors.progressionwrapper);
                    modal.setCourseId(wrapper.data('courseid'));

                    var exerciseId = wrapper.data('exerciseid');
                    if (typeof exerciseId !== 'undefined') {
                        modal.setExerciseId(exerciseId);
                    }

                    modal.setContextId(wrapper.data('context-id'));
                    modal.setStudentId(studentId);
                    modal.setSessionDate(sessionDate);
                    modal.show();
                    return;
                })
                .fail(Notification.exception);

                e.preventDefault();
            });
        }

        // Listen the click on the Cancel booking buttons.
        root.on('click', SELECTORS.CANCEL_BUTTON, function(e) {
            // eslint-disable-next-line no-alert
            if (confirm('Cancel booked session?')) {
                ViewManager.cancelBooking(root, e);
            }
        });
    };

    return {
        init: function(root) {
            root = $(root);
            registerEventListeners(root);
        }
    };
});
