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

class backup_moochat_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define the root element describing the moochat instance.
        $moochat = new backup_nested_element('moochat', ['id'], [
            'name', 'intro', 'introformat', 'timecreated', 'timemodified',
            'display', 'chatsize', 'systemprompt', 'avatarurl', 'avatarsize',
            'include_section_content', 'include_hidden_content',
            'ratelimit_enable', 'ratelimit_period', 'ratelimit_count',
            'maxmessages', 'temperature', 'model'
        ]);

        // Define usage data element (only if including userinfo).
        $usages = new backup_nested_element('usages');
        $usage = new backup_nested_element('usage', ['id'], [
            'userid', 'messagecount', 'firstmessage', 'lastmessage'
        ]);

        // Build the tree.
        $moochat->add_child($usages);
        $usages->add_child($usage);

        // Define data sources.
        $moochat->set_source_table('moochat', ['id' => backup::VAR_ACTIVITYID]);

        // Include usage records only if userinfo is included.
        if ($userinfo) {
            $usage->set_source_table('moochat_usage', ['moochatid' => backup::VAR_PARENTID]);
        }

        // Define id annotations.
        $usage->annotate_ids('user', 'userid');

        // Define file annotations.
        $moochat->annotate_files('mod_moochat', 'intro', null); // Intro field doesn't use itemid.
        $moochat->annotate_files('mod_moochat', 'avatar', null); // Avatar files.

        // Return the root element (moochat), wrapped into standard activity structure.
        return $this->prepare_activity_structure($moochat);
    }
}
