<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// any later version.
/**
 * Library functions for block_moochat
 *
 * @package    block_moochat
 * @copyright  2025 Brian A. Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Serve the files from the moochat file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool false if file not found, does not return if found - just send the file
 */
function block_moochat_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    
    if ($context->contextlevel != CONTEXT_BLOCK) {
        return false;
    }
    
    if ($filearea !== 'avatar') {
        return false;
    }
    
    require_login($course);
    
    $fs = get_file_storage();
    $filename = array_pop($args);
    $filepath = '/';
    
    $file = $fs->get_file($context->id, 'block_moochat', $filearea, 0, $filepath, $filename);
    
    if (!$file || $file->is_directory()) {
        return false;
    }
    
    // Send the file
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}

/**
 * This function is called when a block instance is deleted
 *
 * @param int $instanceid The block instance id
 * @return bool
 */
function block_moochat_delete_instance($instanceid) {
    global $DB;
    
    // Delete all conversations for this block instance
    $DB->delete_records('block_moochat_conversations', array('instanceid' => $instanceid));
    
    // Delete all usage records for this block instance
    $DB->delete_records('block_moochat_usage', array('instanceid' => $instanceid));
    
    return true;
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all conversations for the specified course.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function block_moochat_reset_userdata($data) {
    global $DB;
    
    $status = array();
    $componentstr = get_string('pluginname', 'block_moochat');
    
    if (!empty($data->reset_moochat_conversations)) {
        // Get all block instances in this course
        $coursecontext = context_course::instance($data->courseid);
        $blockinstances = $DB->get_records('block_instances', 
            array('parentcontextid' => $coursecontext->id, 'blockname' => 'moochat'));
        
        if ($blockinstances) {
            $instanceids = array_keys($blockinstances);
            list($insql, $params) = $DB->get_in_or_equal($instanceids);
            
            // Delete conversations
            $DB->delete_records_select('block_moochat_conversations', "instanceid $insql", $params);
            
            // Delete usage records
            $DB->delete_records_select('block_moochat_usage', "instanceid $insql", $params);
            
            $status[] = array(
                'component' => $componentstr,
                'item' => get_string('conversations', 'block_moochat'),
                'error' => false
            );
        }
    }
    
    return $status;
}

/**
 * This function extends the course reset form
 *
 * @param MoodleQuickForm $mform
 */
function block_moochat_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'moochatheader', get_string('pluginname', 'block_moochat'));
    $mform->addElement('checkbox', 'reset_moochat_conversations', get_string('deleteallconversations', 'block_moochat'));
}

/**
 * This function provides default values for the course reset form
 *
 * @param object $course
 * @return array
 */
function block_moochat_reset_course_form_defaults($course) {
    return array('reset_moochat_conversations' => 1);
}
