# Testing Guide for Vendor Registration Form

## Overview
This document outlines all the validations and features that need to be tested for the multi-step vendor registration form.

## Step 1: Basic Information

### Fields to Test:
1. **Organisation Name** (Required)
   - Should show validation error if left empty
   - Placeholder text: "Company/Trade License/GST Registration Name"

2. **Company Registration / Trade License Number** (Optional)
   - Can be left empty

3. **Company Registration / Trade License File Upload** (Required)
   - Must upload a file
   - File size: Maximum 2MB
   - Allowed formats: JPG, JPEG, PDF
   - Should show error for oversized files or invalid formats

4. **Importer / Exporter Code** (Optional)
   - Can be left empty

5. **Street Address** (Optional)
   - Can be left empty

6. **Street Address Line 2** (Optional)
   - Can be left empty

7. **Country** (Default: India)
   - Should default to "India"
   - Changing country should load respective states

8. **State** (Required)
   - Must select a state
   - Options dynamically loaded based on country
   - India selected by default should auto-populate states

9. **City** (Required)
   - Must select a city
   - Options dynamically loaded based on state

10. **Zip Code** (Optional)
    - Can be left empty

11. **Vendor Type** (Required - At least one checkbox)
    - Options: Goods Supplier, Service Supplier, Transporter, Other
    - Must select at least one checkbox
    - Should show validation error if none selected

12. **Product / Services Offered to POEL** (Required)
    - Must fill textarea
    - Should show validation error if left empty

13. **Purchase Contact Person Name** (Required)
    - Must fill
    - Should show validation error if left empty

14. **Purchase Contact Person Phone No** (Required)
    - Must fill
    - Format: 10 digits
    - Should show format error if invalid

15. **Purchase Contact Person Email** (Required)
    - Must fill
    - Format: Valid email (user@domain.com)
    - Should show format error if invalid

16. **Accounts Contact Person Name** (Required)
    - Must fill
    - Should show validation error if left empty

17. **Accounts Contact Person Phone No** (Required)
    - Must fill
    - Format: 10 digits
    - Should show format error if invalid

18. **Accounts Contact Person Email** (Required)
    - Must fill
    - Format: Valid email (user@domain.com)
    - Should show format error if invalid

### Navigation:
- "Next" button should validate all required fields before proceeding
- Should show toast error if validation fails

---

## Step 2: GST Registration

### Fields to Test:

1. **GST Registration** (Required Radio Button)
   - Options: Yes, No
   - Must select one option
   - Should show validation error if not selected

2. **Conditional Fields (Visible only if GST Registration = Yes)**:
   
   a. **GST Registration Number** (Required when GST=Yes)
      - Format: 15 characters (e.g., 22AAAAA0000A1Z5)
      - Should show format validation error if invalid
      
   b. **Legal Name (as per GST)** (Required when GST=Yes)
      - Must fill
      - Should show validation error if left empty
      
   c. **Tax Payer Type** (Required when GST=Yes)
      - Dropdown options: Regular, Composition
      - Must select an option
      
   d. **GST Certificate** (Required when GST=Yes)
      - Must upload file
      - File size: Maximum 2MB
      - Allowed formats: JPG, JPEG, PDF
      
   e. **E-Invoice Applicability** (Required when GST=Yes)
      - Radio options: Applicable, Non-Applicable
      - Must select one option
      
   f. **Return Filing Frequency** (Required when GST=Yes)
      - Radio options: Monthly, Quarterly
      - Must select one option

### Conditional Logic:
- All GST conditional fields should be hidden when "No" is selected
- All GST conditional fields should be visible when "Yes" is selected
- Required validation should only apply to visible fields

### Navigation:
- "Back" button should go to Step 1
- "Next" button should validate all required fields before proceeding

---

## Step 3: MSME Registration

### Fields to Test:

1. **MSME (Udyam Registration)** (Required Radio Button)
   - Options: Yes, No
   - Must select one option
   - Should show validation error if not selected

2. **Conditional Fields (Visible only if MSME = Yes)**:
   
   a. **MSME Type** (Required when MSME=Yes)
      - Dropdown options: Micro, Small, Medium
      - Must select an option
      
   b. **Udyam Registration Number** (Required when MSME=Yes)
      - Format: UDYAM-XX-00-0000000 (19 characters)
      - Should show format validation error if invalid
      
   c. **Udyam Certificate** (Required when MSME=Yes)
      - Must upload file
      - File size: Maximum 2MB
      - Allowed formats: JPG, JPEG, PDF

3. **Conditional Fields (Visible only if MSME = No)**:
   
   a. **MSME Declaration Form** (Download Link)
      - Should provide downloadable template
      - Link should work and download the form
      
   b. **Signed Copy of Declaration** (Required when MSME=No)
      - Must upload signed declaration file
      - File size: Maximum 2MB
      - Allowed formats: JPG, JPEG, PDF

### Conditional Logic:
- MSME=Yes fields should be hidden when "No" is selected
- MSME=No fields should be hidden when "Yes" is selected
- Required validation should only apply to visible fields
- Switching between Yes/No should clear previous selections

