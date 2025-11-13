<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Version information for mod_moochat
 *
 * @package    mod_moochat
 * @copyright  2025 Brian A. Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'mod_moochat';
$plugin->requires = 2024042200; // Moodle 4.5
$plugin->version = 2025111301;  // YYYYMMDDXX - Fixed rate limiting vs message limit confusion
$plugin->maturity = MATURITY_BETA;
$plugin->release = 'v1.2.5';
