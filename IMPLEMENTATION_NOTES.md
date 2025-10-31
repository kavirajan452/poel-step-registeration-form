# Customer Registration Form - Implementation Notes

## Overview
This document provides technical details about the customer registration form implementation.

## Implementation Date
October 31, 2025

## Version
1.1

## Files Modified/Created

### Created Files
1. **assets/js/customer-registration.js** - Frontend JavaScript for customer form
2. **CUSTOMER_FORM_USAGE.md** - User documentation for customer form
3. **IMPLEMENTATION_NOTES.md** - This file

### Modified Files
1. **index.php** - Added customer form shortcode, rendering method, and updated submission handler
2. **README.md** - Updated to include customer form information

## Key Implementation Details

### 1. Form Structure
The customer registration form follows a 3-step structure:
- **Step 1**: Basic Information (17 fields)
- **Step 2**: GST Registration (5 conditional fields)
- **Step 3**: TDS Details (4 fields)

### 2. Shortcode
- **Shortcode Name**: `[customer_registration_form]`
- **PHP Method**: `render_customer_shortcode()`
- **Form ID**: `customer-registration-form`

### 3. Field Naming Conventions
- Country dropdown: `#crf-country` (Customer Registration Form)
- State dropdown: `#crf-state`
- City dropdown: `#crf-city`
- Customer type checkboxes: `.vrf-customer-type`

### 4. Validation Implementation

#### Client-Side Validation (JavaScript)
```javascript
// Email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
validateEmail(email)

// Phone: /^[0-9]{10}$/
validatePhone(phone)

// PAN: /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/
validatePAN(pan)

// TAN: /^[A-Z]{4}[0-9]{5}[A-Z]{1}$/
validateTAN(tan)

// GST: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/
validateGST(gst)

// File: Max 2MB, jpg/jpeg/pdf only
validateFile(input)
```

#### Server-Side Validation (PHP)
- All fields sanitized using `sanitize_text_field()`
- File uploads validated for:
  - Size: 2MB maximum
  - MIME type: image/jpeg, image/jpg, application/pdf
  - Uses `finfo_open()` for accurate MIME detection

### 5. Data Storage

#### Post Meta Fields
```php
// Basic Information
organisation_name
company_registration_number (required for customer, optional for vendor)
iec_code
street_address
street_address_2
country
state
city
zip
customer_type (array: Goods, Services)

// Contact Information
purchase_contact_name
purchase_contact_phone
purchase_contact_email
accounts_contact_name
accounts_contact_phone
accounts_contact_email

// GST Information
gst_registered
gst_number
gst_legal_name
taxpayer_type

// TDS Information
pan_number
pan_type
tan_number (new field for customer form)

// File Attachments (stored as attachment IDs)
company_registration_file
gst_certificate
pan_card

// Metadata
form_type (value: "customer")
_vrf_raw_post (JSON-encoded raw POST data)
```

### 6. AJAX Handlers
The customer form uses the existing AJAX handlers:
- **vrf_submit** - Form submission (shared with vendor form)
- **vrf_get_states** - Get states for country selection
- **vrf_get_cities** - Get cities for state selection

### 7. Email Notifications

#### User Acknowledgement Email
- Sent to: `purchase_contact_email`
- Subject: "Thank You for Your Customer Registration - {org_name}"
- Content: HTML template with POEL branding
- Template Method: `get_user_email_template()`

#### Admin Notification Email
- Sent to: WordPress admin email
- Subject: "New Customer Registration: {org_name}"
- Content: Detailed HTML template with all form data
- Attachments: All uploaded files
- Template Method: `get_admin_email_template()`

### 8. Conditional Field Logic

#### GST Registration (Step 2)
```javascript
// When "Yes" is selected:
- GST Registration Number (required, validated)
- Legal Name (as per GST) (optional)
- Tax Payer Type (optional)
- GST Certificate (required, file upload)

// When "No" is selected:
- All GST fields hidden and not required
```

### 9. Code Quality Improvements

#### Constants
```javascript
var MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
var ALLOWED_FILE_TYPES = ['image/jpeg', 'image/jpg', 'application/pdf'];
```

#### Conditional Script Loading
```php
// Only enqueue vendor-registration-js if vendor form present
// Only enqueue customer-registration-js if customer form present
// Always enqueue vendor-registration-css (shared stylesheet)
```

## Differences from Vendor Form

| Feature | Vendor Form | Customer Form |
|---------|-------------|---------------|
| Steps | 5 | 3 |
| Step 1 Type Field | Vendor Type (4 options) | Customer Type (2 options) |
| Products/Services | Required textarea | Not included |
| MSME Registration | Step 3 - Full section | Not included |
| Bank Details | Step 4 - Full section | Not included |
| TAN Number | Not included | Step 3 - Required field |
| Company Reg Number | Optional | Required |

## Security Considerations

1. **CSRF Protection**: WordPress nonces used for all AJAX requests
2. **Input Sanitization**: All user inputs sanitized before storage
3. **File Upload Security**:
   - MIME type validation using finfo
   - File size restrictions
   - Allowed file types whitelist
4. **XSS Prevention**: All output escaped using `esc_html()`, `esc_url()`, etc.
5. **SQL Injection Prevention**: WordPress APIs used for database operations

## Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11+ (with jQuery support)
- Mobile responsive (inherited from vendor form CSS)

## Dependencies
- WordPress 5.0+
- jQuery (included with WordPress)
- PHP 7.0+

## Testing Checklist
- [ ] Shortcode renders correctly on page
- [ ] All form fields display properly
- [ ] Country dropdown triggers state loading
- [ ] State dropdown triggers city loading
- [ ] Customer type checkboxes validate (at least one required)
- [ ] GST conditional fields show/hide correctly
- [ ] All field validations work (email, phone, PAN, TAN, GST)
- [ ] File upload validation works (size, type)
- [ ] Form submission creates post in admin
- [ ] User receives acknowledgement email
- [ ] Admin receives notification email with attachments
- [ ] Admin can filter registrations by form type
- [ ] All uploaded files are downloadable from admin

## Known Limitations
1. State/city data is hardcoded for major locations only
2. Email sending depends on WordPress mail configuration
3. File uploads limited to WordPress media library capabilities

## Future Enhancements Possible
1. Add more countries/states/cities
2. Implement CSV export for customer registrations
3. Add customer-specific email templates
4. Implement duplicate registration checking
5. Add registration approval workflow
6. Create customer portal for viewing submission status

## Support & Maintenance
For questions or issues related to the customer registration form:
1. Check CUSTOMER_FORM_USAGE.md for usage instructions
2. Review this implementation notes for technical details
3. Check WordPress error logs for server-side issues
4. Use browser console for client-side JavaScript issues

## Change Log

### Version 1.1 (October 31, 2025)
- Added customer registration form with 3-step structure
- Implemented TAN number validation
- Added customer_type field (Goods/Services checkboxes)
- Updated email templates to support both form types
- Improved code quality with constants and conditional loading
- Added comprehensive documentation
