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

use renderable;
use renderer_base;
use templatable;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class chat_interface implements renderable, templatable {

    /** @var object The moochat instance */
    protected $moochat;

    /** @var string|null The avatar URL */
    protected $avatarurl;

    /**
     * Constructor.
     *
     * @param object $moochat The moochat instance
     * @param string|null $avatarurl The avatar URL
     */
    public function __construct($moochat, $avatarurl = null) {
        $this->moochat = $moochat;
        $this->avatarurl = $avatarurl;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        
        $data->moochatid = $this->moochat->id;
        $data->name = format_string($this->moochat->name);
        $data->avatarurl = $this->avatarurl ? $this->avatarurl->out(false) : null;
        $data->avatarsize = $this->moochat->avatarsize;
        $data->sizeclass = 'moochat-size-' . $this->moochat->chatsize;
        
        // Language strings
        $data->startchat = get_string('startchat', 'moochat');
        $data->typemessage = get_string('typemessage', 'moochat');
        $data->send = get_string('send', 'moochat');
        $data->clear = get_string('clear', 'moochat');
        
        return $data;
    }
}
