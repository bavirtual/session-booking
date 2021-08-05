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
 * This module is the highest level module for the calendar. It is
 * responsible for initialising all of the components required for
 * the calendar to run. It also coordinates the interaction between
 * components by listening for and responding to different events
 * triggered within the calendar UI.
 *
 * @module     local_booking/calendar
 * @copyright  2017 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
            'jquery',
            'core/ajax',
            'core/str',
            'core/templates',
            'core/notification',
            'core/custom_interaction_events',
            'local_booking/repository',
            'local_booking/events',
            'local_booking/view_manager',
            'local_booking/slot_actions',
        ],
        function(
            $,
            Ajax,
            Str,
            Templates,
            Notification,
            CustomEvents,
            CalendarRepository,
            CalendarEvents,
            BookingViewManager,
            SlotActions,
        ) {

    var SELECTORS = {
        ROOT: "[data-region='calendar']",
        DAY: "[data-region='day']",
        SAVE_BUTTON: "[data-region='save-button']",
        COPY_BUTTON: "[data-region='copy-button']",
        PASTE_BUTTON: "[data-region='paste-button']",
        CLEAR_BUTTON: "[data-region='clear-button']",
        LOADING_ICON_CONTAINER: '[data-region="loading-icon-container"]',
        NEW_EVENT_BUTTON: "[data-action='new-event-button']",
        DAY_CONTENT: "[data-region='day-content']",
        LOADING_ICON: '.loading-icon',
        VIEW_DAY_LINK: "[data-action='view-day-link']",
        CALENDAR_MONTH_WRAPPER: ".calendarwrapper",
        TODAY: '.today',
    };

    /**
     * Register event listeners for the module.
     *
     * @param {object} root The calendar root element
     */
    var registerEventListeners = function(root) {
        // Listen the click on the Save buttons.
        root.on('click', SELECTORS.SAVE_BUTTON, function() {
            SlotActions.saveWeekSlots(root);
        });

        // Listen the click on the Copy buttons.
        root.on('click', SELECTORS.COPY_BUTTON, function() {
            SlotActions.copySlots(root);
        });

        // Listen the click on the Copy buttons.
        root.on('click', SELECTORS.PASTE_BUTTON, function() {
            SlotActions.pasteSlots(root);
        });

        // Listen the click on the Clear buttons.
        root.on('click', SELECTORS.CLEAR_BUTTON, function() {
            SlotActions.clearWeekSlots();
        });

        // Listen to click on the clickable slot areas/cells
        var contextId = $(SELECTORS.CALENDAR_MONTH_WRAPPER).data('context-id');
        if (contextId) {
            // Bind click events to week calendar days.
            root.on('click', SELECTORS.DAY, function(e) {
                var target = $(e.target);
                // Change marked state
                if (typeof target !== 'undefined') {
                    if (!target.is(SELECTORS.VIEW_DAY_LINK)) {
                        SlotActions.setSlot(this);
                    }
                }

                e.preventDefault();
            });
        }
    };

    return {
        init: function(root) {
            root = $(root);
            BookingViewManager.init(root);
            registerEventListeners(root);
        }
    };
});
