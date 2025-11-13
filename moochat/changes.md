# MooChat Plugin - Changes for Moodle.org Submission

## Version 1.2.5 (2025-11-13)

### Bug Fixes
- Fixed confusion between rate limiting and message limits
  - Rate limiting now correctly counts **conversations per day/hour** (not individual messages)
  - Max messages per session now correctly limits **messages per conversation**
  - Both limits now display separately and clearly:
    - "Conversations remaining today: X"
    - "Messages remaining in this conversation: X"
  - Clearing chat now starts a new conversation (counts toward rate limit)
  - Continuing an existing conversation does not count toward rate limit
- Updated language strings for clarity:
  - Changed "Questions remaining" to "Conversations remaining today"
  - Added "Messages remaining in this conversation"
- Fixed message limit error dialog:
  - Changed title from "Error" to "Chat Limit Reached"
  - Updated message to be more helpful and instructive

**Status:** ✓ COMPLETE

---

## Version 1.2.4 (2025-11-13)

### Bug Fixes
- Fixed missing language string 'missingidandcmid' causing validation errors
- Fixed JavaScript coding standards violations (125 errors corrected)
  - Removed tab characters, replaced with spaces
  - Removed unused variables (messageCount, ex parameter)
  - Fixed @package JSDoc tag formatting
  - Fixed promise return value warning
  - Added eslint-disable comment for necessary confirm() usage
- Rebuilt JavaScript with Grunt - all files now properly minified
- All AMD build files now meet Moodle.org submission requirements

**Status:** ✓ COMPLETE

---

## Version: 1.0.1
Date: 2025-11-08

---

## Issue #3: Undefined function usage (FIXED)

**Problem:** Plugin was using deprecated `print_error()` function which is not defined in newer Moodle versions.

**File Changed:** `view.php` (line 19)

**Change Made:**
- **Before:** `print_error('missingidandcmid', 'moochat');`
- **After:** `throw new moodle_exception('missingidandcmid', 'moochat');`

**Reason:** The `print_error()` function is deprecated in Moodle 4.x. The correct method is to throw a `moodle_exception` which provides the same functionality with better error handling.

**Status:** ✓ COMPLETE

---
## Issue #6: Missing module events implementation (FIXED)

**Problem:** Plugin was missing the required module viewed event implementation for proper activity logging and completion tracking.

**Files Changed:**
1. `classes/event/course_module_viewed.php` (NEW FILE)
2. `view.php` (lines 27-34)

**Changes Made:**

1. **Created custom event class** (`classes/event/course_module_viewed.php`):
   - Extended `\core\event\course_module_viewed` abstract class
   - Implemented required methods: `init()`, `get_description()`, `validate_data()`, and `get_objectid_mapping()`
   - Set proper object table, CRUD operation, and education level

2. **Updated view.php**:
   - Uncommented and corrected the event trigger code
   - Changed from `\core\event\course_module_viewed` to `\mod_moochat\event\course_module_viewed`
   - Event now properly logs when users view the moochat activity

**Reason:** Moodle requires activity modules to trigger events for proper logging, analytics, reporting, and activity completion tracking. The abstract `\core\event\course_module_viewed` class cannot be instantiated directly; modules must create their own implementation.

**Status:** ✓ COMPLETE

---
## Issue #5: Hard-coded language strings (FIXED)

**Problem:** JavaScript file contained hard-coded English strings that should be defined in the language file for proper multilingual support and internationalization.

**Files Changed:**
1. `lang/en/moochat.php` - Added 7 new language strings
2. `amd/src/chat.js` - Updated to use language strings
3. `amd/build/chat.min.js` - Rebuilt minified version

**Changes Made:**

1. **Added new language strings** to `lang/en/moochat.php`:
   - `questionsremaining_js` - "Questions remaining"
   - `thinking_js` - "Thinking..."
   - `ratelimitreached_title` - "Rate Limit Reached"
   - `error_title` - "Error"
   - `connectionerror` - "Failed to connect to AI service"
   - `chatcleared` - "Chat cleared. Start a new conversation!"
   - `confirmclear` - "Clear all messages? (Your question limit will not reset)"

2. **Updated JavaScript** (`amd/src/chat.js`):
   - Added `core/str` module to imports
   - Loaded all language strings using `Str.get_strings()` at initialization
   - Replaced all 7 hard-coded strings with references to loaded strings array
   - Lines changed: 3, 11-23, 21, 57, 79, 84, 120, 192, 208

