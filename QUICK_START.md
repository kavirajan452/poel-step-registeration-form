# Quick Start Guide - Version 1.1

## What's New in This Update

This update adds several improvements to make the form more user-friendly and professional:

1. **Better Error Messages** - Now shows which field has an error
2. **Visual Highlights** - Radio buttons and checkboxes light up red when required
3. **Email Notifications** - Automatic emails to users and admins after submission

---

## Installation (If Not Already Installed)

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin in WordPress admin
3. Add the shortcode `[vendor_registration_form]` to any page

---

## New Features Overview

### 1. Field Names in Error Messages

**Before:**
- "File size must not exceed 2MB" ❌ (Which file?)

**Now:**
- "Company Registration File: File size must not exceed 2MB" ✅ (Clear!)

### 2. Red Highlighting for Radio Buttons & Checkboxes

When you click "Next" or "Submit" without selecting required radio buttons or checkboxes, you'll see:
- **Red border** on the left
- **Light red background**
- **Red text** for the label

This makes it obvious which selections you missed!

**Affected Fields:**
- Vendor Type checkboxes (Step 1)
- GST Registration (Step 2)
- E-Invoice Applicability (Step 2, if GST=Yes)
- Return Filing Frequency (Step 2, if GST=Yes)
- MSME Registration (Step 3)

### 3. Email Notifications

#### For Users (Vendors)
After submitting the form, the vendor receives an email at their Purchase Contact Email with:
- Thank you message
- Confirmation that data was received
- What happens next
- Timeline (3-5 business days)

#### For Admins
The WordPress admin receives an email with:
- All form data organized by sections
- All uploaded files attached
- Direct link to view in admin dashboard
- Professional formatting

---

## Testing the New Features

### Test 1: Field-Specific Error Messages

1. Go to the form page
2. Try to upload a file larger than 2MB
3. You should see: "[Field Name]: File size must not exceed 2MB"
4. Try to upload a .txt file
5. You should see: "[Field Name]: File must be jpg, jpeg, or pdf format"

### Test 2: Radio/Checkbox Highlighting

1. Fill out Step 1 but DON'T check any Vendor Type boxes
2. Click "Next"
3. The Vendor Type section should light up red
4. Check one box
5. Red highlighting should disappear

### Test 3: Email Notifications

1. Fill out and submit a complete form
2. Check the Purchase Contact Email inbox for acknowledgement
3. Check WordPress admin email for detailed notification
4. Verify files are attached to admin email

---

## Email Configuration (Important!)

### Basic Setup (Works on Most Servers)
The plugin uses WordPress's built-in email function. It should work out of the box.

### For Better Email Delivery (Recommended)
If emails aren't arriving or go to spam:

1. **Install WP Mail SMTP Plugin**
   - Go to Plugins > Add New
   - Search for "WP Mail SMTP"
   - Install and activate
   - Configure with your email provider

2. **Check WordPress Admin Email**
   - Go to Settings > General
   - Verify the "Email Address" field is correct
   - This is where admin notifications will be sent

3. **Check Spam Folders**
   - Sometimes emails go to spam initially
   - Mark as "Not Spam" to train your email provider

### Troubleshooting Email Issues

**Emails not arriving?**
1. Check WordPress admin email in Settings > General
2. Check spam/junk folders
3. Enable debug logging (see below)
4. Install WP Mail SMTP plugin

**Enable Debug Logging:**
Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```
Check logs at: `/wp-content/debug.log`

---

## Common Questions

### Q: Can I customize the email templates?
A: Currently, the emails use fixed templates. Custom templates are planned for a future version.

### Q: Can I change who receives the admin email?
A: Yes, change the WordPress admin email in Settings > General.

### Q: Can I send emails to multiple admins?
A: Not currently. This is planned for a future version.

### Q: What if a file attachment is too large for email?
A: All files are limited to 2MB each (max 12MB total). Most email servers support up to 25MB.

### Q: Are the emails mobile-friendly?
A: Yes, the email templates are responsive and work on all devices.

### Q: What if email sending fails?
A: The form submission still succeeds. Check debug logs if emails aren't sending.

---

## File Attachments in Admin Email

The following files are automatically attached to the admin email:
- Company Registration File
- GST Certificate (if applicable)
- Udyam Certificate (if applicable)
- MSME Declaration (if applicable)
- Bank Proof/Cancelled Cheque
- PAN Card

---

## For Developers

### Customizing Email Templates

Email templates are in the `index.php` file:
- `get_user_email_template()` - User acknowledgement email
- `get_admin_email_template()` - Admin notification email

### Customizing Toast Messages

Toast messages can be customized in `assets/js/vendor-registration.js`:
- Search for `showToast()` function calls
- Update the message strings as needed

### Customizing Highlight Colors

Radio/checkbox highlighting colors can be changed in `assets/css/vendor-registration.css`:
- Look for `.vrf-field-required` class
- Change border color, background, etc.

---

## Support

### Before Requesting Support

1. Check that WordPress and all plugins are up to date
2. Clear your browser cache
3. Test in incognito/private browsing mode
4. Check browser console for JavaScript errors
5. Enable debug logging and check logs

### Documentation Files

- **README.md** - Full feature documentation
- **ENHANCEMENTS.md** - Detailed guide to new features
- **TESTING.md** - Comprehensive testing guide
- **SECURITY_SUMMARY.md** - Security review and recommendations
- **IMPLEMENTATION_SUMMARY.md** - Technical implementation details

---

## Version History

### Version 1.1 (Current - October 2025)
- Added field names to toast alerts
- Added visual highlighting for required radio/checkbox groups
- Implemented user acknowledgement emails
- Implemented admin notification emails with attachments
- Fixed XSS vulnerability in toast notifications
- Added error logging for email failures

### Version 1.0 (Previous)
- Multi-step form with validation
- File upload with size/type checking
- WordPress custom post type storage
- Admin dashboard with download links

---

## Quick Reference

### Form Steps
1. **Basic Info** - Organization details, address, contacts
2. **GST** - GST registration information
3. **MSME** - MSME/Udyam registration
4. **Bank Details** - Banking information
5. **TDS** - PAN card details

### Required Field Indicators
- **Text fields**: Red border when empty
- **Radio/Checkbox**: Red left border + light red background when not selected
- **File uploads**: Error toast if wrong size/format

### Toast Notification Colors
- **Green** - Success (form submitted)
- **Red** - Error (validation failed)
- **Blue** - Info (processing)

---

## Next Steps

1. Test the form with sample data
2. Submit a test registration
3. Verify emails are received
4. Check admin dashboard for the submission
5. Review attached files in admin email

---

## Need Help?

If you encounter any issues:
1. Check the documentation files
2. Enable debug logging
3. Check WordPress admin email settings
4. Consider installing WP Mail SMTP plugin
5. Review the TROUBLESHOOTING section in ENHANCEMENTS.md

---

**Thank you for using the Vendor Registration Form!**

For detailed information about any feature, please refer to the respective documentation files.
