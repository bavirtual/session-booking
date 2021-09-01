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
 * Contains event class for displaying the week view.
 *
 * @package   local_booking
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use local_booking\local\slot\data_access\slot_vault;
use core\external\exporter;
use core_calendar\external\date_exporter;
use core_calendar\type_base;
use renderer_base;
use moodle_url;

/**
 * Class for displaying the week view.
 *
 * @package   core_calendar
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class week_exporter extends exporter {

    /**
     * @var \calendar_information $calendar The calendar to be rendered.
     */
    protected $calendar;

    /**
     * @var array $days An array of week_timeslot_exporter objects.
     */
    protected $days = [];

    /**
     * @var array $days An array of week_timeslot_exporter objects.
     */
    protected $weekslots;

    /**
     * @var array $showlocaltime Whether to show local time.
     */
    protected $showlocaltime;

    /**
     * @var array $days An array of week_timeslot_exporter objects.
     */
    protected $weekno;

    /**
     * @var int $firstdayofweek The first day of the week.
     */
    protected $firstdayofweek;

    /**
     * @var moodle_url $url The URL for the events page.
     */
    protected $url;

    /**
     * @var bool $initialeventsloaded Whether the events have been loaded for this month.
     */
    protected $initialeventsloaded = true;

    /**
     * Constructor.
     *
     * @param \calendar_information $calendar The calendar information for the period being displayed
     * @param mixed $timeslots An array of week_day_exporter objects.
     * @param array $related Related objects.
     */
    public function __construct(\calendar_information $calendar, type_base $type, $related) {
        global $USER;

        $this->calendar = $calendar;
        $this->weekno = strftime('%W', $this->calendar->time);
        $this->showlocaltime = true;

        $this->firstdayofweek = $type->get_starting_weekday();
        $calendarday = $type->timestamp_to_date_array($this->calendar->time);
        $GMTdate = $type->timestamp_to_date_array(gmmktime(0, 0, 0, $calendarday['mon'], $calendarday['mday'], $calendarday['year']));
        $this->days = get_week_days($GMTdate);

        $vault = new slot_vault();
        $this->weekslots = $vault->get_slots($GMTdate['year'], $this->weekno);

        $this->url = new moodle_url('/local/booking/view.php', [
                'time' => $calendar->time,
            ]);

        if ($this->calendar->course && SITEID !== $this->calendar->course->id) {
            $this->url->param('course', $this->calendar->course->id);
        } else if ($this->calendar->categoryid) {
            $this->url->param('category', $this->calendar->categoryid);
        }

        $related['type'] = $type;

        $data = [
            'url' => $this->url->out(false),
        ];

        parent::__construct($data, $related);
    }

    protected static function define_properties() {
        return [
            'url' => [
                'type' => PARAM_URL,
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
            'courseid' => [
                'type' => PARAM_INT,
            ],
            'categoryid' => [
                'type' => PARAM_INT,
                'optional' => true,
                'default' => 0,
            ],
            'date' => [
                'type' => date_exporter::read_properties_definition(),
            ],
            'daynames' => [
                'type' => day_name_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'timeslots' => [
                'type' => week_timeslot_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'showlocaltime' => [
                'type' => PARAM_BOOL,
            ],
            'weekofyear' => [
                'type' => PARAM_INT,
            ],
            'periodname' => [
                // Note: We must use RAW here because the calendar type returns the formatted month name based on a
                // calendar format.
                'type' => PARAM_RAW,
            ],
            'previousperiod' => [
                'type' => date_exporter::read_properties_definition(),
            ],
            'previousweek' => [
                'type' => PARAM_INT,
            ],
            'previousweekts' => [
                'type' => PARAM_INT,
            ],
            'previousperiodname' => [
                // Note: We must use RAW here because the calendar type returns the formatted month name based on a
                // calendar format.
                'type' => PARAM_RAW,
            ],
            'previousperiodlink' => [
                'type' => PARAM_URL,
            ],
            'nextperiod' => [
                'type' => date_exporter::read_properties_definition(),
            ],
            'nextweek' => [
                'type' => PARAM_INT,
            ],
            'nextweekts' => [
                'type' => PARAM_INT,
            ],
            'nextperiodname' => [
                // Note: We must use RAW here because the calendar type returns the formatted month name based on a
                // calendar format.
                'type' => PARAM_RAW,
            ],
            'nextperiodlink' => [
                'type' => PARAM_URL,
            ],
            'larrow' => [
                // The left arrow defined by the theme.
                'type' => PARAM_RAW,
            ],
            'rarrow' => [
                // The right arrow defined by the theme.
                'type' => PARAM_RAW,
            ],
            // Tracks whether the first set of events have been loaded and provided to the exporter.
            'initialeventsloaded' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
            'defaulteventcontext' => [
                'type' => PARAM_INT,
                'default' => 0,
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
        global $CFG;

        $date = $this->related['type']->timestamp_to_date_array($this->calendar->time);

        $previousperiod = $this->get_period($date, '-');
        $nextperiod = $this->get_period($date, '+');

        $nextperiodlink = new moodle_url($this->url);
        $nextperiodlink->param('time', $nextperiod[0]);
        $nextperiodlink->param('week', strftime('%W', $nextperiod[0]));

        $previousperiodlink = new moodle_url($this->url);
        $previousperiodlink->param('time', $previousperiod[0]);
        $previousperiodlink->param('week', strftime('%W', $previousperiod[0]));

        $return = [
            'courseid' => $this->calendar->courseid,
            'daynames' => $this->get_day_names($output),
            'timeslots' => $this->get_time_slots($output),
            'showlocaltime' => $this->showlocaltime,
            'date' => (new date_exporter($date))->export($output),
            'weekofyear' => $this->weekno,
            'periodname' => strftime(get_string('strftimeweekinyear','local_booking'), $this->calendar->time),
            'previousperiod' => (new date_exporter($previousperiod))->export($output),
            'previousweek' => strftime('%W', $previousperiod[0]),
            'previousweekts' => $previousperiod[0],
            'previousperiodname' => strftime(get_string('strftimeweekinyear','local_booking'), $previousperiod[0]),
            'previousperiodlink' => $previousperiodlink->out(false),
            'nextperiod' => (new date_exporter($nextperiod))->export($output),
            'nextweek' => strftime('%W', $nextperiod[0]),
            'nextweekts' => $nextperiod[0],
            'nextperiodname' => strftime(get_string('strftimeweekinyear','local_booking'), $nextperiod[0]),
            'nextperiodlink' => $nextperiodlink->out(false),
            'larrow' => $output->larrow(),
            'rarrow' => $output->rarrow(),
            'initialeventsloaded' => $this->initialeventsloaded,
        ];

        if ($context = $this->get_default_add_context()) {
            $return['defaulteventcontext'] = $context->id;
        }

        if ($this->calendar->categoryid) {
            $return['categoryid'] = $this->calendar->categoryid;
        }

        return $return;
    }

    /**
     * Get the list of day names for display, re-ordered from the first day
     * of the week.
     *
     * @param   renderer_base $output
     * @return  day_name_exporter[]
     */
    protected function get_day_names(renderer_base $output) {
        $weekdaynames = $this->related['type']->get_weekdays();
        $daysinweek = count($weekdaynames);

        $daynames = [];
        for ($i = 0; $i < $daysinweek; $i++) {
            // Bump the currentdayno and ensure it loops.
            $dayno = ($i + $this->firstdayofweek + $daysinweek) % $daysinweek;
            $dayofmonthname = $this->days[$i]['mday'] . '/' . $this->days[$i]['mon'];
            $dayname = new day_name_exporter($dayno, $dayofmonthname, $weekdaynames[$dayno]);

            $daynames[] = $dayname->export($output);
        }

        return $daynames;
    }

    /**
     * Get the list of day hours in 24hr format for display
     * of the week.
     *
     * @return  $timeslots[]
     */
    protected function get_time_slots(renderer_base $output) {
        // Get daily slots from settings
        $firstsessionhour = (get_config('local_booking', 'firstsession')) ? substr(get_config('local_booking', 'firstsession'), 0, 2) : LOCAL_BOOKING_FIRSTSLOT;
        $lastsessionhour = (get_config('local_booking', 'lastsession')) ? substr(get_config('local_booking', 'lastsession'), 0, 2) : LOCAL_BOOKING_LASTSLOT;

        // Get user timezone offset
        $usertz = new \DateTimeZone(usertimezone());
        $usertime = new \DateTime("now", $usertz);
        $usertimezoneoffset = intval($usertz->getOffset($usertime)) / 3600;

        $slots = [];
        for ($i = $firstsessionhour; $i <= $lastsessionhour; $i++) {
            $daydata = [];
            $daydata['timeslot'] = substr('00' . $i, -2) . ':00';
            $daydata['usertimeslot'] = substr('00' . ($i + $usertimezoneoffset) % 24, -2) . ':00';
            $daydata['hour'] = $i;
            $daydata['days'] = $this->days;
            $timeslot = new week_timeslot_exporter($this->calendar, $daydata, $this->weekslots, $this->related);

            $slots[] = $timeslot->export($output);
        }

        return $slots;
    }

    /**
     * Get the previous month timestamp.
     *
     * @return int The previous and next week's timestamp.
     */
    protected function get_period($date, $nextprev) {
        $perioddate = date_create();
        date_timestamp_set($perioddate, $date[0]);
        $perioddate->modify($nextprev . '7 days');
        $newperioddate = $this->related['type']->timestamp_to_date_array(date_timestamp_get($perioddate));

        return $newperioddate;
    }

    /**
     * Set whether the initial events have already been loaded and
     * provided to the exporter.
     *
     * @param   bool    $loaded
     * @return  $this
     */
    public function set_initialeventsloaded(bool $loaded) {
        $this->initialeventsloaded = $loaded;

        return $this;
    }

    /**
     * Get the default context for use when adding a new event.
     *
     * @return null|\context
     */
    protected function get_default_add_context() {
        if (calendar_user_can_add_event($this->calendar->course)) {
            return \context_course::instance($this->calendar->course->id);
        }

        return null;
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return [
            'events' => '\core_calendar\local\event\entities\event_interface[]',
            'cache' => '\core_calendar\external\events_related_objects_cache',
            'type' => '\core_calendar\type_base',
        ];
    }
}
