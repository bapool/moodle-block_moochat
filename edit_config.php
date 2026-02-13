<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// any later version.
/**
 * Privacy Subsystem implementation for mod_mooproof
 *
 * @package    block_moochat
 * @copyright  2025 Brian A. Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$instanceid = required_param('instanceid', PARAM_INT);

require_login();

$instance = $DB->get_record('block_instances', array('id' => $instanceid), '*', MUST_EXIST);
$context = context_block::instance($instanceid);

require_capability('block/moochat:configure', $context);

// Redirect to the block configuration page
$coursecontext = $context->get_course_context();
$course = $DB->get_record('course', array('id' => $coursecontext->instanceid), '*', MUST_EXIST);

$url = new moodle_url('/course/view.php', array('id' => $course->id, 
                      'bui_editid' => $instanceid, 
                      'sesskey' => sesskey()));

redirect($url);
