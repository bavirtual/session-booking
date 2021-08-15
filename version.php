<?php

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$plugin->version = 2021081400;
$plugin->component = 'local_booking';
$plugin->maturity = MATURITY_ALPHA;
$plugin->release = '1.0';

$plugin->dependencies = array(
    'local_availability' => ANY_VERSION,   // The custom user profile fields supporting this plugin (i.e. Show Local Time)
);