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

class block_moochat_edit_form extends block_edit_form {
    
    protected function specific_definition($mform) {
        global $CFG;
        
        // Custom Block Title
        $mform->addElement('text', 'config_title', 
                          get_string('blocktitle', 'block_moochat'));
        $mform->setType('config_title', PARAM_TEXT);
        $mform->addHelpButton('config_title', 'blocktitle', 'block_moochat');
        $mform->setDefault('config_title', 'MooChat');      
        
        // Avatar Image Upload
        $mform->addElement('filemanager', 'config_avatar', 
                          get_string('avatar', 'block_moochat'),
                          null,
                          array('subdirs' => 0, 'maxfiles' => 1, 
                                'accepted_types' => array('image')));
        $mform->addHelpButton('config_avatar', 'avatar', 'block_moochat');
        
        // Avatar Size Selection
        $sizes = array(
            '32' => 'Small (32x32)',
            '48' => 'Medium (48x48)',
            '64' => 'Large (64x64)',
            '128' => 'Extra Large (128x128)',
        );
        $mform->addElement('select', 'config_avatarsize', 
                          get_string('avatarsize', 'block_moochat'), 
                          $sizes);
        $mform->setDefault('config_avatarsize', '48');
        $mform->addHelpButton('config_avatarsize', 'avatarsize', 'block_moochat');  
        
        // System Prompt (AI Personality)
        $mform->addElement('textarea', 'config_systemprompt', 
                          get_string('systemprompt', 'block_moochat'),
                          array('rows' => 5, 'cols' => 50));
        $mform->setType('config_systemprompt', PARAM_TEXT);
        $mform->addHelpButton('config_systemprompt', 'systemprompt', 'block_moochat');
        $mform->setDefault('config_systemprompt', get_string('defaultprompt', 'block_moochat'));
        
        // Rate Limiting Header
        $mform->addElement('header', 'ratelimitheader', get_string('ratelimiting', 'block_moochat'));
        
        // Enable Rate Limiting
        $mform->addElement('advcheckbox', 'config_ratelimit_enable', 
                          get_string('ratelimit_enable', 'block_moochat'));
        $mform->addHelpButton('config_ratelimit_enable', 'ratelimit_enable', 'block_moochat');
        $mform->setDefault('config_ratelimit_enable', 0);
        
        // Rate Limit Period
        $periods = array(
            'hour' => get_string('period_hour', 'block_moochat'),
            'day' => get_string('period_day', 'block_moochat'),
        );
        $mform->addElement('select', 'config_ratelimit_period', 
                          get_string('ratelimit_period', 'block_moochat'), 
                          $periods);
        $mform->setDefault('config_ratelimit_period', 'day');
        $mform->addHelpButton('config_ratelimit_period', 'ratelimit_period', 'block_moochat');
        $mform->hideIf('config_ratelimit_period', 'config_ratelimit_enable');
        
        // Rate Limit Count
        $mform->addElement('text', 'config_ratelimit_count', 
                          get_string('ratelimit_count', 'block_moochat'));
        $mform->setType('config_ratelimit_count', PARAM_INT);
        $mform->setDefault('config_ratelimit_count', 10);
        $mform->addHelpButton('config_ratelimit_count', 'ratelimit_count', 'block_moochat');
        $mform->hideIf('config_ratelimit_count', 'config_ratelimit_enable');
        
        // Max Messages (kept for backward compatibility)
        $mform->addElement('text', 'config_maxmessages', 
                          get_string('maxmessages', 'block_moochat'));
        $mform->setType('config_maxmessages', PARAM_INT);
        $mform->setDefault('config_maxmessages', 20);
        $mform->addHelpButton('config_maxmessages', 'maxmessages', 'block_moochat');
        
        // Temperature (Creativity)
        $temperatures = array(
            '0.1' => '0.1 - Very Focused',
            '0.3' => '0.3 - Focused',
            '0.5' => '0.5 - Balanced',
            '0.7' => '0.7 - Creative',
            '0.9' => '0.9 - Very Creative',
        );
        $mform->addElement('select', 'config_temperature', 
                          get_string('temperature', 'block_moochat'), 
                          $temperatures);
        $mform->setDefault('config_temperature', '0.7');
        $mform->addHelpButton('config_temperature', 'temperature', 'block_moochat');
        
        /* Advanced Settings Header
        $mform->addElement('header', 'advancedheader', get_string('advancedsettings', 'block_moochat'));
        $mform->setExpanded('advancedheader', false);
        
        /* Model Selection (moved to advanced)
        $models = array(
            'tinyllama:latest' => 'TinyLlama (Fastest, 1.1B)',
            'llama3.2:latest' => 'Llama 3.2 (Fast, 3.2B)',
            'llama2:latest' => 'Llama 2 (Balanced, 7B)',
            'gemma2:latest' => 'Gemma 2 (Quality, 9.2B)',
        );
        $mform->addElement('select', 'config_model', 
                          get_string('modelselection', 'block_moochat'), 
                          $models);
        $mform->setDefault('config_model', get_config('block_moochat', 'default_model'));
        $mform->addHelpButton('config_model', 'modelselection', 'block_moochat');*/
                
    }
    
    public function set_data($defaults) {
        // Prepare file manager for avatar
        $draftitemid = file_get_submitted_draft_itemid('config_avatar');
        file_prepare_draft_area($draftitemid, $this->block->context->id, 'block_moochat', 
                                'avatar', 0, array('subdirs' => false, 'maxfiles' => 1));
        $defaults->config_avatar = $draftitemid;
        
        parent::set_data($defaults);
    }
    
}
