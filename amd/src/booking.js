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
 * Improvised from core_calendar.
 *
 * @module     local_booking/booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
            'jquery',
            'core/str',
            'core/pending',
            'local_booking/view_manager',
            'local_booking/booking_actions',
            'local_booking/events',
            'local_booking/selectors'
        ],
        function(
            $,
            Str,
            Pending,
            ViewManager,
            BookingActions,
            BookingEvents,
            BookingsSelectors
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

        body.on(BookingEvents.canceled, function() {
            ViewManager.refreshProgressionContent(root);
            ViewManager.refreshMyBookingsContent(root);
        });
        body.on(BookingEvents.created, function() {
            ViewManager.refreshProgressionContent(root);
        });
        body.on(BookingEvents.updated, function() {
            ViewManager.refreshProgressionContent(root);
        });

        if (logentryFormModalPromise !== 'undefined') {
            BookingActions.registerEditListeners(root, logentryFormModalPromise);
        }
    };

    /**
     * Register event listeners for the module.
     *
     * @param {object} root The calendar root element
     */
     var registerEventListeners = function(root) {

        var eventFormPromise = BookingActions.registerLogentryFormModal(root),
            contextId = $(SELECTORS.PROGRESSION_WRAPPER).data('context-id'),
            courseId = $(SELECTORS.PROGRESSION_WRAPPER).data('courseid');
        registerBookingEventListeners(root, eventFormPromise);

        if (contextId) {
            // Listen the click on the progression table of sessions.
            root.on('click', SELECTORS.SESSION_ENTRY, function(e) {
                var sessionDate = $(this).attr('data-session-date');
                var studentId = $(this).attr('data-student-id');
                var exerciseId = $(this).attr('data-exercise-id');
                var logentryId = $(this).attr('data-logentry-id');

                if (logentryId == 0) {
                    eventFormPromise.then(function(modal) {
                        modal.setContextId(contextId);
                        modal.setCourseId(courseId);
                        modal.setStudentId(studentId);
                        modal.setExerciseId(exerciseId);
                        modal.setLogentryId(logentryId);
                        modal.setSessionDate(sessionDate);
                        modal.show();
                        return;
                    })
                    .fail(Notification.exception);

                    e.preventDefault();
                } else {
                    let gradedSession = null;
                    let logentryId = null;
                    const target = e.target;
                    const pendingPromise = new Pending('local_booking/view_manager:logentryLink:click');

                    if (target.matches(BookingsSelectors.actions.viewEvent)) {
                        gradedSession = target;
                    } else {
                        gradedSession = target.closest(BookingsSelectors.actions.viewEvent);
                    }

                    if (gradedSession) {
                        logentryId = gradedSession.dataset.logentryId;
                    } else {
                        logentryId = target.querySelector(BookingsSelectors.actions.viewEvent).dataset.logentryId;
                    }

                    if (logentryId) {
                        // A link was found. Show the modal.

                        e.preventDefault();
                        // We've handled the event so stop it from bubbling
                        // and causing the day click handler to fire.
                        e.stopPropagation();

                        ViewManager.renderLogentrySummaryModal(logentryId, courseId, studentId)
                        .then(pendingPromise.resolve)
                        .catch();
                    } else {
                        pendingPromise.resolve();
                    }

                }
            });
        }

        // Listen the click on the Cancel booking buttons.
        root.on('click', SELECTORS.CANCEL_BUTTON, function(e) {
            // eslint-disable-next-line no-alert
            Str.get_string('cancellationcomment', 'local_booking').then(function(promptMsg) {
                var comment = prompt(promptMsg);
                if (comment !== null) {
                    BookingActions.cancelBooking(root, e, comment);
                }
                return;
            }).catch(Notification.exception);
        });
    };

    return {
        init: function(root) {
            root = $(root);
            registerEventListeners(root);
        }
    };
});
