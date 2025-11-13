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

require_once($CFG->dirroot . '/mod/moochat/backup/moodle2/backup_moochat_stepslib.php');

class backup_moochat_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the moochat.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_moochat_activity_structure_step('moochat_structure', 'moochat.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to the list of moochats.
        $search = "/(".$base."\/mod\/moochat\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@MOOCHATINDEX*$2@$', $content);

        // Link to moochat view by moduleid.
        $search = "/(".$base."\/mod\/moochat\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@MOOCHATVIEWBYID*$2@$', $content);

        return $content;
    }
}
