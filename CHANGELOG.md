# Changelog for block_moochat

All notable changes to this project will be documented in this file.

## [Version 1.5.1] - 2026-02-19

### Added
- **Personalized Welcome Message**
  - Chat now displays a friendly greeting on load instead of a generic prompt
  - If no chatbot name is configured: "Hello, I am here if you want to chat."
  - If a chatbot name is set in block configuration: "Hello, my name is [Name], would you like to chat?"
  - Welcome message also shown after clearing chat (replaces "Chat cleared" message)
  - Added `startchat_named` language string for the named greeting

### Changed
- `startchat` language string updated from "Start chatting with the AI assistant!" to "Hello, I am here if you want to chat."
- `clearChat()` in JavaScript now restores the personalized welcome message instead of the generic cleared message

### Files Modified
- `lang/en/block_moochat.php` - Updated `startchat` string, added `startchat_named` string
- `block_moochat.php` - Dynamic welcome message based on configured chatbot name; passes `welcomemessage` to JavaScript
- `amd/src/chat.js` - Uses `strings.welcomemessage` on chat clear

## [Version 1.5] - 2026-02-11

### Added
- **Collapsible Date Sections** in conversation view
  - Conversations now grouped by date with collapsible sections
  - "Expand All" and "Collapse All" buttons for easy navigation
  - All dates collapsed by default to reduce clutter
  - Message count displayed for each date
  - Smooth animations for expand/collapse transitions

### Changed
- **View Conversations button now always visible to teachers**
  - Previously only visible in edit mode
  - Now appears in block footer for teachers at all times
  - Makes conversation monitoring more accessible
  
- **Improved Conversation Display**
  - Student name shown instead of generic "Student" label
  - Chatbot name from block config shown instead of generic "Assistant" label
  - Date/time separated: date in section header, time in message header
  - Messages within same day show only time (not full date/time)
  - Better visual hierarchy with message grouping

- **Enhanced Privacy Implementation**
  - Complete GDPR-compliant privacy provider
  - Supports data export for user conversation history
  - Supports data deletion for individual users or all users
  - Implements userlist provider for bulk operations
  - Proper metadata declarations for both usage and conversation tables

### Fixed
- Language string issues (expandall, collapseall, messages now properly defined)
- JavaScript collapse functionality now works reliably
- Default collapsed state works correctly

### Technical Details
- Simplified JavaScript for collapse functionality (no Bootstrap API dependency)
- Added proper language strings for UI controls
- Enhanced CSS for collapsible sections with hover states
- Privacy provider fully implements all required interfaces

## [Version 1.4] - 2026-02-11

### Added
- **Conversation Tracking System**
  - New database table `block_moochat_conversations` to store all chat messages
  - Teacher view page to see list of all students with conversations
  - Individual conversation detail page showing full chat history
  - Timestamps recorded for each message
  - Conversations displayed in chronological order with clear user/assistant labels
  - Message count and last message time displayed for each student

- **Teacher Features**
  - "View Conversations" button added to block (visible in edit mode)
  - Student list showing all users who have chatted with the bot
  - Click-through to view complete conversation history
  - Clean, readable chat-style display with proper styling

- **Data Management**
  - Conversations stored indefinitely (no automatic deletion)
  - Automatic cleanup when block instance is deleted
  - Integration with Moodle's course reset functionality
  - Option to delete all MooChat conversations during course reset
  - GDPR-compliant privacy API updates for conversation data