3. **Rebuilt minified JavaScript** (`amd/build/chat.min.js`)

**Reason:** All user-facing strings must be defined in language files to support proper internationalization and allow translation into other languages. This is a Moodle coding standard requirement.

**Status:** ✓ COMPLETE
## Issue #4: Update Ajax implementation to External Services (FIXED)

**Problem:** Plugin was using direct jQuery $.ajax() calls to a custom PHP script (chat_service.php), which bypasses Moodle's security, session handling, and web services framework. This is not the recommended approach for Moodle 4.x plugins.

**Files Changed:**
1. `classes/external/send_message.php` (NEW FILE)
2. `db/services.php` (NEW FILE)
3. `amd/src/chat.js` - Converted from $.ajax to core/ajax
4. `amd/build/chat.min.js` - Rebuilt
5. `version.php` - Bumped to 2025103005 / v1.2

**Changes Made:**

1. **Created External Service class** (`classes/external/send_message.php`):
   - Extends `external_api` to create a proper web service
   - Implements `execute_parameters()`, `execute()`, and `execute_returns()` methods
   - Moved all logic from chat_service.php into the external service
   - Includes proper parameter validation, context checking, and capability requirements
   - Returns structured data with success/error handling

2. **Registered service** in `db/services.php`:
   - Defined `mod_moochat_send_message` function
   - Marked as available for Ajax with `'ajax' => true`
   - Set type as 'write' (modifies database)
   - Required capability: `mod/moochat:submit`
   - Requires login

