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
 * A javascript module to handler calendar view changes.
 *
 * @module     local_booking/time_format
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 define([
    'jquery',
],
function($) {

    var initTimeHHMM = function() {
        $(".time-hhmm").attr('maxlength', '4');
        $(".time-hhmm").attr('placeholder', 'HH:MM');
        $(".time-hhmm").bind({
            keydown: CheckNum,
            blur: formateHHMM,
            focus: unformateHHMM
        });
    };

    var CheckNum = function(e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
                // Let it happen, don't do anything
                return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    };
    var unformateHHMM = function() {
        $(this).val($(this).val().replace(':', ''));
    };

    var formateHHMM = function() {
        var str = $(this).val();
        if (str.length > 2) {
            str = ('0' + str).slice(-4);
        } else {
            str = ('0' + str + '00').slice(-4);
        }

        var mm = parseInt(str.substr(2, 2));
        var hh = parseInt(str.slice(0,2));
        if (mm > 59) {
            mm = mm - 60;
        }

        if (hh > 23) {
            hh = hh % 24;
        }

        mm = ('0' + mm).slice(-2);
        hh = ('0' + hh).slice(-2);
        var formate = hh + ':' + mm;
        $(this).val(formate);
    };

    return {
        init: function() {
            initTimeHHMM();
        }
    };
});
