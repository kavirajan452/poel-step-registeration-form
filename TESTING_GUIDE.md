# Testing Guide for Version 1.2

This guide provides instructions for testing the new features added in version 1.2.

## New Features to Test

1. Email Configuration Settings
2. Registration Protection (Edit/Trash Prevention)

## Prerequisites

- WordPress installation with the plugin activated
- Admin access to the WordPress dashboard
- Test email addresses for verification
- At least one test registration submission

## Test 1: Email Settings Page Access

### Steps:
1. Log in to WordPress admin as an administrator
2. Navigate to **Registrations** in the admin menu
3. Click on **Email Settings** submenu

### Expected Results:
✅ Email Settings page loads successfully
✅ Page displays two sections: "Vendor Form Email Settings" and "Customer Form Email Settings"
✅ Each section has fields for: Admin Email Recipients, CC Recipients, BCC Recipients
✅ Admin Email fields show default values (WordPress admin email)
✅ Form has "Save Email Settings" button

## Test 2: Email Settings Validation

### Test 2.1: Required Field Validation

#### Steps:
1. Navigate to Email Settings page
2. Clear the "Vendor Form - Admin Email Recipients" field
3. Click "Save Email Settings"

#### Expected Results:
✅ Error message appears: "Admin email recipients are required for both vendor and customer forms"
✅ Settings are not saved

### Test 2.2: Email Format Validation

#### Steps:
1. Enter invalid email format in any field (e.g., "notanemail")
2. Click "Save Email Settings"

#### Expected Results:
✅ Invalid emails are filtered out during save
✅ Only valid email addresses are saved
✅ Success message appears if required fields have valid emails

### Test 2.3: Multiple Email Addresses

#### Steps:
1. Enter multiple comma-separated emails: `admin1@test.com, admin2@test.com, admin3@test.com`
2. Add spaces before/after commas: `admin1@test.com , admin2@test.com , admin3@test.com`
3. Click "Save Email Settings"

#### Expected Results:
✅ All valid emails are saved
✅ Whitespace is trimmed automatically
✅ Success message: "Email settings saved successfully"

## Test 3: Email Configuration Usage

### Test 3.1: Vendor Form Submission

#### Steps:
1. Configure vendor form email settings with:
   - Admin Email: `admin@test.com`
   - CC: `cc@test.com`
   - BCC: `bcc@test.com`
2. Submit a test vendor registration form
3. Check email inboxes

#### Expected Results:
✅ Admin email is sent to `admin@test.com`
✅ CC recipient `cc@test.com` receives a copy
✅ BCC recipient `bcc@test.com` receives a blind copy
✅ User receives acknowledgement email
✅ Admin email includes all form data and attachments

### Test 3.2: Customer Form Submission

#### Steps:
1. Configure customer form email settings with different addresses
2. Submit a test customer registration form
3. Check email inboxes

#### Expected Results:
✅ Emails sent to configured customer form recipients
✅ Separate from vendor form configuration
✅ Both forms work independently

## Test 4: Registration Protection - View Access

### Steps:
1. Navigate to **Registrations** in admin menu
2. View the list of registrations
3. Hover over any registration entry

### Expected Results:
✅ Registrations list displays normally
✅ "Edit" link is NOT visible
✅ "Trash" link is NOT visible
✅ "Quick Edit" option is NOT available
✅ "View" option (if any) still works

## Test 5: Registration Protection - Edit Prevention

### Test 5.1: Direct URL Access

#### Steps:
1. Get the ID of any registration (e.g., ID: 123)
2. Try to access edit URL directly: `wp-admin/post.php?post=123&action=edit`

#### Expected Results:
✅ Edit screen does not load normally
✅ User lacks permission or capability to edit
✅ No ability to modify registration data

### Test 5.2: Bulk Actions

#### Steps:
1. Navigate to Registrations list
2. Select one or more registrations
3. Check available bulk actions dropdown

#### Expected Results:
✅ "Edit" option not available in bulk actions
✅ "Move to Trash" option not available in bulk actions
✅ Only view and export options available