3. **Updated JavaScript** (`amd/src/chat.js`):
   - Replaced jQuery `$.ajax()` call with Moodle's `Ajax.call()` from core/ajax module
   - Changed from calling `/mod/moochat/chat_service.php` to calling the web service `mod_moochat_
   
   ## Issue #1: Missing Backup/Restore API implementation (FIXED)

**Problem:** Plugin did not implement Moodle's Backup/Restore API, preventing courses containing MooChat activities from being properly backed up and restored.

**Files Changed:**
1. `backup/moodle2/backup_moochat_activity_task.class.php` (NEW FILE)
2. `backup/moodle2/backup_moochat_stepslib.php` (NEW FILE)
3. `backup/moodle2/restore_moochat_activity_task.class.php` (NEW FILE)
4. `backup/moodle2/restore_moochat_activity_task.class.php` (NEW FILE)
5. `version.php` - Bumped to 2025103006

**Changes Made:**

1. **Created Backup Task** (`backup_moochat_activity_task.class.php`):
   - Extends `backup_activity_task`
   - Defines backup steps and structure
   - Implements `encode_content_links()` for URL encoding

2. **Created Backup Structure** (`backup_moochat_stepslib.php`):
   - Extends `backup_activity_structure_step`
   - Defines all moochat fields to be backed up
   - Includes user usage data when userinfo is selected
   - Annotates file areas (intro, avatar)
   - Defines ID mappings for user references

3. **Created Restore Task** (`restore_moochat_activity_task.class.php`):
   - Extends `restore_activity_task`
   - Defines restore steps and structure
   - Implements decode rules for content and links
   - Defines restore log rules

4. **Created Restore Structure** (`restore_moochat_stepslib.php`):
   - Extends `restore_activity_structure_step`
   - Processes moochat instance data restoration
   - Processes usage records (when userinfo included)
   - Remaps user IDs and applies date offsets
   - Restores file areas

**Backup includes:**
- All moochat instance settings (name, intro, display mode, system prompt, rate limits, etc.)
- Avatar images
- User usage records (message counts, timestamps) - only when "Include user data" is selected

**Reason:** The Backup/Restore API is essential for Moodle courses to be portable. Teachers need to be able to backup courses containing activities and restore them to other courses or Moodle instances. This is a core requirement for all activity modules.

**Status:** ✓ COMPLETE
## Issue #9: The Privacy Provider is not implemented (FIXED)

**Problem:** Plugin did not implement the Privacy API, which is required for GDPR compliance. Without this, Moodle cannot properly handle data export and deletion requests for user data stored by the plugin.

**Files Changed:**
1. `classes/privacy/provider.php` (NEW FILE)
2. `version.php` - Bumped to 2025103007

**Changes Made:**

1. **Created Privacy Provider class** (`classes/privacy/provider.php`):
   - Implements `\core_privacy\local\metadata\provider` - declares what user data is stored
   - Implements `\core_privacy\local\request\plugin\provider` - handles data export and deletion
   - Implements `\core_privacy\local\request\core_userlist_provider` - handles bulk operations

2. **Implemented required methods**:
   - `get_metadata()` - Declares that moochat_usage table stores user data (userid, messagecount, timestamps)
   - `get_contexts_for_userid()` - Returns all moochat contexts where a user has data
   - `get_users_in_context()` - Returns all users who have data in a specific moochat activity
   - `export_user_data()` - Exports a user's moochat usage data (message counts and timestamps)
   - `delete_data_for_all_users_in_context()` - Deletes all usage data for a specific moochat instance
   - `delete_data_for_user()` - Deletes a specific user's usage data across multiple contexts
   - `delete_data_for_users()` - Deletes multiple users' data in a specific context

**Data Stored:**
The plugin stores the following personal data in `moochat_usage` table:
- User ID (who used the chat)
- Message count (how many messages sent)
- First message timestamp (when they first used it)
- Last message timestamp (when they last used it)

**Privacy Compliance:**
- Users can request their data to be exported (shows their usage statistics)
- Users can request their data to be deleted (removes usage records)
- Site administrators can delete all data for a moochat activity
- Full GDPR compliance for user data handling

**Reason:** The Privacy API is mandatory for all Moodle plugins that store personal user data. It ensures compliance with GDPR and other privacy regulations by allowing users to export and delete their personal data.

**Status:** ✓ COMPLETE
## Issue #8: Transition to Templates and Output API (FIXED)

**Problem:** Plugin was generating HTML directly in PHP files using echo statements and string concatenation. Moodle's modern approach requires using Mustache templates and the Output API for better code separation, theming support, and security.

**Files Changed:**
1. `templates/chat_interface.mustache` (NEW FILE)
2. `classes/output/chat_interface.php` (NEW FILE - renderable class)
3. `classes/output/renderer.php` (NEW FILE - renderer class)
4. `view.php` - Converted to use templates (lines 55-117 replaced)
5. `lib.php` - Updated `moochat_get_coursemodule_info()` function to use templates (lines 104-181)
6. `version.php` - Bumped to 2025103009 / v1.2.2

**Changes Made:**

1. **Created Mustache Template** (`templates/chat_interface.mustache`):
   - Contains all HTML structure for the chat interface
   - Uses Mustache syntax for dynamic content ({{variables}})
   - Includes proper documentation with context variables
   - Supports conditional rendering (avatar display)
   - Automatic HTML escaping for security

2. **Created Renderable Class** (`classes/output/chat_interface.php`):
   - Implements `renderable`, `templatable` interfaces
   - Holds data for the chat interface (moochat instance, avatar URL)
   - `export_for_template()` method prepares data for the template
   - Formats data appropriately (format_string, language strings, etc.)

3. **Created Renderer Class** (`classes/output/renderer.php`):
   - Extends `plugin_renderer_base`
   - `render_chat_interface()` method renders the template
   - Uses `render_from_template()` to process Mustache templates

4. **Updated view.php**:
   - Removed 60+ lines of HTML generation code
   - Now creates a chat_interface object
   - Gets the renderer and calls render_chat_interface()
   - Clean, maintainable code

5. **Updated lib.php**:
   - Updated `moochat_get_coursemodule_info()` for inline display
   - Removed HTML string concatenation
   - Uses same template system as view.php
   - Consistent rendering across display modes

**Benefits:**
- **Separation of concerns**: HTML in templates, logic in PHP
- **Better theming**: Themes can override templates
- **Security**: Automatic HTML escaping prevents XSS attacks
- **Maintainability**: Easier to update UI without touching PHP code
- **Standards compliance**: Follows Moodle coding guidelines
- **Consistency**: Same rendering approach throughout the plugin

**Before (old approach):**
```php
echo '<div class="moochat-activity-container">';
echo '<div class="moochat-sidebar">';
// 60+ lines of echo statements
```

**After (modern approach):**
```php
$chatinterface = new \mod_moochat\output\chat_interface($moochat, $avatarurl);
$renderer = $PAGE->get_renderer('mod_moochat');
echo $renderer->render_chat_interface($chatinterface);
```

## Version 1.2.3 (2025-11-10)

### Bug Fixes
- Fixed backup and restore functionality failing with status code 500
- Added missing 'model' and 'avatarurl' fields to backup structure
- Backup and restore now correctly preserves all moochat activity settings

---
