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
require_once($CFG->dirroot.'/mod/moochat/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID

if ($id) {
    $cm = get_coursemodule_from_id('moochat', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moochat = $DB->get_record('moochat', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    throw new moodle_exception('missingidandcmid', 'moochat');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/moochat:view', $context);
//
// Trigger module viewed event
$event = \mod_moochat\event\course_module_viewed::create(array(
    'objectid' => $moochat->id,
    'context' => $context,
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('moochat', $moochat);
$event->trigger();
//
// Set up the page
$PAGE->set_url('/mod/moochat/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moochat->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Include JavaScript
$PAGE->requires->js_call_amd('mod_moochat/chat', 'init', array($moochat->id));

// Output starts here
echo $OUTPUT->header();

// Display activity name and intro
//echo $OUTPUT->heading(format_string($moochat->name));

/*if ($moochat->intro) {
    echo $OUTPUT->box(format_module_intro('moochat', $moochat, $cm->id), 'generalbox mod_introbox', 'moochatintro');
}*/

// Get avatar URL if exists
$avatarurl = null;
$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_moochat', 'avatar', 0, 'filename', false);
if (!empty($files)) {
    $file = reset($files);
    $avatarurl = moodle_url::make_pluginfile_url(
        $file->get_contextid(),
        $file->get_component(),
        $file->get_filearea(),
        $file->get_itemid(),
        $file->get_filepath(),
        $file->get_filename()
    );
}

// Display the chat interface with horizontal layout
echo '<div class="moochat-activity-container">';

// Left side - Avatar and info
echo '<div class="moochat-sidebar">';
if ($avatarurl) {
    echo '<div class="moochat-avatar-large">';
    echo html_writer::img($avatarurl, $moochat->name, array('width' => $moochat->avatarsize, 'height' => $moochat->avatarsize));
    echo '</div>';
}
echo '<h3 class="moochat-sidebar-title">' . format_string($moochat->name) . '</h3>';

// Show questions remaining
echo '<div class="moochat-remaining-sidebar" id="moochat-remaining-' . $moochat->id . '"></div>';

echo '</div>'; // End sidebar

// Right side - Chat interface
echo '<div class="moochat-chat-area">';
$sizeclass = 'moochat-size-' . $moochat->chatsize;
echo '<div class="moochat-interface ' . $sizeclass . '" id="moochat-' . $moochat->id . '">';

// Chat messages area
echo '<div class="moochat-messages" id="moochat-messages-' . $moochat->id . '">';
echo '<p class="moochat-welcome">' . get_string('startchat', 'moochat') . '</p>';
echo '</div>';

// Input area
echo '<div class="moochat-input-area">';
echo '<textarea id="moochat-input-' . $moochat->id . '" class="moochat-input" placeholder="' . 
     get_string('typemessage', 'moochat') . '" rows="3"></textarea>';

// Buttons
echo '<div class="moochat-buttons">';
echo '<button id="moochat-send-' . $moochat->id . '" class="btn btn-primary moochat-send">' . 
     get_string('send', 'moochat') . '</button>';
echo '<button id="moochat-clear-' . $moochat->id . '" class="btn btn-secondary moochat-clear">' . 
     get_string('clear', 'moochat') . '</button>';
echo '</div>'; // End buttons

echo '</div>'; // End input area

echo '</div>'; // End moochat-interface
echo '</div>'; // End chat-area

echo '</div>'; // End container

echo $OUTPUT->footer();