## Test 6: Registration Protection - Trash Prevention

### Steps:
1. Navigate to Registrations list
2. Try to find trash/delete options
3. Check for trash link in row actions
4. Check for trash in bulk actions

### Expected Results:
✅ No trash link visible for any registration
✅ No delete option available
✅ Bulk trash action not available
✅ Registrations remain in the list permanently

## Test 7: Data Integrity Verification

### Steps:
1. View a registration's details (using meta boxes if viewing is possible)
2. Note down the data
3. Wait some time or perform other actions
4. Return and view the same registration

### Expected Results:
✅ All data remains unchanged
✅ No modifications possible
✅ Data integrity maintained

## Test 8: Export Functionality (Should Still Work)

### Steps:
1. Navigate to **Registrations > Export**
2. Select export format and options
3. Export registrations

### Expected Results:
✅ Export functionality still works
✅ All registration data exports correctly
✅ Read-only nature doesn't affect export

## Test 9: Different User Roles

### Test 9.1: Administrator

#### Expected Results:
✅ Can view Email Settings
✅ Can save Email Settings
✅ Cannot edit registrations
✅ Cannot trash registrations

### Test 9.2: Editor/Other Roles (if they have access)

#### Expected Results:
✅ Cannot access Email Settings (requires 'manage_options')
✅ Can view registrations (if permissions allow)
✅ Cannot edit registrations
✅ Cannot trash registrations

## Test 10: Security Checks

### Test 10.1: CSRF Protection

#### Steps:
1. Try to submit Email Settings form without nonce
2. Try to submit with invalid nonce

#### Expected Results:
✅ Form submission fails
✅ Security check prevents unauthorized changes

### Test 10.2: XSS Prevention

#### Steps:
1. Try to enter HTML/JavaScript in email fields: `<script>alert('test')</script>`
2. Save settings
3. View the saved value

#### Expected Results:
✅ Script tags are not executed
✅ Email validation rejects invalid input
✅ Only valid emails are saved

## Test 11: Email Header Injection Prevention

### Steps:
1. Try to enter email with newlines or headers: `admin@test.com\nCc: hacker@test.com`
2. Save settings

### Expected Results:
✅ Invalid email is rejected
✅ Email validation prevents header injection
✅ Only properly formatted emails are accepted

## Automated Testing Checklist

If implementing automated tests, verify:

- [ ] Email settings page loads
- [ ] Required field validation works
- [ ] Email format validation works
- [ ] Multiple emails can be saved
- [ ] Emails are sanitized properly
- [ ] Edit capability is removed
- [ ] Trash capability is removed
- [ ] Row actions are filtered correctly
- [ ] Email sending uses configured settings
- [ ] CC/BCC headers are added correctly

## Known Limitations

1. **No Import Function**: Registrations cannot be imported (by design)
2. **No Database UI for Deletion**: Database access required for deletion
3. **Read-Only View**: Even admins cannot modify submissions through UI
4. **Email Sending**: Depends on WordPress email configuration

## Troubleshooting

### Email Settings Not Saving
- Check user has 'manage_options' capability
- Verify nonce is valid
- Check for JavaScript errors in browser console
- Ensure valid email format

### Emails Not Received
- Check WordPress email configuration
- Verify SMTP settings
- Check spam folders
- Enable WP_DEBUG to see error logs

### Edit Links Still Visible
- Clear browser cache
- Check if another plugin is interfering
- Verify plugin is latest version

## Success Criteria

All tests pass if:
- ✅ Email settings page is accessible and functional
- ✅ Email validation works correctly
- ✅ Configured emails are used for notifications
- ✅ Edit links are completely removed
- ✅ Trash links are completely removed
- ✅ Registrations cannot be modified through UI
- ✅ Data integrity is maintained
- ✅ Security measures are effective

## Reporting Issues

If any test fails:
1. Document the exact steps taken
2. Note the expected vs actual result
3. Include browser/WordPress version
4. Check error logs
5. Report with full details
