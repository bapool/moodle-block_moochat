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

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_moochat_mod_form extends moodleform_mod {

    function definition() {
        global $CFG;
        
        $mform = $this->_form;

        // General section
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Activity name
        $mform->addElement('text', 'name', get_string('chatname', 'moochat'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        
        // Introduction
        $this->standard_intro_elements();

	// Display mode
        $displayoptions = array(
            0 => get_string('display_page', 'moochat'),
            1 => get_string('display_inline', 'moochat'),
        );

        $mform->addElement('select', 'display', get_string('display', 'moochat'), $displayoptions);
        $mform->addHelpButton('display', 'display', 'moochat');
        $mform->setDefault('display', 0);

        // Chat size
        $sizeoptions = array(
            'small' => get_string('chatsize_small', 'moochat'),
            'medium' => get_string('chatsize_medium', 'moochat'),
            'large' => get_string('chatsize_large', 'moochat'),
        );
        $mform->addElement('select', 'chatsize', get_string('chatsize', 'moochat'), $sizeoptions);
        $mform->addHelpButton('chatsize', 'chatsize', 'moochat');
        $mform->setDefault('chatsize', 'medium');

        
	// Include section content
        $mform->addElement('advcheckbox', 'include_section_content', 
                          get_string('include_section_content', 'moochat'));
        $mform->addHelpButton('include_section_content', 'include_section_content', 'moochat');
        $mform->setDefault('include_section_content', 0);

        // Include hidden content (only shows if section content is enabled)
        $mform->addElement('advcheckbox', 'include_hidden_content', 
                          get_string('include_hidden_content', 'moochat'));
        $mform->addHelpButton('include_hidden_content', 'include_hidden_content', 'moochat');
        $mform->setDefault('include_hidden_content', 0);
        $mform->hideIf('include_hidden_content', 'include_section_content');
        // Avatar Image Upload
        $mform->addElement('filemanager', 'avatar', 
                          get_string('avatar', 'moochat'),
                          null,
                          array('subdirs' => 0, 'maxfiles' => 1, 
                                'accepted_types' => array('image')));
        $mform->addHelpButton('avatar', 'avatar', 'moochat');
        
        // Avatar Size Selection
        $sizes = array(
            '48' => 'Small (48x48)',
            '64' => 'Medium (64x64)',
            '96' => 'Large (96x96)',
            '128' => 'Extra Large (128x128)',
        );
        $mform->addElement('select', 'avatarsize', 
                          get_string('avatarsize', 'moochat'), 
                          $sizes);
        $mform->setDefault('avatarsize', '64');
        $mform->addHelpButton('avatarsize', 'avatarsize', 'moochat');

        // System Prompt (AI Personality)
        $mform->addElement('textarea', 'systemprompt', 
                          get_string('systemprompt', 'moochat'),
                          array('rows' => 5, 'cols' => 60));
        $mform->setType('systemprompt', PARAM_TEXT);
        $mform->addHelpButton('systemprompt', 'systemprompt', 'moochat');
        $mform->setDefault('systemprompt', get_string('defaultprompt', 'moochat'));

        // Rate Limiting Header
        $mform->addElement('header', 'ratelimitheader', get_string('ratelimiting', 'moochat'));

        // Enable Rate Limiting
        $mform->addElement('advcheckbox', 'ratelimit_enable', 
                          get_string('ratelimit_enable', 'moochat'));
        $mform->addHelpButton('ratelimit_enable', 'ratelimit_enable', 'moochat');
        $mform->setDefault('ratelimit_enable', 0);

        // Rate Limit Period
        $periods = array(
            'hour' => get_string('period_hour', 'moochat'),
            'day' => get_string('period_day', 'moochat'),
        );
        $mform->addElement('select', 'ratelimit_period', 
                          get_string('ratelimit_period', 'moochat'), 
                          $periods);
        $mform->setDefault('ratelimit_period', 'day');
        $mform->addHelpButton('ratelimit_period', 'ratelimit_period', 'moochat');
        $mform->hideIf('ratelimit_period', 'ratelimit_enable');

        // Rate Limit Count
        $mform->addElement('text', 'ratelimit_count', 
                          get_string('ratelimit_count', 'moochat'));
        $mform->setType('ratelimit_count', PARAM_INT);
        $mform->setDefault('ratelimit_count', 10);
        $mform->addHelpButton('ratelimit_count', 'ratelimit_count', 'moochat');
        $mform->hideIf('ratelimit_count', 'ratelimit_enable');

        // Advanced Settings Header
        $mform->addElement('header', 'advancedheader', get_string('advancedsettings', 'moochat'));
        $mform->setExpanded('advancedheader', false);

        // Max Messages
        $mform->addElement('text', 'maxmessages', 
                          get_string('maxmessages', 'moochat'));
        $mform->setType('maxmessages', PARAM_INT);
        $mform->setDefault('maxmessages', 20);
        $mform->addHelpButton('maxmessages', 'maxmessages', 'moochat');

        // Temperature (Creativity)
        $temperatures = array(
            '0.1' => '0.1 - Very Focused',
            '0.3' => '0.3 - Focused',
            '0.5' => '0.5 - Balanced',
            '0.7' => '0.7 - Creative',
            '0.9' => '0.9 - Very Creative',
        );
        $mform->addElement('select', 'temperature', 
                          get_string('temperature', 'moochat'), 
                          $temperatures);
        $mform->setDefault('temperature', '0.7');
        $mform->addHelpButton('temperature', 'temperature', 'moochat');

        /* Model Selection
        $models = array(
            'tinyllama:latest' => 'TinyLlama (Fastest, 1.1B)',
            'llama3.2:latest' => 'Llama 3.2 (Fast, 3.2B)',
            'llama2:latest' => 'Llama 2 (Balanced, 7B)',
            'gemma2:latest' => 'Gemma 2 (Quality, 9.2B)',
        );
        $mform->addElement('select', 'model', 
                          get_string('modelselection', 'moochat'), 
                          $models);
        $mform->setDefault('model', 'gemma2:latest');
        $mform->addHelpButton('model', 'modelselection', 'moochat');*/

        // Standard coursemodule elements
        $this->standard_coursemodule_elements();

        // Buttons
        $this->add_action_buttons();
    }
    
    function data_preprocessing(&$default_values) {
        parent::data_preprocessing($default_values);
        
        // Prepare file manager for avatar
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('avatar');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_moochat', 
                                    'avatar', 0, array('subdirs' => false, 'maxfiles' => 1));
            $default_values['avatar'] = $draftitemid;
        }
    }
    
    function data_postprocessing($data) {
        parent::data_postprocessing($data);
        
        // Handle avatar file upload
        if (!empty($data->avatar)) {
            $draftitemid = $data->avatar;
            file_save_draft_area_files($draftitemid, $this->context->id, 'mod_moochat', 
                                      'avatar', 0, array('subdirs' => false, 'maxfiles' => 1));
        }
    }
}
