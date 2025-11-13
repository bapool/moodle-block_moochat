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

class restore_moochat_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines structure of path elements to be processed during the restore
     *
     * @return array of {@link restore_path_element}
     */
    protected function define_structure() {

        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('moochat', '/activity/moochat');
        if ($userinfo) {
            $paths[] = new restore_path_element('moochat_usage', '/activity/moochat/usages/usage');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process the given restore path element data
     *
     * @param array $data parsed element data
     */
    protected function process_moochat($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the moochat record.
        $newitemid = $DB->insert_record('moochat', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process the given restore path element data
     *
     * @param array $data parsed element data
     */
    protected function process_moochat_usage($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->moochatid = $this->get_new_parentid('moochat');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->firstmessage = $this->apply_date_offset($data->firstmessage);
        $data->lastmessage = $this->apply_date_offset($data->lastmessage);

        $newitemid = $DB->insert_record('moochat_usage', $data);
        $this->set_mapping('moochat_usage', $oldid, $newitemid);
    }

    /**
     * Post-execution actions
     */
    protected function after_execute() {
        // Add moochat related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_moochat', 'intro', null);
        $this->add_related_files('mod_moochat', 'avatar', null);
    }
}
