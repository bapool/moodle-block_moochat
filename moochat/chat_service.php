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

define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once('lib.php');

$PAGE->set_context(context_system::instance());

require_login();

$moochatid = required_param('moochatid', PARAM_INT);
$message = required_param('message', PARAM_TEXT);
$conversationhistory = optional_param('history', '', PARAM_RAW);

// Get moochat instance
$moochat = $DB->get_record('moochat', array('id' => $moochatid), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('moochat', $moochatid, $moochat->course, false, MUST_EXIST);
$context = context_module::instance($cm->id);

// Check capability
require_capability('mod/moochat:submit', $context);

// Automatic cleanup: Delete records older than 7 days
$cleanuptime = time() - (7 * 86400); // 7 days ago
$DB->delete_records_select('moochat_usage', 'lastmessage < ?', array($cleanuptime));

// Check rate limiting
$ratelimit_enabled = $moochat->ratelimit_enable;
if ($ratelimit_enabled) {
    $ratelimit_period = $moochat->ratelimit_period;
    $ratelimit_count = intval($moochat->ratelimit_count);
    
    // Get or create usage record
    $usage = $DB->get_record('moochat_usage', 
        array('moochatid' => $moochatid, 'userid' => $USER->id));
    
    $now = time();
    $period_seconds = ($ratelimit_period === 'hour') ? 3600 : 86400;
    
    if ($usage) {
        // Check if we need to reset the counter
        if (($now - $usage->firstmessage) >= $period_seconds) {
            // Period has expired, reset counter
            $usage->messagecount = 0;
            $usage->firstmessage = $now;
            $usage->lastmessage = $now;
            $DB->update_record('moochat_usage', $usage);
        } else {
            // Check if limit reached
            if ($usage->messagecount >= $ratelimit_count) {
                $period_string = get_string('ratelimitreached_' . $ratelimit_period, 'moochat');
                echo json_encode(array(
                    'error' => get_string('ratelimitreached', 'moochat', 
                        array('limit' => $ratelimit_count, 'period' => $period_string)),
                    'remaining' => 0
                ));
                die();
            }
        }
    } else {
        // Create new usage record
        $usage = new stdClass();
        $usage->moochatid = $moochatid;
        $usage->userid = $USER->id;
        $usage->messagecount = 0;
        $usage->firstmessage = $now;
        $usage->lastmessage = $now;
        $usage->id = $DB->insert_record('moochat_usage', $usage);
    }
}

// Parse conversation history
$history = array();
if (!empty($conversationhistory)) {
    $history = json_decode($conversationhistory, true);
    if (!is_array($history)) {
        $history = array();
    }
}

// Check message limit (old system, kept for compatibility)
$maxmessages = intval($moochat->maxmessages);
if ($maxmessages > 0 && count($history) >= ($maxmessages * 2)) {
    echo json_encode(array('error' => get_string('maxmessagesreached', 'moochat')));
    die();
}

// Build full prompt with system instructions and conversation history
$systemprompt = !empty($moochat->systemprompt) ? $moochat->systemprompt : get_string('defaultprompt', 'moochat');
$fullprompt = $systemprompt . "\n\n";

// Add section content if enabled
if ($moochat->include_section_content) {
    // Get section number from section id
    $section = $DB->get_record('course_sections', array('id' => $cm->section));
    $sectionnum = $section ? $section->section : 0;
    
    $include_hidden = isset($moochat->include_hidden_content) ? $moochat->include_hidden_content : 0;
    $sectioncontent = moochat_get_section_content($moochat->course, $sectionnum, $include_hidden);
    
    /*/ DEBUG - Log what we got
    error_log("Section number: " . $sectionnum);
    error_log("Section content length: " . strlen($sectioncontent));
    error_log("Section content preview: " . substr($sectioncontent, 0, 500));*/
    
    $fullprompt .= $sectioncontent;
}

// Add conversation history
foreach ($history as $msg) {
    if ($msg['role'] === 'user') {
        $fullprompt .= "User: " . $msg['content'] . "\n";
    } else if ($msg['role'] === 'assistant') {
        $fullprompt .= "Assistant: " . $msg['content'] . "\n";
    }
}

// Add current message
$fullprompt .= "User: " . $message . "\nAssistant:";

try {
    // Create AI action using Moodle's core AI system
    $action = new \core_ai\aiactions\generate_text(
        contextid: $context->id,
        userid: $USER->id,
        prompttext: $fullprompt
    );
    
    // Get AI manager and process the action
    $manager = \core\di::get(\core_ai\manager::class);
    $response = $manager->process_action($action);
    
    if ($response->get_success()) {
        $reply = $response->get_response_data()['generatedcontent'] ?? '';
        
        // Update usage counter if rate limiting is enabled
        if ($ratelimit_enabled && isset($usage)) {
            $usage->messagecount++;
            $usage->lastmessage = time();
            $DB->update_record('moochat_usage', $usage);
            
            $remaining = $ratelimit_count - $usage->messagecount;
        } else {
            $remaining = -1; // Unlimited
        }
        
        // Return success response
        echo json_encode(array(
            'success' => true,
            'reply' => trim($reply),
            'remaining' => $remaining
        ));
    } else {
        // Return error from AI system
        echo json_encode(array(
            'error' => $response->get_errormessage() ?: 'AI generation failed'
        ));
    }
    
} catch (Exception $e) {
    echo json_encode(array(
        'error' => 'Error: ' . $e->getMessage()
    ));
}
