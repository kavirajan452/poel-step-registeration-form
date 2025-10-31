# Customer Registration Form

This document explains how to use the new customer registration form feature.

## Usage

### Shortcode
To display the customer registration form on any WordPress page or post, use the following shortcode:

```
[customer_registration_form]
```

### Form Structure

The customer registration form consists of 3 steps:

#### Step 1: Basic Information
- Organisation Name (Required)
- Company Registration / Trade License Number (Required)
- Company Registration / Trade License File Upload (Required)
- Importer / Exporter Code (Optional)
- Street Address (Required)
- Street Address Line 2 (Optional)
- Country (Required, Default: India)
- State (Required, dynamically populated based on country)
- City (Required, dynamically populated based on state)
- Zip Code (Optional)
- Customer Type (Required, Checkboxes: Goods, Services)
- Purchase Contact Person Details (Name, Phone, Email - All Required)
- Accounts Contact Person Details (Name, Phone, Email - All Required)

#### Step 2: GST Registration
- GST Registration (Required, Radio: Yes/No)
- If "Yes" is selected:
  - GST Registration Number (Required, validated format)
  - Legal Name (as per GST) (Optional)
  - Tax Payer Type (Optional, Dropdown: Regular, Composition)
  - GST Certificate (Required, File upload)

#### Step 3: TDS Details
- PAN (Required, validated format: ABCDE1234F)
- PAN Type (Required, Dropdown: Individual, Company, Partnership, HUF, Trust, Other)
- PAN Card (Required, File upload)
- TAN Number (Required, validated format: ABCD12345E)

## Validation Rules

### Field Formats

#### Email
- Must be valid email format: `user@example.com`

#### Phone
- Must be 10 digits
- Accepts formats: `1234567890`, `(123) 456-7890`, `123-456-7890`

#### PAN Number
- Format: `ABCDE1234F`
- 5 uppercase letters + 4 digits + 1 uppercase letter

#### TAN Number
- Format: `ABCD12345E`
- 4 uppercase letters + 5 digits + 1 uppercase letter

#### GST Number
- Format: 15 characters
- Example: `22AAAAA0000A1Z5`

### File Uploads
- **Maximum Size**: 2MB per file
- **Allowed Formats**: JPG, JPEG, PDF
- Client-side and server-side validation

## Features

### Real-Time Validation
- Instant feedback on field validation
- Visual indicators for invalid fields
- Error messages below invalid fields

### Toast Notifications
- Success messages (green)
- Error messages (red)
- Info messages (blue)
- Auto-dismiss after 3 seconds

### Dynamic Location Selection
- Country selection updates available states
- State selection updates available cities
- AJAX-powered real-time data loading

### Email Notifications
- **User Acknowledgement**: Sent to customer's purchase contact email
- **Admin Notification**: Detailed email with all form data and file attachments

### Data Storage
- All submissions stored in WordPress custom post type: `registrations`
- Form type field distinguishes customer from vendor registrations
- All uploaded files stored in WordPress media library

## Admin Dashboard

### Viewing Customer Registrations
1. Navigate to **Registrations** in WordPress admin menu
2. Use the **Form Type** filter and select "Customer"
3. View all customer registration details
4. Download uploaded documents directly from the listing

### Registration Details
When viewing a customer registration, you'll see organized sections:
- Basic Information
- Contact Information
- GST Information
- TDS Information
- Uploaded Files (with download links)

## Differences from Vendor Form

| Feature | Vendor Form | Customer Form |
|---------|-------------|---------------|
| Steps | 5 (Basic, GST, MSME, Bank, TDS) | 3 (Basic, GST, TDS) |
| Type Field | Vendor Type (4 options) | Customer Type (2 options) |
| Products/Services | Required | Not included |
| MSME Registration | Included | Not included |
| Bank Details | Included | Not included |
| TAN Number | Not included | Included (Required) |

## Technical Details

### Form ID
- `customer-registration-form`

### JavaScript File
- `/assets/js/customer-registration.js`

### CSS File
- Uses same stylesheet as vendor form: `/assets/css/vendor-registration.css`

### AJAX Actions
- `vrf_submit` - Form submission (shared with vendor form)
- `vrf_get_states` - Get states for country (shared)
- `vrf_get_cities` - Get cities for state (shared)

## Example Usage

To add the customer registration form to a page:

1. Create a new page in WordPress or edit an existing one
2. Add the shortcode: `[customer_registration_form]`
3. Publish the page
4. Customers can now fill out and submit the registration form

## Support

For issues or questions about the customer registration form, please refer to the main plugin documentation or contact the plugin administrator.
