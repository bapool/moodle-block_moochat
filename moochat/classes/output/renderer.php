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

namespace mod_moochat\output;

use plugin_renderer_base;

defined('MOODLE_INTERNAL') || die();

class renderer extends plugin_renderer_base {

    /**
     * Render the chat interface.
     *
     * @param chat_interface $chatinterface
     * @return string HTML
     */
    public function render_chat_interface(chat_interface $chatinterface) {
        $data = $chatinterface->export_for_template($this);
        return $this->render_from_template('mod_moochat/chat_interface', $data);
    }
}