- **New External Service**
  - `block_moochat_save_conversation` AJAX service
  - Automatically saves conversations after each message exchange
  - Silent failure handling (doesn't interrupt user experience)

### Changed
- **Data Retention Policy**
  - Removed 7-day auto-cleanup of usage records
  - Conversations now kept until block deletion or course reset
  - Better aligned with educational needs for year-long courses

- **Block Deletion Behavior**
  - Added `instance_delete()` method to block class
  - Properly removes all conversations and usage data on deletion
  - Clean database state after block removal

- **Course Reset Integration**
  - Added reset functions to lib.php
  - Checkbox option in course reset form
  - Deletes conversations for all MooChat blocks in course when reset
  - Follows Moodle course reset standards

### Technical Details
- Created new database table with proper indexes for performance
- Added upgrade script (db/upgrade.php) for existing installations
- Updated JavaScript to call save_conversation service
- Added CSS styling for conversation view pages
- New language strings for conversation tracking features
- Privacy API metadata updated for GDPR compliance
- External services properly registered in db/services.php

### Files Added
- `classes/external/save_conversation.php` - External service for saving conversations
- `view_conversations.php` - Teacher view page for conversations
- `db/upgrade.php` - Database upgrade script

### Files Modified
- `db/install.xml` - Added conversations table schema
- `db/services.php` - Registered save_conversation service
- `version.php` - Updated to 2026021100 (v1.4)
- `block_moochat.php` - Added View Conversations button and instance_delete method
- `lib.php` - Added course reset and block deletion handlers
- `lang/en/block_moochat.php` - Added conversation tracking strings
- `styles.css` - Added conversation view styling
- `amd/src/chat.js` - Added conversation saving after message exchange

## [Version 1.3] - 2025-11-13

### Fixed
- **Removed all hard-coded strings from JavaScript** (Moodle plugin requirement)
  - Fixed hard-coded "Thinking..." message
  - Fixed hard-coded "Rate Limit Reached" alert title
  - Fixed hard-coded "Error" alert titles
  - Fixed hard-coded "Failed to connect to AI service" message
  - All strings now properly loaded from language file via PHP

### Changed
- **Improved user experience for chat limit messages**
  - Changed generic "Error" title to "Chat Limit Reached" for max messages
  - Updated message to be more user-friendly and actionable
  - Added `chatlimitreached` and `maxmessagesreached` language strings

### Technical Details
- Updated `block_moochat.php` to pass all UI strings to JavaScript
- Modified `amd/src/chat.js` to use `strings` parameter for all user-facing text
- Compiled JavaScript using grunt for Moodle standards compliance
- Fixed code style issues (tabs, trailing spaces, mixed indentation)
- Removed unused `messageCount` variable

## [Version 1.2] - 2025-11-08

### Changed
- **Migrated from legacy AJAX to External Services API** (Moodle plugin requirement)
  - Created new External Service class (`classes/external/send_message.php`)
  - Added service definition file (`db/services.php`)
  - Updated JavaScript to use Moodle's `Ajax.call()` instead of jQuery `$.ajax()`
  - Removed deprecated `chat_service.php` file
  - Improved security and compatibility with Moodle standards

### Added
- **Privacy API Implementation** (GDPR compliance)
  - Created Privacy Provider class (`classes/privacy/provider.php`)
  - Implements metadata provider to describe stored user data
  - Implements plugin provider for data export and deletion
  - Implements userlist provider for bulk operations
  - Added privacy-related language strings

- **Language String Improvements**
  - Moved all hard-coded JavaScript strings to language files
  - Added `chatcleared` string for chat reset confirmation
  - Added `confirmclear` string for clear button dialog
  - Added six privacy metadata strings for GDPR compliance

### Fixed
- Hard-coded language strings in JavaScript replaced with proper `get_string()` calls
- JavaScript now properly receives language strings from PHP
- All user-facing text now translatable and follows Moodle coding standards

### Technical Details
- Updated `amd/src/chat.js` to accept language strings as parameter
- Modified `block_moochat.php` to pass language strings to JavaScript module
- External Service uses `core_external` namespace for Moodle 4.5 compatibility
- Privacy Provider handles user data in `block_moochat_usage` table

## [Version 1.1] - 2025-10-30

### Added
- Rate limiting functionality
- Avatar support with configurable sizes
- Enhanced configuration options

## [Version 1.0] - Initial Release

### Added
- Basic AI chatbot block functionality
- Integration with Moodle's core AI system
- Configurable system prompts
- Message history management
- Clear chat functionality