### Navigation:
- "Back" button should go to Step 2
- "Next" button should validate all required fields before proceeding

---

## Step 4: Bank Details

### Fields to Test:

1. **Beneficiary Name** (Required)
   - Must fill
   - Should show validation error if left empty

2. **Bank Name** (Required)
   - Must fill
   - Should show validation error if left empty

3. **Branch Name** (Required)
   - Must fill
   - Should show validation error if left empty

4. **Bank IFSC Code** (Required)
   - Format: ABCD0123456 (4 letters + 0 + 6 alphanumeric)
   - Should show format validation error if invalid

5. **Bank Account Number** (Required)
   - Format: 9-18 digits
   - Should show format validation error if invalid

6. **Cancelled Cheque Leaf / Bank Details in Company Letterhead** (Required)
   - Must upload file
   - File size: Maximum 2MB
   - Allowed formats: JPG, JPEG, PDF

### Navigation:
- "Back" button should go to Step 3
- "Next" button should validate all required fields before proceeding

---

## Step 5: TDS Details

### Fields to Test:

1. **PAN** (Required)
   - Format: ABCDE1234F (5 letters + 4 digits + 1 letter)
   - Should show format validation error if invalid

2. **PAN Type** (Required)
   - Dropdown options: Individual, Company, Partnership, HUF, Trust, Other
   - Must select an option

3. **PAN Card** (Required)
   - Must upload file
   - File size: Maximum 2MB
   - Allowed formats: JPG, JPEG, PDF

### Navigation:
- "Back" button should go to Step 4
- "Submit" button should validate all required fields and submit the form

---

## General Features to Test

### Real-Time Validation:
- All fields should validate as user types/selects
- Error messages should appear below invalid fields
- Fields should be highlighted with red border when invalid
- Error messages should disappear when field becomes valid

### Toast Notifications:
- Success toast (green) for successful submission
- Error toast (red) for validation errors
- Info toast (blue) for processing messages
- Toasts should auto-dismiss after 3 seconds

### File Upload Validation:
- Should reject files larger than 2MB
- Should reject files not in JPG, JPEG, or PDF format
- Should show error toast immediately on invalid file selection
- Should clear file input if validation fails

### Step Navigation:
- Step indicators should show current active step
- Clicking on step indicators should navigate to that step
- Back buttons should work on all steps except Step 1
- Next buttons should validate before proceeding

### Form Submission:
- Should build FormData including all form fields and files
- Should submit via AJAX to WordPress backend
- Should show success message on successful submission
- Should show error message on submission failure
- Should reset form after successful submission

### Responsive Design:
- Form should work on mobile devices
- Toast notifications should adjust for mobile screens
- Form layout should be responsive

---

## Test Scenarios

### Scenario 1: Complete Valid Submission
1. Fill all required fields in Step 1 correctly
2. Select "Yes" for GST and fill all GST fields
3. Select "Yes" for MSME and fill all MSME fields
4. Fill all Bank Details correctly
5. Fill all TDS Details correctly
6. Submit form
7. Verify success message

### Scenario 2: GST Not Registered
1. Fill Step 1 correctly
2. Select "No" for GST
3. Verify GST conditional fields are hidden
4. Continue to next steps
5. Complete and submit form

### Scenario 3: MSME Not Registered
1. Fill Step 1 and 2 correctly
2. Select "No" for MSME
3. Verify MSME declaration download link appears
4. Download declaration form
5. Upload signed declaration
6. Complete and submit form

### Scenario 4: Validation Errors
1. Try to proceed from Step 1 without filling required fields
2. Verify validation errors appear
3. Verify toast notification shows
4. Fill one field at a time and verify errors clear
5. Complete all steps with validation

### Scenario 5: File Upload Errors
1. Try to upload file larger than 2MB
2. Verify error message
3. Try to upload invalid file format (e.g., .txt)
4. Verify error message
5. Upload valid file and verify acceptance

### Scenario 6: Format Validation Errors
1. Enter invalid email format
2. Enter invalid phone number (not 10 digits)
3. Enter invalid PAN format
4. Enter invalid GST number
5. Enter invalid IFSC code
6. Enter invalid Udyam number
7. Verify all show appropriate error messages

---

## Expected Backend Behavior

### Data Storage:
- All form data should be saved as post meta in 'registrations' custom post type
- Uploaded files should be saved to WordPress media library
- File attachment IDs should be saved in post meta
- Post title should be Organisation Name

### Admin Dashboard:
- Registrations should appear in admin menu
- Custom columns should show:
  - Form Type
  - Organisation Name
  - Contact Info
  - Submitted Date
  - Uploaded Files (with download links)
- Filter dropdown for form type should work
- All uploaded files should have working download links

---

## Security Validations (Server-Side)

### File Upload:
- Server validates file size (max 2MB)
- Server validates MIME type (jpg, jpeg, pdf only)
- Files stored securely in WordPress media library

### Data Sanitization:
- All text fields sanitized
- Array data (vendor_type) properly sanitized
- Nonce verification for AJAX requests

### Access Control:
- AJAX endpoints require proper nonce
- Custom post type only accessible to admins
