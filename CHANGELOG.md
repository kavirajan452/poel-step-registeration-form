# Changelog

All notable changes to this project will be documented in this file.

## [1.2] - 2025-10-31

### Added
- **Email Configuration Settings**: New submenu under Registrations for configuring email recipients
  - Separate settings for vendor and customer forms
  - Support for multiple admin email recipients (comma-separated)
  - CC (Carbon Copy) recipients configuration
  - BCC (Blind Carbon Copy) recipients configuration
  - Proper email validation and sanitization
  - Server-side validation for required fields
  - Detailed documentation in EMAIL_SETTINGS.md

- **Registration Protection**: Prevent editing and trashing of registration entries
  - Removed edit capability for registrations post type
  - Removed trash/delete capability for registrations post type
  - Cleaned up UI by removing edit and trash action links
  - Removed quick edit functionality
  - Detailed documentation in REGISTRATION_PROTECTION.md

### Changed
- Updated email sending function to use configured settings from Email Settings page
- Email recipients now support multiple addresses separated by commas
- Enhanced email validation using WordPress `is_email()` and `sanitize_email()`
- Updated plugin version to 1.2
- Updated README.md with new features

### Security
- Added capability checks with `current_user_can('manage_options')`
- Implemented CSRF protection using WordPress nonces
- Added sanitization callbacks to all registered settings
- Proper input sanitization for all email fields
- Email validation before sending
- Data integrity protection through edit/trash prevention

## [1.1] - Previous Release

### Added
- Customer Registration Form with 3-step process
- TAN number validation
- Email notifications (user acknowledgement and admin notification)
- Field-level toast alerts
- Radio/Checkbox highlighting
- Enhanced navigation
- Customer type field

## [1.0] - Initial Release

### Added
- Vendor Registration Form with 5-step process
- Multi-step form interface
- Dynamic location selection (Country, State, City)
- Real-time field validation
- File upload management
- Custom post type for registrations
- Admin dashboard features
- Security features (CSRF protection, input sanitization)
