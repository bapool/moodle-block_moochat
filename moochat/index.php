<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Define all the restore steps that will be used by the restore_moochat_activity_task
 *
 * @package    mod_moochat
 * @copyright  2025 Brian A. Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT); // Course ID

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_login($course);
$PAGE->set_pagelayout('incourse');

$params = array('id' => $id);
$PAGE->set_url('/mod/moochat/index.php', $params);
$PAGE->set_title($course->shortname.': '.get_string('modulenameplural', 'moochat'));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('modulenameplural', 'moochat'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'moochat'));

if (!$moochats = get_all_instances_in_course('moochat', $course)) {
    notice(get_string('thereareno', 'moodle', get_string('modulenameplural', 'moochat')),
           new moodle_url('/course/view.php', array('id' => $course->id)));
    exit;
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head  = array ($strsectionname, get_string('name'));
    $table->align = array ('center', 'left');
} else {
    $table->head  = array (get_string('name'));
    $table->align = array ('left');
}

foreach ($moochats as $moochat) {
    $cm = get_coursemodule_from_instance('moochat', $moochat->id);
    $context = context_module::instance($cm->id);
    
    if (!$moochat->visible) {
        $link = html_writer::link(
            new moodle_url('/mod/moochat/view.php', array('id' => $cm->id)),
            format_string($moochat->name),
            array('class' => 'dimmed'));
    } else {
        $link = html_writer::link(
            new moodle_url('/mod/moochat/view.php', array('id' => $cm->id)),
            format_string($moochat->name));
    }

    if ($usesections) {
        $table->data[] = array (get_section_name($course, $moochat->section), $link);
    } else {
        $table->data[] = array ($link);
    }
}

echo html_writer::table($table);

echo $OUTPUT->footer();
