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

defined('MOODLE_INTERNAL') || die();

class block_moochat extends block_base {
    
    public function init() {
        $this->title = get_string('pluginname', 'block_moochat');
    }
    
    public function specialization() {
        if (isset($this->config->title) && !empty($this->config->title)) {
            $title = format_string($this->config->title);
            
            // Add avatar if exists
            $avatarurl = $this->get_avatar_url();
            if ($avatarurl) {
                // Get avatar size from config, default to 48
                $size = isset($this->config->avatarsize) ? $this->config->avatarsize : 48;
                
                // Stack for 128x128, inline for smaller sizes
                if ($size >= 128) {
                    // Stacked layout for large avatars
                    $avatar = html_writer::div(
                        html_writer::img($avatarurl, $title, 
                            array('class' => 'moochat-avatar-img', 'width' => $size, 'height' => $size)),
                        'moochat-avatar-container'
                    );
                    $titletext = html_writer::div($title, 'moochat-title-text');
                    $this->title = $avatar . $titletext;
                } else {
                    // Inline layout for smaller avatars
                    $avatar = html_writer::img($avatarurl, $title, 
                        array('class' => 'moochat-avatar-title', 'width' => $size, 'height' => $size));
                    $this->title = $avatar . ' ' . $title;
                }
            } else {
                $this->title = $title;
            }
        } else {
            $this->title = get_string('pluginname', 'block_moochat');
        }
    } 
    
    public function applicable_formats() {
        return array('course' => true);
    }
    
    public function instance_allow_multiple() {
        return true;
    }
    
    public function has_config() {
        return true;
    }
    
    /**
     * Delete block instance data when block is deleted
     *
     * @return bool
     */
    public function instance_delete() {
        global $DB;
        
        // Delete all conversations for this block instance
        $DB->delete_records('block_moochat_conversations', array('instanceid' => $this->instance->id));
        
        // Delete all usage records for this block instance
        $DB->delete_records('block_moochat_usage', array('instanceid' => $this->instance->id));
        
        return true;
    }
    
    public function instance_config_save($data, $nolongerused = false) {
        global $USER;
        
        debugging('instance_config_save called!', DEBUG_DEVELOPER);
        
        // File manager fields need special handling - get from $_POST
        if (isset($data->config_avatar)) {
            debugging('Avatar from data object: ' . $data->config_avatar, DEBUG_DEVELOPER);
        }
        
        // Try to get draft item ID from form submission
        $draftitemid = file_get_submitted_draft_itemid('config_avatar');
        debugging('Draft item ID from submission: ' . $draftitemid, DEBUG_DEVELOPER);
        
        if ($draftitemid) {
            debugging('Saving avatar files...', DEBUG_DEVELOPER);
            file_save_draft_area_files(
                $draftitemid,
                $this->context->id,
                'block_moochat',
                'avatar',
                0,
                array('subdirs' => false, 'maxfiles' => 1)
            );
            debugging('Avatar files saved to context ' . $this->context->id, DEBUG_DEVELOPER);
        }
        
        // Don't save the filemanager field in config
        if (isset($data->config_avatar)) {
            unset($data->config_avatar);
        }
        
        return parent::instance_config_save($data, $nolongerused);
    }        
    
    private function get_avatar_url() {
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'block_moochat', 'avatar', 0, 'filename', false);
        
        debugging('Avatar check - Context ID: ' . $this->context->id . ', Files found: ' . count($files), DEBUG_DEVELOPER);
        
