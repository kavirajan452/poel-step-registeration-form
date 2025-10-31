# Vendor Registration Form - WordPress Plugin

A comprehensive multi-step vendor registration form plugin for WordPress that collects vendor information, validates inputs in real-time, and stores submissions in a custom post type.

## Features

### 1. Multi-Step Form Interface
- 5-step registration process:
  - **Step 1**: Basic Information (Organisation details, address, contacts)
  - **Step 2**: GST Registration
  - **Step 3**: MSME Registration
  - **Step 4**: Bank Details
  - **Step 5**: TDS Information
- Visual step indicators with navigation
- Form validation before proceeding to next step

### 2. Dynamic Location Selection
- **Country Dropdown**: Select from India, USA, or UK
- **State Dropdown**: Populated dynamically based on country selection
- **City Dropdown**: Populated dynamically based on state selection
- AJAX-powered real-time data loading

### 3. Real-Time Field Validation
- **Email Validation**: Checks proper email format
- **Phone Validation**: Validates 10-digit phone numbers
- **PAN Card**: Validates Indian PAN format (e.g., ABCDE1234F)
- **GST Number**: Validates 15-character GST format
- **IFSC Code**: Validates bank IFSC format
- **Required Fields**: Highlights missing required fields
- Visual feedback with error messages

### 4. File Upload Management
- **File Size Limit**: Maximum 2MB per file
- **Allowed Formats**: JPG, JPEG, PDF only
- **Real-Time Validation**: Validates size and format before submission
- **Secure Handling**: Server-side validation with MIME type checking
- Upload fields for:
  - Company Registration/Trade License
  - GST Certificate
  - Udyam Certificate
  - Bank Proof/Cancelled Cheque
  - PAN Card

### 5. Toast Notifications
- **Success Messages**: Green toast for successful submissions
- **Error Messages**: Red toast for validation errors
- **Info Messages**: Blue toast for progress updates
- Auto-dismiss after 3 seconds
- Smooth animations

### 6. Admin Dashboard Features
- **Custom Post Type**: Stores all registrations in 'registrations' CPT
- **Custom Columns**:
  - Form Type (Vendor/Customer)
  - Organisation Name
  - Contact Information
  - Submission Date
  - Uploaded Files with download links
- **Filtering**: Filter registrations by form type
- **Download Links**: Direct download access to all uploaded documents

### 7. Security Features
- CSRF protection with WordPress nonces
- Sanitized input data
- Secure file upload handling
- MIME type validation
- File size restrictions

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the shortcode `[vendor_registration_form]` on any page or post

## Usage

### Frontend - Display Form
```
[vendor_registration_form]
```

### Backend - View Registrations
1. Navigate to **Registrations** in the WordPress admin menu
2. View all submitted vendor registrations
3. Use the **Form Type** filter to filter by vendor or customer
4. Click on download links to access uploaded documents

## Technical Details

### Files Structure
```
├── index.php                           # Main plugin file
├── assets/
│   ├── css/
│   │   └── vendor-registration.css     # Styling
│   ├── js/
│   │   └── vendor-registration.js      # Frontend logic
│   └── reference-screenshot/           # UI reference screenshots
└── README.md                            # Documentation
```

### Custom Post Type
- **Name**: registrations
- **Public**: No (admin only)
- **Supports**: Title
- **Icon**: dashicons-clipboard

### Meta Fields Stored
All form data is stored as post meta:
- `form_type` - Type of registration (vendor/customer)
- `organisation_name` - Company name
- `company_registration_number` - Registration/license number
- `iec_code` - Import/Export code
- `street_address`, `street_address_2` - Address lines
- `country`, `state`, `city`, `zip` - Location details
- `vendor_type` - Array of vendor types
- `products` - Products/services offered
- Contact person details (purchase and accounts)
- GST registration details
- MSME registration details
- Bank details
- TDS information
- File attachment IDs

### AJAX Endpoints
- `vrf_submit` - Form submission handler
- `vrf_get_states` - Get states for selected country
- `vrf_get_cities` - Get cities for selected state

## Form Validation Rules

### Email Fields
- Must be valid email format: `user@example.com`

### Phone Fields
- Must be 10 digits
- Accepts formats: `1234567890`, `(123) 456-7890`, `123-456-7890`

### PAN Number
- Format: `ABCDE1234F`
- 5 letters + 4 digits + 1 letter

### GST Number
- Format: 15 characters
- Example: `22AAAAA0000A1Z5`

### IFSC Code
- Format: `ABCD0123456`
- 4 letters + 0 + 6 alphanumeric characters

### File Uploads
- **Max Size**: 2MB
- **Formats**: JPG, JPEG, PDF
- Validated on both client and server side

## Browser Support
- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11+ (with jQuery support)

## Dependencies
- WordPress 5.0+
- jQuery (included with WordPress)
- PHP 7.0+

## Future Enhancements
This plugin is designed to be extensible. Future versions may include:
- Customer registration form variant
- Export functionality for registrations
- Email notifications
- Custom email templates
- Additional location data
- Integration with CRM systems

## Author
Developed with GitHub Copilot

## Version
1.0

## License
This plugin is provided as-is for the specific use case described.
