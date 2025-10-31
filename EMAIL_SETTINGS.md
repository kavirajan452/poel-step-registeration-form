# Email Settings Configuration

This document explains how to configure email settings for vendor and customer registration forms.

## Overview

The plugin now includes a dedicated Email Settings page that allows administrators to configure:
- Primary email recipients
- CC (Carbon Copy) recipients
- BCC (Blind Carbon Copy) recipients

Settings can be configured separately for:
- **Vendor Registration Form** submissions
- **Customer Registration Form** submissions

## Accessing Email Settings

1. Navigate to **Registrations** in the WordPress admin menu
2. Click on **Email Settings** submenu item
3. Configure the email settings as needed
4. Click **Save Email Settings**

## Configuration Options

### Vendor Form Email Settings

**Admin Email Recipients** (Required)
- Primary email address(es) that will receive vendor registration notifications
- Defaults to WordPress admin email if not configured
- Use commas to separate multiple email addresses
- Example: `admin@example.com, vendor-manager@example.com`

**CC Recipients** (Optional)
- Email addresses that will receive a copy of the notification
- Recipients will see each other's email addresses
- Use commas to separate multiple email addresses
- Example: `hr@example.com, finance@example.com`

**BCC Recipients** (Optional)
- Email addresses that will receive a blind copy of the notification
- Recipients will NOT see each other's email addresses
- Use commas to separate multiple email addresses
- Example: `archive@example.com, backup@example.com`

### Customer Form Email Settings

Same configuration options as Vendor Form, but specifically for customer registration submissions.

## Email Behavior

### User Acknowledgement Email
- Sent to the purchase contact email provided in the form
- Always sent (not configurable)
- Contains a thank you message and confirmation

### Admin Notification Email
- Sent to configured recipients (Admin Email, CC, BCC)
- Contains all submitted form data
- Includes all uploaded documents as attachments
- HTML formatted with professional styling

## Email Validation

- All email addresses are validated before sending
- Invalid email addresses are automatically filtered out
- Multiple emails can be configured by separating them with commas
- Whitespace is automatically trimmed from email addresses

## Security Features

- Only users with `manage_options` capability can access settings
- CSRF protection via WordPress nonces
- All input is sanitized before saving
- Email addresses are validated using WordPress's built-in `is_email()` function

## Troubleshooting

**Emails not being received?**
1. Check WordPress email configuration
2. Verify email addresses are correct
3. Check spam/junk folders
4. Enable WP_DEBUG to see error logs
5. Test with a single email address first

**Multiple recipients not working?**
- Ensure emails are separated by commas
- Remove any extra spaces or special characters
- Test each email address individually

## Best Practices

1. **Always configure at least one admin email** for each form type
2. **Use BCC for archiving** - keeps the main email clean
3. **Test email delivery** after configuration
4. **Keep email lists updated** - remove inactive addresses
5. **Use department emails** - more reliable than personal emails

## Default Behavior

If email settings are not configured:
- Admin emails default to the WordPress site's admin email
- CC and BCC fields are empty by default
- User acknowledgement emails are always sent to the form submitter

## Example Configuration

**Vendor Form:**
- Admin Email: `purchasing@company.com, vendor-relations@company.com`
- CC: `operations@company.com`
- BCC: `archive@company.com`

**Customer Form:**
- Admin Email: `sales@company.com, customer-service@company.com`
- CC: `marketing@company.com`
- BCC: `archive@company.com`
