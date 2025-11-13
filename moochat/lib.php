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

defined('MOODLE_INTERNAL') || die();

/**
 * Add moochat instance
 */
function moochat_add_instance($moochat) {
    global $DB;
    
    $moochat->timecreated = time();
    $moochat->timemodified = time();
    
    $moochat->id = $DB->insert_record('moochat', $moochat);
    
    return $moochat->id;
}

/**
 * Update moochat instance
 */
function moochat_update_instance($moochat) {
    global $DB;
    
    $moochat->timemodified = time();
    $moochat->id = $moochat->instance;
    
    return $DB->update_record('moochat', $moochat);
}

/**
 * Delete moochat instance
 */
function moochat_delete_instance($id) {
    global $DB;
    
    if (!$moochat = $DB->get_record('moochat', array('id' => $id))) {
        return false;
    }
    
    // Delete usage records
    $DB->delete_records('moochat_usage', array('moochatid' => $id));
    
    // Delete the instance
    $DB->delete_records('moochat', array('id' => $id));
    
    return true;
}

/**
 * Supported features
 */
function moochat_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
	//case FEATURE_MOD_ARCHETYPE:
          //  return MOD_ARCHETYPE_ASSIGNMENT;
        default:
            return null;
    }
}

/**
 * Serve the files from the moochat file areas
 */
function mod_moochat_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }
    
    if ($filearea !== 'avatar') {
        return false;
    }
    
    require_login($course, false, $cm);
    
    $fs = get_file_storage();
    $filename = array_pop($args);
    $filepath = '/';
    
    $file = $fs->get_file($context->id, 'mod_moochat', $filearea, 0, $filepath, $filename);
    
    if (!$file || $file->is_directory()) {
        return false;
    }
    
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
/**
 * Return the content to display inline on course page
 */
function moochat_get_coursemodule_info($coursemodule) {
    global $DB, $PAGE;
    
    $moochat = $DB->get_record('moochat', array('id' => $coursemodule->instance), '*', MUST_EXIST);
    
    $info = new cached_cm_info();
    $info->name = $moochat->name;
    
    if ($moochat->display == 1) {
        // Inline display mode
        $context = context_module::instance($coursemodule->id);
        
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
        
        // Render using template
        $chatinterface = new \mod_moochat\output\chat_interface($moochat, $avatarurl);
        $renderer = $PAGE->get_renderer('mod_moochat');
        $content = $renderer->render_chat_interface($chatinterface);
        
        $info->content = $content;
    }
    
    return $info;
}
/**
 * Callback to add JS when course page loads
 */
function moochat_cm_info_view(cm_info $cm) {
    global $PAGE, $DB;
    
    $moochat = $DB->get_record('moochat', array('id' => $cm->instance));
    
    if ($moochat && $moochat->display == 1) {
        // Only load JS for inline display mode
        $PAGE->requires->js_call_amd('mod_moochat/chat', 'init', array($moochat->id));
    }
}
/**
 * Extract content from the current section
 */
function moochat_get_section_content($courseid, $sectionnum, $includehidden = false) {
    global $DB;
    
    $content = "\n\n=== COURSE SECTION CONTENT ===\n\n";
    
    // Get all course modules in this section
    $modinfo = get_fast_modinfo($courseid);
    $section = $modinfo->get_section_info($sectionnum);
    
    if (empty($section->sequence)) {
        return '';
    }
    
    $cms = explode(',', $section->sequence);
    
    foreach ($cms as $cmid) {
        $cm = $modinfo->get_cm($cmid);
        
        // Skip if not visible (unless includehidden is true)
        if (!$includehidden && !$cm->uservisible) {
            continue;
        }
        
        $content .= "\n--- " . format_string($cm->name) . " ---\n";
        
        // Extract content based on module type
        switch ($cm->modname) {
            case 'page':
                if ($page = $DB->get_record('page', array('id' => $cm->instance))) {
                    $content .= strip_tags($page->content) . "\n";
                }
                break;
                
            case 'book':
                if ($book = $DB->get_record('book', array('id' => $cm->instance))) {
                    $chapters = $DB->get_records('book_chapters', array('bookid' => $book->id), 'pagenum');
                    foreach ($chapters as $chapter) {
                        $content .= "Chapter: " . format_string($chapter->title) . "\n";
                        $content .= strip_tags($chapter->content) . "\n";
                    }
                }
                break;
                
            case 'label':
                if ($label = $DB->get_record('label', array('id' => $cm->instance))) {
                    $content .= strip_tags($label->intro) . "\n";
                }
                break;
                
            case 'assign':
                if ($assign = $DB->get_record('assign', array('id' => $cm->instance))) {
                    $content .= "Assignment Description: " . strip_tags($assign->intro) . "\n";
                }
                break;
                
            case 'url':
                if ($url = $DB->get_record('url', array('id' => $cm->instance))) {
                    $content .= "URL: " . $url->externalurl . "\n";
                    if ($url->intro) {
                        $content .= strip_tags($url->intro) . "\n";
                    }
                }
                break;
                
            case 'glossary':
                if ($glossary = $DB->get_record('glossary', array('id' => $cm->instance))) {
                    $entries = $DB->get_records('glossary_entries', array('glossaryid' => $glossary->id));
                    $content .= "Glossary Entries:\n";
                    foreach ($entries as $entry) {
                        $content .= "- " . format_string($entry->concept) . ": " . strip_tags($entry->definition) . "\n";
                    }
                }
                break;
        }
    }
    
    $content .= "\n=== END COURSE SECTION CONTENT ===\n\n";
    
    return $content;
}