        if (!empty($files)) {
            $file = reset($files);
            debugging('Found file: ' . $file->get_filename() . ' in ' . $file->get_filepath(), DEBUG_DEVELOPER);
            $url = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            );
            debugging('Avatar URL: ' . $url->out(), DEBUG_DEVELOPER);
            return $url;
        }
        
        debugging('No avatar files found', DEBUG_DEVELOPER);
        return null;
    }  
        
        
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        
        // Check if user can edit (teacher view)
        $context = context_block::instance($this->instance->id);
        $canedit = has_capability('block/moochat:configure', $context);
        
        if ($canedit && $this->page->user_is_editing()) {
            // Show configuration prompt for teachers in edit mode
            $this->content->text .= $this->get_teacher_config_view();
        } else {
            // Show chat interface for everyone (students and teachers)
            $this->content->text .= $this->get_chat_interface();
            
            // Add View Conversations button in footer for teachers (always visible)
            if ($canedit) {
                $conversationsurl = new moodle_url('/blocks/moochat/view_conversations.php', 
                    array('instanceid' => $this->instance->id));
                $this->content->footer = html_writer::div(
                    html_writer::link($conversationsurl, get_string('viewconversations', 'block_moochat'), 
                        array('class' => 'btn btn-sm btn-secondary btn-block')),
                    'text-center mt-2'
                );
            }
        }
        
        return $this->content;
    }
    
    private function get_teacher_config_view() {
        $config = $this->config;
        $instanceid = $this->instance->id;
        
        $output = html_writer::start_div('moochat-teacher-config');
        $output .= html_writer::tag('h4', get_string('teacherconfig', 'block_moochat'));
        $output .= html_writer::tag('p', get_string('confighelp', 'block_moochat'));
        
        // Show current configuration
        $systemprompt = isset($config->systemprompt) ? $config->systemprompt : get_string('defaultprompt', 'block_moochat');
        $output .= html_writer::tag('p', html_writer::tag('strong', get_string('currentsystemprompt', 'block_moochat')) . '<br>' . 
                   html_writer::tag('em', s($systemprompt)));
        
        // Link to edit configuration
        $editurl = new moodle_url('/blocks/moochat/edit_config.php', array('instanceid' => $instanceid));
        $output .= html_writer::link($editurl, get_string('editconfiguration', 'block_moochat'), 
                   array('class' => 'btn btn-primary'));
        
        $output .= ' ';
        
        // Link to view conversations
        $conversationsurl = new moodle_url('/blocks/moochat/view_conversations.php', array('instanceid' => $instanceid));
        $output .= html_writer::link($conversationsurl, get_string('viewconversations', 'block_moochat'), 
                   array('class' => 'btn btn-secondary'));
        
        $output .= html_writer::end_div();
        
        return $output;
    }
    
private function get_chat_interface() {
        global $PAGE;
        
        $config = $this->config;
        $instanceid = $this->instance->id;
        
        // Determine the AI name from block config title (if set)
        $ainame = (isset($config->title) && !empty($config->title)) ? format_string($config->title) : '';

        // Choose the appropriate welcome message
        if (!empty($ainame)) {
            $welcomemessage = get_string('startchat_named', 'block_moochat', $ainame);
        } else {
            $welcomemessage = get_string('startchat', 'block_moochat');
        }

        // Prepare language strings for JavaScript
        $strings = array(
            'questionsremaining' => get_string('questionsremaining', 'block_moochat'),
            'chatcleared' => get_string('chatcleared', 'block_moochat'),
            'confirmclear' => get_string('confirmclear', 'block_moochat'),
            'thinking' => get_string('thinking', 'block_moochat'),
            'ratelimitreached_title' => get_string('ratelimitreached_title', 'block_moochat'),
            'error' => get_string('error', 'block_moochat'),
            'connectionerror' => get_string('connectionerror', 'block_moochat'),
            'chatlimitreached' => get_string('chatlimitreached', 'block_moochat'),
            'maxmessagesreached' => get_string('maxmessagesreached', 'block_moochat'),
            'welcomemessage' => $welcomemessage
        );

        // Include required JavaScript
        $PAGE->requires->js_call_amd('block_moochat/chat', 'init', array($instanceid, $strings));

        $output = html_writer::start_div('moochat-interface', array('id' => 'moochat-' . $instanceid));

        // Chat display area
        $output .= html_writer::start_div('moochat-messages', array('id' => 'moochat-messages-' . $instanceid));
        $output .= html_writer::tag('p', $welcomemessage, array('class' => 'moochat-welcome'));
        $output .= html_writer::end_div();
        
        // Input area
        $output .= html_writer::start_div('moochat-input-area');
        $output .= html_writer::tag('textarea', '', array(
            'id' => 'moochat-input-' . $instanceid,
            'class' => 'moochat-input',
            'placeholder' => get_string('typemessage', 'block_moochat'),
            'rows' => '3'
        ));
        
        // Buttons container
        $output .= html_writer::start_div('moochat-buttons');
        $output .= html_writer::tag('button', get_string('send', 'block_moochat'), array(
            'id' => 'moochat-send-' . $instanceid,
            'class' => 'btn btn-primary moochat-send'
        ));
        $output .= html_writer::tag('button', get_string('clear', 'block_moochat'), array(
            'id' => 'moochat-clear-' . $instanceid,
            'class' => 'btn btn-secondary moochat-clear'
        ));
        $output .= html_writer::end_div(); // End buttons
        
        $output .= html_writer::end_div(); // End input area
        
        $output .= html_writer::end_div();
        
        return $output;
    }
}
