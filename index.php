<?php
/*
Plugin Name: Vendor & Customer Registration Forms
Description: Multi-step vendor and customer registration forms with shortcodes, stores submissions as custom post type 'registrations' with file attachments and displays downloads in admin.
Version: 1.1
Author: GitHub Copilot (adapted)
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class VRF_Plugin {

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_shortcode( 'vendor_registration_form', [ $this, 'render_shortcode' ] );
        add_shortcode( 'customer_registration_form', [ $this, 'render_customer_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_ajax_vrf_submit', [ $this, 'handle_ajax_submit' ] );
        add_action( 'wp_ajax_nopriv_vrf_submit', [ $this, 'handle_ajax_submit' ] );
        add_action( 'wp_ajax_vrf_get_states', [ $this, 'ajax_get_states' ] );
        add_action( 'wp_ajax_nopriv_vrf_get_states', [ $this, 'ajax_get_states' ] );
        add_action( 'wp_ajax_vrf_get_cities', [ $this, 'ajax_get_cities' ] );
        add_action( 'wp_ajax_nopriv_vrf_get_cities', [ $this, 'ajax_get_cities' ] );

        // Admin columns to show uploaded files with download links
        add_filter( 'manage_registrations_posts_columns', [ $this, 'admin_columns' ] );
        add_action( 'manage_registrations_posts_custom_column', [ $this, 'admin_columns_content' ], 10, 2 );
        add_filter( 'manage_edit-registrations_sortable_columns', function($cols){ return $cols; } );
        add_filter( 'restrict_manage_posts', [ $this, 'admin_filter_dropdown' ] );
        add_filter( 'parse_query', [ $this, 'admin_filter_query' ] );
        
        // Admin meta boxes for post edit screen
        add_action( 'add_meta_boxes', [ $this, 'add_registration_meta_boxes' ] );
    }

    // Register CPT "registrations"
    public function register_post_type() {
        $labels = array(
            'name'               => 'Registrations',
            'singular_name'      => 'Registration',
            'menu_name'          => 'Registrations',
            'name_admin_bar'     => 'Registration',
        );
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'capability_type'    => 'post',
            'supports'           => array( 'title' ),
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-clipboard',
        );
        register_post_type( 'registrations', $args );
    }

    // Enqueue JS/CSS
    public function enqueue_assets() {
        // Check if page has either shortcode
        $content = get_post_field( 'post_content', get_the_ID() );
        $has_vendor_form = has_shortcode( $content, 'vendor_registration_form' );
        $has_customer_form = has_shortcode( $content, 'customer_registration_form' );
        
        if ( ! is_singular() && ! is_page() && ! $has_vendor_form && ! $has_customer_form ) {
            // Don't enqueue if no shortcode present
            return;
        }
        
        // Register scripts
        wp_register_script( 'vendor-registration-js', plugin_dir_url( __FILE__ ) . 'assets/js/vendor-registration.js', array('jquery'), '1.1', true );
        wp_register_script( 'customer-registration-js', plugin_dir_url( __FILE__ ) . 'assets/js/customer-registration.js', array('jquery'), '1.1', true );
        wp_register_style( 'vendor-registration-css', plugin_dir_url( __FILE__ ) . 'assets/css/vendor-registration.css', array(), '1.1' );
        
        // Enqueue CSS (shared by both forms)
        wp_enqueue_style( 'vendor-registration-css' );
        
        // Enqueue vendor JS only if vendor form is present
        if ( $has_vendor_form ) {
            wp_enqueue_script( 'vendor-registration-js' );
            wp_localize_script( 'vendor-registration-js', 'vrf_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'vrf_nonce' ),
            ) );
        }
        
        // Enqueue customer JS only if customer form is present
        if ( $has_customer_form ) {
            wp_enqueue_script( 'customer-registration-js' );
            wp_localize_script( 'customer-registration-js', 'vrf_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'vrf_nonce' ),
            ) );
        }
    }

    // Shortcode output (form)
    public function render_shortcode( $atts ) {
        ob_start();
        ?>
        <div class="vrf-container">
            <h2 class="vrf-title">VENDOR REGISTRATION FORM</h2>

            <div class="vrf-steps">
                <button class="vrf-step active" data-step="1">Basic Info</button>
                <button class="vrf-step" data-step="2">GST</button>
                <button class="vrf-step" data-step="3">MSME</button>
                <button class="vrf-step" data-step="4">Bank Details</button>
                <button class="vrf-step" data-step="5">TDS</button>
            </div>

            <form id="vendor-registration-form" class="vrf-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="action" value="vrf_submit" />
                <input type="hidden" name="vrf_nonce" value="<?php echo wp_create_nonce('vrf_nonce'); ?>" />
                <input type="hidden" name="form_type" value="vendor" />

                <!-- Step 1: Basic Info -->
                <div class="vrf-panel" data-panel="1">
                    <h3 class="vrf-section-title">Basic Information</h3>

                    <div class="vrf-row">
                        <label>Organisation Name *</label>
                        <input type="text" name="organisation_name" placeholder="Company/Trade License/GST Registration Name" required />
                    </div>

                    <div class="vrf-row">
                        <label>Company Registration / Trade License Number</label>
                        <input type="text" name="company_registration_number" />
                    </div>

                    <div class="vrf-row">
                        <label>Company Registration / Trade License File Upload *</label>
                        <input type="file" name="company_registration_file" required />
                    </div>

                    <div class="vrf-row">
                        <label>Importer / Exporter Code</label>
                        <input type="text" name="iec_code" />
                    </div>

                    <div class="vrf-row">
                        <label>Address - Street Address</label>
                        <input type="text" name="street_address" />
                    </div>

                    <div class="vrf-row">
                        <label>Street Address Line 2</label>
                        <input type="text" name="street_address_2" />
                    </div>

                    <div class="vrf-row">
                        <label>Country</label>
                        <select name="country" id="vrf-country">
                            <option value="">Select Country</option>
                            <?php
                            $countries = $this->get_countries();
                            foreach ( $countries as $country ) {
                                echo '<option value="' . esc_attr( $country['name'] ) . '">' . esc_html( $country['name'] ) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="vrf-row">
                        <label>State *</label>
                        <select name="state" id="vrf-state" required>
                            <option value="">Select State</option>
                        </select>
                    </div>

                    <div class="vrf-row">
                        <label>City *</label>
                        <select name="city" id="vrf-city" required>
                            <option value="">Select City</option>
                        </select>
                    </div>

                    <div class="vrf-row">
                        <label>Zip Code</label>
                        <input type="text" name="zip" />
                    </div>

                    <div class="vrf-row vrf-checks">
                        <label>Vendor Type *</label>
                        <label><input type="checkbox" name="vendor_type[]" value="Goods Supplier" class="vrf-vendor-type"> Goods Supplier</label>
                        <label><input type="checkbox" name="vendor_type[]" value="Service Supplier" class="vrf-vendor-type"> Service Supplier</label>
                        <label><input type="checkbox" name="vendor_type[]" value="Transporter" class="vrf-vendor-type"> Transporter</label>
                        <label><input type="checkbox" name="vendor_type[]" value="Other" class="vrf-vendor-type"> Other</label>
                    </div>

                    <div class="vrf-row">
                        <label>Product / Services Offered to POEL *</label>
                        <textarea name="products" rows="5" required></textarea>
                    </div>

                    <div class="vrf-row-inline">
                        <div>
                            <label>Purchase Contact Person Name *</label>
                            <input type="text" name="purchase_contact_name" required />
                        </div>
                        <div>
                            <label>Purchase Contact Person Phone No *</label>
                            <input type="text" name="purchase_contact_phone" required />
                        </div>
                        <div>
                            <label>Purchase Contact Person Email *</label>
                            <input type="email" name="purchase_contact_email" required />
                        </div>
                    </div>

                    <div class="vrf-row-inline">
                        <div>
                            <label>Accounts Contact Person Name *</label>
                            <input type="text" name="accounts_contact_name" required />
                        </div>
                        <div>
                            <label>Accounts Contact Person Phone No *</label>
                            <input type="text" name="accounts_contact_phone" required />
                        </div>
                        <div>
                            <label>Accounts Contact Person Email *</label>
                            <input type="email" name="accounts_contact_email" required />
                        </div>
                    </div>

                    <div class="vrf-actions">
                        <button type="button" class="vrf-next">Next</button>
                    </div>
                </div>

                <!-- Step 2: GST -->
                <div class="vrf-panel" data-panel="2" style="display:none;">
                    <h3 class="vrf-section-title">GST Registration</h3>

                    <div class="vrf-row">
                        <label>GST Registration *</label>
                        <label><input type="radio" name="gst_registered" value="yes" class="vrf-gst-radio" required> Yes</label>
                        <label><input type="radio" name="gst_registered" value="no" class="vrf-gst-radio" required> No</label>
                    </div>

                    <div class="vrf-gst-fields" style="display:none;">
                        <div class="vrf-row">
                            <label>GST Registration Number *</label>
                            <input type="text" name="gst_number" class="vrf-gst-conditional" />
                        </div>

                        <div class="vrf-row">
                            <label>Legal Name (as per GST) *</label>
                            <input type="text" name="gst_legal_name" class="vrf-gst-conditional" />
                        </div>

                        <div class="vrf-row">
                            <label>Tax Payer Type *</label>
                            <select name="taxpayer_type" class="vrf-gst-conditional">
                                <option value="">Please select</option>
                                <option value="Regular">Regular</option>
                                <option value="Composition">Composition</option>
                            </select>
                        </div>

                        <div class="vrf-row">
                            <label>GST Certificate *</label>
                            <input type="file" name="gst_certificate" class="vrf-gst-conditional" />
                        </div>

                        <div class="vrf-row">
                            <label>E-Invoice Applicability *</label>
                            <label><input type="radio" name="einvoice_applicability" value="Applicable" class="vrf-gst-conditional-radio"> Applicable</label>
                            <label><input type="radio" name="einvoice_applicability" value="Non-Applicable" class="vrf-gst-conditional-radio"> Non-Applicable</label>
                        </div>

                        <div class="vrf-row">
                            <label>Return Filing Frequency *</label>
                            <label><input type="radio" name="return_filing_frequency" value="Monthly" class="vrf-gst-conditional-radio"> Monthly</label>
                            <label><input type="radio" name="return_filing_frequency" value="Quarterly" class="vrf-gst-conditional-radio"> Quarterly</label>
                        </div>
                    </div>

                    <div class="vrf-actions">
                        <button type="button" class="vrf-back">Back</button>
                        <button type="button" class="vrf-next">Next</button>
                    </div>
                </div>

                <!-- Step 3: MSME -->
                <div class="vrf-panel" data-panel="3" style="display:none;">
                    <h3 class="vrf-section-title">MSME Registration</h3>

                    <div class="vrf-row">
                        <label>MSME (Udyam Registration) *</label>
                        <label><input type="radio" name="msme_registered" value="yes" class="vrf-msme-radio" required> Yes</label>
                        <label><input type="radio" name="msme_registered" value="no" class="vrf-msme-radio" required> No</label>
                    </div>

                    <div class="vrf-msme-yes-fields" style="display:none;">
                        <div class="vrf-row">
                            <label>MSME Type *</label>
                            <select name="msme_type" class="vrf-msme-conditional">
                                <option value="">Please select</option>
                                <option value="Micro">Micro</option>
                                <option value="Small">Small</option>
                                <option value="Medium">Medium</option>
                            </select>
                        </div>

                        <div class="vrf-row">
                            <label>Udyam Registration Number *</label>
                            <input type="text" name="udyam_number" class="vrf-msme-conditional" />
                        </div>

                        <div class="vrf-row">
                            <label>Udyam Certificate *</label>
                            <input type="file" name="udyam_certificate" class="vrf-msme-conditional" />
                        </div>
                    </div>

                    <div class="vrf-msme-no-fields" style="display:none;">
                        <div class="vrf-row">
                            <label>MSME Declaration Form</label>
                            <p><a href="<?php echo plugin_dir_url( __FILE__ ) . 'assets/documents/MSME_Declaration_Form.txt'; ?>" id="vrf-msme-declaration-download" class="vrf-download-link" download="MSME_Declaration_Form.txt">📥 Download MSME Declaration Form</a></p>
                            <small>Please download, print in letterhead, sign, and seal the form.</small>
                        </div>

                        <div class="vrf-row">
                            <label>Signed Copy of Declaration *</label>
                            <input type="file" name="msme_declaration_signed" class="vrf-msme-no-conditional" />
                        </div>
                    </div>

                    <div class="vrf-actions">
                        <button type="button" class="vrf-back">Back</button>
                        <button type="button" class="vrf-next">Next</button>
                    </div>
                </div>

                <!-- Step 4: Bank Details -->
                <div class="vrf-panel" data-panel="4" style="display:none;">
                    <h3 class="vrf-section-title">Bank Details</h3>

                    <div class="vrf-row">
                        <label>Beneficiary Name *</label>
                        <input type="text" name="beneficiary_name" required />
                    </div>

                    <div class="vrf-row">
                        <label>Bank Name *</label>
                        <input type="text" name="bank_name" required />
                    </div>

                    <div class="vrf-row">
                        <label>Branch Name *</label>
                        <input type="text" name="branch_name" required />
                    </div>

                    <div class="vrf-row">
                        <label>Bank IFSC Code *</label>
                        <input type="text" name="ifsc" required />
                    </div>

                    <div class="vrf-row">
                        <label>Bank Account Number *</label>
                        <input type="text" name="bank_account" required />
                    </div>

                    <div class="vrf-row">
                        <label>Cancelled Cheque Leaf / Bank Details in Company Letterhead *</label>
                        <input type="file" name="bank_proof" required />
                    </div>

                    <div class="vrf-actions">
                        <button type="button" class="vrf-back">Back</button>
                        <button type="button" class="vrf-next">Next</button>
                    </div>
                </div>

                <!-- Step 5: TDS -->
                <div class="vrf-panel" data-panel="5" style="display:none;">
                    <h3 class="vrf-section-title">TDS Details</h3>

                    <div class="vrf-row">
                        <label>PAN *</label>
                        <input type="text" name="pan_number" required />
                    </div>

                    <div class="vrf-row">
                        <label>PAN Type *</label>
                        <select name="pan_type" required>
                            <option value="">Please select</option>
                            <option value="Individual">Individual</option>
                            <option value="Company">Company</option>
                            <option value="Partnership">Partnership</option>
                            <option value="HUF">HUF</option>
                            <option value="Trust">Trust</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="vrf-row">
                        <label>PAN Card *</label>
                        <input type="file" name="pan_card" required />
                    </div>

                    <div class="vrf-actions">
                        <button type="button" class="vrf-back">Back</button>
                        <button type="submit" class="vrf-submit">Submit</button>
                    </div>
                </div>

                <div id="vrf-message" style="display:none;"></div>

            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    // Customer Shortcode output (form)
    public function render_customer_shortcode( $atts ) {
        ob_start();
        ?>
        <div class="vrf-container">
            <h2 class="vrf-title">CUSTOMER REGISTRATION FORM</h2>

            <div class="vrf-steps">
                <button class="vrf-step active" data-step="1">Basic Info</button>
                <button class="vrf-step" data-step="2">GST</button>
                <button class="vrf-step" data-step="3">TDS</button>
            </div>

            <form id="customer-registration-form" class="vrf-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="action" value="vrf_submit" />
                <input type="hidden" name="vrf_nonce" value="<?php echo wp_create_nonce('vrf_nonce'); ?>" />
                <input type="hidden" name="form_type" value="customer" />

                <!-- Step 1: Basic Info -->
                <div class="vrf-panel" data-panel="1">
                    <h3 class="vrf-section-title">Basic Information</h3>

                    <div class="vrf-row">
                        <label>Organisation Name *</label>
                        <input type="text" name="organisation_name" placeholder="Company/Trade License/GST Registration Name" required />
                    </div>

                    <div class="vrf-row">
                        <label>Company Registration / Trade License Number *</label>
                        <input type="text" name="company_registration_number" required />
                    </div>

                    <div class="vrf-row">
                        <label>Company Registration / Trade License File Upload *</label>
                        <input type="file" name="company_registration_file" required />
                    </div>

                    <div class="vrf-row">
                        <label>Importer / Exporter Code</label>
                        <input type="text" name="iec_code" />
                    </div>

                    <div class="vrf-row">
                        <label>Street Address *</label>
                        <input type="text" name="street_address" required />
                    </div>

                    <div class="vrf-row">
                        <label>Street Address Line 2</label>
                        <input type="text" name="street_address_2" />
                    </div>

                    <div class="vrf-row">
                        <label>Country *</label>
                        <select name="country" id="crf-country" required>
                            <option value="">Select Country</option>
                            <?php
                            $countries = $this->get_countries();
                            foreach ( $countries as $country ) {
                                echo '<option value="' . esc_attr( $country['name'] ) . '">' . esc_html( $country['name'] ) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="vrf-row">
                        <label>State *</label>
                        <select name="state" id="crf-state" required>
                            <option value="">Select State</option>
                        </select>
                    </div>

                    <div class="vrf-row">
                        <label>City *</label>
                        <select name="city" id="crf-city" required>
                            <option value="">Select City</option>
                        </select>
                    </div>

                    <div class="vrf-row">
                        <label>Zip Code</label>
                        <input type="text" name="zip" />
                    </div>

                    <div class="vrf-row vrf-checks">
                        <label>Customer Type *</label>
                        <label><input type="checkbox" name="customer_type[]" value="Goods" class="vrf-customer-type"> Goods</label>
                        <label><input type="checkbox" name="customer_type[]" value="Services" class="vrf-customer-type"> Services</label>
                    </div>

                    <div class="vrf-row-inline">
                        <div>
                            <label>Purchase Contact Person Name *</label>
                            <input type="text" name="purchase_contact_name" required />
                        </div>
                        <div>
                            <label>Purchase Contact Person Phone No *</label>
                            <input type="text" name="purchase_contact_phone" required />
                        </div>
                        <div>
                            <label>Purchase Contact Person Email *</label>
                            <input type="email" name="purchase_contact_email" required />
                        </div>
                    </div>

                    <div class="vrf-row-inline">
                        <div>
                            <label>Accounts Contact Person Name *</label>
                            <input type="text" name="accounts_contact_name" required />
                        </div>
                        <div>
                            <label>Accounts Contact Person Phone No *</label>
                            <input type="text" name="accounts_contact_phone" required />
                        </div>
                        <div>
                            <label>Accounts Contact Person Email *</label>
                            <input type="email" name="accounts_contact_email" required />
                        </div>
                    </div>

                    <div class="vrf-actions">
                        <button type="button" class="vrf-next">Next</button>
                    </div>
                </div>

                <!-- Step 2: GST -->
                <div class="vrf-panel" data-panel="2" style="display:none;">
                    <h3 class="vrf-section-title">GST Registration</h3>

                    <div class="vrf-row">
                        <label>GST Registration *</label>
                        <label><input type="radio" name="gst_registered" value="yes" class="vrf-gst-radio" required> Yes</label>
                        <label><input type="radio" name="gst_registered" value="no" class="vrf-gst-radio" required> No</label>
                    </div>

                    <div class="vrf-gst-fields" style="display:none;">
                        <div class="vrf-row">
                            <label>GST Registration Number *</label>
                            <input type="text" name="gst_number" class="vrf-gst-conditional" />
                        </div>

                        <div class="vrf-row">
                            <label>Legal Name (as per GST)</label>
                            <input type="text" name="gst_legal_name" class="vrf-gst-conditional" />
                        </div>

                        <div class="vrf-row">
                            <label>Tax Payer Type</label>
                            <select name="taxpayer_type" class="vrf-gst-conditional">
                                <option value="">Please select</option>
                                <option value="Regular">Regular</option>
                                <option value="Composition">Composition</option>
                            </select>
                        </div>

                        <div class="vrf-row">
                            <label>GST Certificate *</label>
                            <input type="file" name="gst_certificate" class="vrf-gst-conditional" />
                        </div>
                    </div>

                    <div class="vrf-actions">
                        <button type="button" class="vrf-back">Back</button>
                        <button type="button" class="vrf-next">Next</button>
                    </div>
                </div>

                <!-- Step 3: TDS -->
                <div class="vrf-panel" data-panel="3" style="display:none;">
                    <h3 class="vrf-section-title">TDS Details</h3>

                    <div class="vrf-row">
                        <label>PAN *</label>
                        <input type="text" name="pan_number" required />
                    </div>

                    <div class="vrf-row">
                        <label>PAN Type *</label>
                        <select name="pan_type" required>
                            <option value="">Please select</option>
                            <option value="Individual">Individual</option>
                            <option value="Company">Company</option>
                            <option value="Partnership">Partnership</option>
                            <option value="HUF">HUF</option>
                            <option value="Trust">Trust</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="vrf-row">
                        <label>PAN Card *</label>
                        <input type="file" name="pan_card" required />
                    </div>

                    <div class="vrf-row">
                        <label>TAN Number *</label>
                        <input type="text" name="tan_number" required />
                    </div>

                    <div class="vrf-actions">
                        <button type="button" class="vrf-back">Back</button>
                        <button type="submit" class="vrf-submit">Submit</button>
                    </div>
                </div>

                <div id="vrf-message" style="display:none;"></div>

            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    // Handle AJAX submission: validate, upload files, create CPT with meta
    public function handle_ajax_submit() {
        check_ajax_referer( 'vrf_nonce', 'vrf_nonce' );

        // Collect fields: we accept any posted fields; sanitize
        $allowed = array(
            'form_type','organisation_name','company_registration_number','iec_code','street_address','street_address_2','country','state','city','zip',
            'products','purchase_contact_name','purchase_contact_phone','purchase_contact_email','accounts_contact_name','accounts_contact_phone','accounts_contact_email',
            'gst_registered','gst_number','gst_legal_name','taxpayer_type','einvoice_applicability','return_filing_frequency',
            'msme_registered','msme_type','udyam_number',
            'beneficiary_name','bank_name','branch_name','bank_account','ifsc',
            'pan_number','pan_type','tan_number'
        );

        $data = array();
        foreach ( $allowed as $k ) {
            if ( isset( $_POST[ $k ] ) ) {
                $data[ $k ] = sanitize_text_field( wp_unslash( $_POST[ $k ] ) );
            }
        }

        // vendor_type is array
        if ( isset( $_POST['vendor_type'] ) && is_array( $_POST['vendor_type'] ) ) {
            $data['vendor_type'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['vendor_type'] ) );
        } else {
            $data['vendor_type'] = array();
        }

        // customer_type is array
        if ( isset( $_POST['customer_type'] ) && is_array( $_POST['customer_type'] ) ) {
            $data['customer_type'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['customer_type'] ) );
        } else {
            $data['customer_type'] = array();
        }

        // files: handle uploads using WP handle_upload and attach to media library
        $file_fields = array(
            'company_registration_file',
            'gst_certificate',
            'udyam_certificate',
            'msme_declaration_signed',
            'bank_proof',
            'pan_card'
        );

        $uploaded = array();

        foreach ( $file_fields as $field ) {
            if ( ! empty( $_FILES[ $field ] ) && ! empty( $_FILES[ $field ]['name'] ) ) {
                $file = $_FILES[ $field ];
                
                // Validate file size (2MB max)
                $max_size = 2 * 1024 * 1024; // 2MB in bytes
                if ( $file['size'] > $max_size ) {
                    wp_send_json_error( array( 'message' => 'File ' . $field . ' exceeds 2MB limit.' ) );
                }
                
                // Validate file type (jpg, jpeg, pdf only)
                $allowed_types = array('image/jpeg', 'image/jpg', 'application/pdf');
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ( $finfo === false ) {
                    wp_send_json_error( array( 'message' => 'Failed to validate file type for ' . $field ) );
                }
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if ( ! in_array( $mime, $allowed_types ) ) {
                    wp_send_json_error( array( 'message' => 'File ' . $field . ' must be jpg, jpeg, or pdf format.' ) );
                }
                
                // require WordPress file functions
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                require_once( ABSPATH . 'wp-admin/includes/media.php' );

                $overrides = array( 
                    'test_form' => false,
                    'mimes' => array(
                        'jpg|jpeg|jpe' => 'image/jpeg',
                        'pdf' => 'application/pdf',
                    )
                );
                $movefile = wp_handle_upload( $file, $overrides );
                if ( $movefile && empty( $movefile['error'] ) ) {
                    // Insert into media library
                    $filetype = wp_check_filetype( $movefile['file'], null );
                    $attachment = array(
                        'post_mime_type' => $filetype['type'],
                        'post_title'     => sanitize_file_name( $movefile['file'] ),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );
                    $attach_id = wp_insert_attachment( $attachment, $movefile['file'] );
                    if ( ! is_wp_error( $attach_id ) ) {
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
                        wp_update_attachment_metadata( $attach_id, $attach_data );
                        $uploaded[ $field ] = $attach_id;
                    }
                } else {
                    wp_send_json_error( array( 'message' => 'Failed to upload ' . $field . ': ' . ( $movefile['error'] ?? 'Unknown error' ) ) );
                }
            }
        }

        // prepare title
        $title = ! empty( $data['organisation_name'] ) ? $data['organisation_name'] : 'Vendor - ' . date( 'Y-m-d H:i:s' );

        // Create custom post (registrations)
        $post_id = wp_insert_post( array(
            'post_title'   => $title,
            'post_type'    => 'registrations',
            'post_status'  => 'publish',
        ) );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            wp_send_json_error( array( 'message' => 'Failed to create registration post.' ) );
        }

        // Save all data as meta
        foreach ( $data as $k => $v ) {
            update_post_meta( $post_id, $k, $v );
        }

        // Save uploaded attachment IDs in meta
        foreach ( $uploaded as $k => $attach_id ) {
            update_post_meta( $post_id, $k, intval( $attach_id ) );
        }

        // Additionally save full raw POST for reference
        update_post_meta( $post_id, '_vrf_raw_post', wp_json_encode( $data ) );

        // Send emails to user and admin
        $this->send_registration_emails( $post_id, $data, $uploaded );

        wp_send_json_success( array( 'message' => 'Registration submitted successfully.' ) );
    }

    // Send email notifications to user and admin
    private function send_registration_emails( $post_id, $data, $uploaded ) {
        $form_type = isset( $data['form_type'] ) ? ucfirst( $data['form_type'] ) : 'Vendor';
        $org_name = isset( $data['organisation_name'] ) ? $data['organisation_name'] : $form_type;
        $user_email = isset( $data['purchase_contact_email'] ) ? $data['purchase_contact_email'] : '';
        
        // Get admin email
        $admin_email = get_option( 'admin_email' );
        
        // Send acknowledgement email to user
        if ( ! empty( $user_email ) && is_email( $user_email ) ) {
            $user_subject = 'Thank You for Your ' . $form_type . ' Registration - ' . $org_name;
            $user_message = $this->get_user_email_template( $data );
            
            $headers = array( 'Content-Type: text/html; charset=UTF-8' );
            $user_email_sent = wp_mail( $user_email, $user_subject, $user_message, $headers );
            
            if ( ! $user_email_sent && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'VRF: Failed to send user acknowledgement email to ' . $user_email );
            }
        }
        
        // Send detailed email to admin with data
        if ( ! empty( $admin_email ) ) {
            $admin_subject = 'New ' . $form_type . ' Registration: ' . $org_name;
            $admin_message = $this->get_admin_email_template( $post_id, $data, $uploaded );
            
            $headers = array( 'Content-Type: text/html; charset=UTF-8' );
            
            // Attach files to admin email
            $attachments = array();
            foreach ( $uploaded as $field => $attach_id ) {
                $file_path = get_attached_file( $attach_id );
                if ( $file_path && file_exists( $file_path ) ) {
                    $attachments[] = $file_path;
                }
            }
            
            $admin_email_sent = wp_mail( $admin_email, $admin_subject, $admin_message, $headers, $attachments );
            
            if ( ! $admin_email_sent && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'VRF: Failed to send admin notification email to ' . $admin_email );
            }
        }
    }

    // User email template (acknowledgement)
    private function get_user_email_template( $data ) {
        $form_type = isset( $data['form_type'] ) ? ucfirst( $data['form_type'] ) : 'Vendor';
        $org_name = isset( $data['organisation_name'] ) ? esc_html( $data['organisation_name'] ) : $form_type;
        $contact_name = isset( $data['purchase_contact_name'] ) ? esc_html( $data['purchase_contact_name'] ) : '';
        
        $message = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #163a6b; color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 30px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .highlight { color: #ef2927; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . esc_html( $form_type ) . ' Registration Confirmation</h1>
        </div>
        <div class="content">
            <p>Dear ' . $contact_name . ',</p>
            
            <p>Thank you for registering with <span class="highlight">POEL</span>.</p>
            
            <p>We have successfully received your ' . strtolower( $form_type ) . ' registration for <strong>' . $org_name . '</strong>.</p>
            
            <p>Our team will review your application and contact you shortly. If you have any questions in the meantime, please feel free to reach out to us.</p>
            
            <p><strong>What happens next?</strong></p>
            <ul>
                <li>Our team will review your submitted information and documents</li>
                <li>We will verify the details provided</li>
                <li>You will be contacted within 3-5 business days</li>
            </ul>
            
            <p>Thank you for your interest in partnering with us.</p>
            
            <p>Best regards,<br>
            <strong>POEL Team</strong></p>
        </div>
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; ' . date('Y') . ' POEL. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';
        
        return $message;
    }

    // Admin email template (with all data)
    private function get_admin_email_template( $post_id, $data, $uploaded ) {
        $form_type = isset( $data['form_type'] ) ? ucfirst( $data['form_type'] ) : 'Vendor';
        $org_name = isset( $data['organisation_name'] ) ? esc_html( $data['organisation_name'] ) : 'N/A';
        
        $message = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { background: #163a6b; color: white; padding: 20px; text-align: center; }
        .content { background: #fff; padding: 20px; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #ef2927; }
        .section-title { color: #163a6b; font-weight: bold; margin-bottom: 10px; font-size: 18px; }
        .field { margin: 8px 0; }
        .field-label { font-weight: bold; color: #666; display: inline-block; min-width: 200px; }
        .field-value { color: #333; }
        .files { margin-top: 10px; }
        .file-link { display: block; padding: 5px 0; color: #17a2b8; text-decoration: none; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New ' . esc_html( $form_type ) . ' Registration</h1>
            <p>' . $org_name . '</p>
        </div>
        <div class="content">
            <p><strong>Submission Date:</strong> ' . date( 'F j, Y, g:i a' ) . '</p>
            <p><strong>Registration ID:</strong> #' . $post_id . '</p>
            
            <div class="section">
                <div class="section-title">Basic Information</div>
                ' . $this->format_field( 'Organisation Name', $data, 'organisation_name' ) . '
                ' . $this->format_field( 'Company Registration Number', $data, 'company_registration_number' ) . '
                ' . $this->format_field( 'IEC Code', $data, 'iec_code' ) . '
                ' . $this->format_field( 'Street Address', $data, 'street_address' ) . '
                ' . $this->format_field( 'Street Address Line 2', $data, 'street_address_2' ) . '
                ' . $this->format_field( 'Country', $data, 'country' ) . '
                ' . $this->format_field( 'State', $data, 'state' ) . '
                ' . $this->format_field( 'City', $data, 'city' ) . '
                ' . $this->format_field( 'Zip Code', $data, 'zip' ) . '
                ' . $this->format_field( 'Vendor Type', $data, 'vendor_type', true ) . '
                ' . $this->format_field( 'Customer Type', $data, 'customer_type', true ) . '
                ' . $this->format_field( 'Products/Services Offered', $data, 'products' ) . '
            </div>
            
            <div class="section">
                <div class="section-title">Contact Information</div>
                ' . $this->format_field( 'Purchase Contact Name', $data, 'purchase_contact_name' ) . '
                ' . $this->format_field( 'Purchase Contact Phone', $data, 'purchase_contact_phone' ) . '
                ' . $this->format_field( 'Purchase Contact Email', $data, 'purchase_contact_email' ) . '
                ' . $this->format_field( 'Accounts Contact Name', $data, 'accounts_contact_name' ) . '
                ' . $this->format_field( 'Accounts Contact Phone', $data, 'accounts_contact_phone' ) . '
                ' . $this->format_field( 'Accounts Contact Email', $data, 'accounts_contact_email' ) . '
            </div>
            
            <div class="section">
                <div class="section-title">GST Information</div>
                ' . $this->format_field( 'GST Registered', $data, 'gst_registered' ) . '
                ' . $this->format_field( 'GST Number', $data, 'gst_number' ) . '
                ' . $this->format_field( 'Legal Name (as per GST)', $data, 'gst_legal_name' ) . '
                ' . $this->format_field( 'Tax Payer Type', $data, 'taxpayer_type' ) . '
                ' . $this->format_field( 'E-Invoice Applicability', $data, 'einvoice_applicability' ) . '
                ' . $this->format_field( 'Return Filing Frequency', $data, 'return_filing_frequency' ) . '
            </div>
            
            <div class="section">
                <div class="section-title">MSME Information</div>
                ' . $this->format_field( 'MSME Registered', $data, 'msme_registered' ) . '
                ' . $this->format_field( 'MSME Type', $data, 'msme_type' ) . '
                ' . $this->format_field( 'Udyam Registration Number', $data, 'udyam_number' ) . '
            </div>
            
            <div class="section">
                <div class="section-title">Bank Details</div>
                ' . $this->format_field( 'Beneficiary Name', $data, 'beneficiary_name' ) . '
                ' . $this->format_field( 'Bank Name', $data, 'bank_name' ) . '
                ' . $this->format_field( 'Branch Name', $data, 'branch_name' ) . '
                ' . $this->format_field( 'Bank Account Number', $data, 'bank_account' ) . '
                ' . $this->format_field( 'IFSC Code', $data, 'ifsc' ) . '
            </div>
            
            <div class="section">
                <div class="section-title">TDS Information</div>
                ' . $this->format_field( 'PAN Number', $data, 'pan_number' ) . '
                ' . $this->format_field( 'PAN Type', $data, 'pan_type' ) . '
                ' . $this->format_field( 'TAN Number', $data, 'tan_number' ) . '
            </div>
            
            <div class="section">
                <div class="section-title">Uploaded Files</div>
                <div class="files">
                    ' . $this->format_uploaded_files( $uploaded ) . '
                </div>
                <p style="margin-top: 15px; color: #666; font-size: 14px;">
                    <em>All files are attached to this email for your convenience.</em>
                </p>
            </div>
            
            <p style="margin-top: 30px;">
                <a href="' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . '" 
                   style="display: inline-block; padding: 12px 24px; background: #ef2927; color: white; 
                          text-decoration: none; border-radius: 4px;">
                    View in Admin Dashboard
                </a>
            </p>
        </div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' POEL Vendor Registration System</p>
        </div>
    </div>
</body>
</html>';
        
        return $message;
    }

    // Helper to format individual fields for email
    private function format_field( $label, $data, $key, $is_array = false ) {
        if ( ! isset( $data[ $key ] ) || empty( $data[ $key ] ) ) {
            return '';
        }
        
        $value = $data[ $key ];
        if ( $is_array && is_array( $value ) ) {
            $value = implode( ', ', $value );
        }
        
        return '<div class="field">
            <span class="field-label">' . esc_html( $label ) . ':</span>
            <span class="field-value">' . esc_html( $value ) . '</span>
        </div>';
    }

    // Helper to format uploaded files list for email
    private function format_uploaded_files( $uploaded ) {
        if ( empty( $uploaded ) ) {
            return '<p>No files uploaded.</p>';
        }
        
        $file_labels = array(
            'company_registration_file' => 'Company Registration File',
            'gst_certificate' => 'GST Certificate',
            'udyam_certificate' => 'Udyam Certificate',
            'msme_declaration_signed' => 'MSME Declaration (Signed)',
            'bank_proof' => 'Bank Proof/Cancelled Cheque',
            'pan_card' => 'PAN Card',
        );
        
        $output = '';
        foreach ( $uploaded as $field => $attach_id ) {
            $url = wp_get_attachment_url( $attach_id );
            $label = isset( $file_labels[ $field ] ) ? $file_labels[ $field ] : ucwords( str_replace( '_', ' ', $field ) );
            
            if ( $url ) {
                $output .= '<a href="' . esc_url( $url ) . '" class="file-link" target="_blank">📎 ' . esc_html( $label ) . '</a>';
            }
        }
        
        return $output;
    }

    // Admin columns
    public function admin_columns( $columns ) {
        $new = array();
        $new['cb'] = $columns['cb'];
        $new['title'] = 'Title';
        $new['form_type'] = 'Form Type';
        $new['organisation_name'] = 'Organisation';
        $new['contact_info'] = 'Contact Info';
        $new['submitted_date'] = 'Submitted';
        $new['files'] = 'Uploaded Files';
        $new['date'] = $columns['date'];
        return $new;
    }

    public function admin_columns_content( $column, $post_id ) {
        if ( $column == 'form_type' ) {
            $form_type = get_post_meta( $post_id, 'form_type', true );
            echo esc_html( ucfirst( $form_type ?: 'Unknown' ) );
        }

        if ( $column == 'organisation_name' ) {
            $org = get_post_meta( $post_id, 'organisation_name', true );
            echo esc_html( $org ?: '—' );
        }

        if ( $column == 'contact_info' ) {
            $email = get_post_meta( $post_id, 'purchase_contact_email', true );
            $phone = get_post_meta( $post_id, 'purchase_contact_phone', true );
            echo esc_html( $email ?: '—' ) . '<br>' . esc_html( $phone ?: '—' );
        }

        if ( $column == 'submitted_date' ) {
            echo get_the_date( '', $post_id );
        }

        if ( $column == 'files' ) {
            $file_keys = array(
                'company_registration_file' => 'Company Registration',
                'gst_certificate' => 'GST Certificate',
                'udyam_certificate' => 'Udyam Certificate',
                'msme_declaration_signed' => 'MSME Declaration',
                'bank_proof' => 'Bank Proof',
                'pan_card' => 'PAN Card',
            );

            $out = array();
            foreach ( $file_keys as $meta_key => $label ) {
                $att_id = get_post_meta( $post_id, $meta_key, true );
                if ( $att_id ) {
                    $url = wp_get_attachment_url( $att_id );
                    if ( $url ) {
                        $out[] = '<a href="' . esc_url( $url ) . '" target="_blank" download>' . esc_html( $label ) . '</a>';
                    }
                }
            }
            if ( empty( $out ) ) {
                echo '—';
            } else {
                echo implode( '<br>', $out );
            }
        }
    }

    // Admin filter dropdown
    public function admin_filter_dropdown( $post_type ) {
        if ( 'registrations' === $post_type ) {
            $selected = isset( $_GET['form_type_filter'] ) ? $_GET['form_type_filter'] : '';
            ?>
            <select name="form_type_filter">
                <option value="">All Form Types</option>
                <option value="vendor" <?php selected( $selected, 'vendor' ); ?>>Vendor</option>
                <option value="customer" <?php selected( $selected, 'customer' ); ?>>Customer</option>
            </select>
            <?php
        }
    }

    // Admin filter query
    public function admin_filter_query( $query ) {
        global $pagenow;
        if ( is_admin() && $pagenow === 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'registrations' && isset( $_GET['form_type_filter'] ) && $_GET['form_type_filter'] !== '' ) {
            $query->query_vars['meta_key'] = 'form_type';
            $query->query_vars['meta_value'] = sanitize_text_field( $_GET['form_type_filter'] );
        }
    }

    // AJAX handler for getting states based on country
    public function ajax_get_states() {
        check_ajax_referer( 'vrf_nonce', 'nonce' );
        global $wpdb;
        
        $country = isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : '';
        
        if ( empty( $country ) ) {
            wp_send_json_success( array( 'states' => array() ) );
            return;
        }
        
        $countries_table = $wpdb->prefix . 'vrf_countries';
        $states_table = $wpdb->prefix . 'vrf_states';
        
        // Get country ID
        $country_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $countries_table WHERE name = %s",
            $country
        ) );
        
        if ( ! $country_id ) {
            wp_send_json_success( array( 'states' => array() ) );
            return;
        }
        
        // Get states for this country
        $states = $wpdb->get_results( $wpdb->prepare(
            "SELECT name FROM $states_table WHERE country_id = %d ORDER BY name ASC",
            $country_id
        ), ARRAY_A );
        
        $state_names = array();
        foreach ( $states as $state ) {
            $state_names[] = $state['name'];
        }
        
        wp_send_json_success( array( 'states' => $state_names ) );
    }

    // AJAX handler for getting cities based on state
    public function ajax_get_cities() {
        check_ajax_referer( 'vrf_nonce', 'nonce' );
        global $wpdb;
        
        $state = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
        
        if ( empty( $state ) ) {
            wp_send_json_success( array( 'cities' => array() ) );
            return;
        }
        
        $states_table = $wpdb->prefix . 'vrf_states';
        $cities_table = $wpdb->prefix . 'vrf_cities';
        
        // Get state ID
        $state_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $states_table WHERE name = %s",
            $state
        ) );
        
        if ( ! $state_id ) {
            wp_send_json_success( array( 'cities' => array() ) );
            return;
        }
        
        // Get cities for this state
        $cities = $wpdb->get_results( $wpdb->prepare(
            "SELECT name FROM $cities_table WHERE state_id = %d ORDER BY name ASC",
            $state_id
        ), ARRAY_A );
        
        $city_names = array();
        foreach ( $cities as $city ) {
            $city_names[] = $city['name'];
        }
        
        wp_send_json_success( array( 'cities' => $city_names ) );
    }

    // Add meta boxes to registration post edit screen
    public function add_registration_meta_boxes() {
        add_meta_box(
            'vrf_basic_info',
            'Basic Information',
            [ $this, 'render_basic_info_meta_box' ],
            'registrations',
            'normal',
            'high'
        );
        
        add_meta_box(
            'vrf_contact_info',
            'Contact Information',
            [ $this, 'render_contact_info_meta_box' ],
            'registrations',
            'normal',
            'high'
        );
        
        add_meta_box(
            'vrf_gst_info',
            'GST Information',
            [ $this, 'render_gst_info_meta_box' ],
            'registrations',
            'normal',
            'high'
        );
        
        add_meta_box(
            'vrf_msme_info',
            'MSME Information',
            [ $this, 'render_msme_info_meta_box' ],
            'registrations',
            'normal',
            'high'
        );
        
        add_meta_box(
            'vrf_bank_info',
            'Bank Details',
            [ $this, 'render_bank_info_meta_box' ],
            'registrations',
            'normal',
            'high'
        );
        
        add_meta_box(
            'vrf_tds_info',
            'TDS Information',
            [ $this, 'render_tds_info_meta_box' ],
            'registrations',
            'normal',
            'high'
        );
        
        add_meta_box(
            'vrf_files',
            'Uploaded Files',
            [ $this, 'render_files_meta_box' ],
            'registrations',
            'side',
            'default'
        );
    }

    // Render Basic Information meta box
    public function render_basic_info_meta_box( $post ) {
        $this->render_meta_fields( $post->ID, array(
            'form_type' => 'Form Type',
            'organisation_name' => 'Organisation Name',
            'company_registration_number' => 'Company Registration Number',
            'iec_code' => 'IEC Code',
            'street_address' => 'Street Address',
            'street_address_2' => 'Street Address Line 2',
            'country' => 'Country',
            'state' => 'State',
            'city' => 'City',
            'zip' => 'Zip Code',
            'vendor_type' => 'Vendor Type',
            'customer_type' => 'Customer Type',
            'products' => 'Products/Services Offered'
        ) );
    }

    // Render Contact Information meta box
    public function render_contact_info_meta_box( $post ) {
        $this->render_meta_fields( $post->ID, array(
            'purchase_contact_name' => 'Purchase Contact Name',
            'purchase_contact_phone' => 'Purchase Contact Phone',
            'purchase_contact_email' => 'Purchase Contact Email',
            'accounts_contact_name' => 'Accounts Contact Name',
            'accounts_contact_phone' => 'Accounts Contact Phone',
            'accounts_contact_email' => 'Accounts Contact Email'
        ) );
    }

    // Render GST Information meta box
    public function render_gst_info_meta_box( $post ) {
        $this->render_meta_fields( $post->ID, array(
            'gst_registered' => 'GST Registered',
            'gst_number' => 'GST Number',
            'gst_legal_name' => 'Legal Name (as per GST)',
            'taxpayer_type' => 'Tax Payer Type',
            'einvoice_applicability' => 'E-Invoice Applicability',
            'return_filing_frequency' => 'Return Filing Frequency'
        ) );
    }

    // Render MSME Information meta box
    public function render_msme_info_meta_box( $post ) {
        $this->render_meta_fields( $post->ID, array(
            'msme_registered' => 'MSME Registered',
            'msme_type' => 'MSME Type',
            'udyam_number' => 'Udyam Registration Number'
        ) );
    }

    // Render Bank Details meta box
    public function render_bank_info_meta_box( $post ) {
        $this->render_meta_fields( $post->ID, array(
            'beneficiary_name' => 'Beneficiary Name',
            'bank_name' => 'Bank Name',
            'branch_name' => 'Branch Name',
            'ifsc' => 'IFSC Code',
            'bank_account' => 'Bank Account Number'
        ) );
    }

    // Render TDS Information meta box
    public function render_tds_info_meta_box( $post ) {
        $this->render_meta_fields( $post->ID, array(
            'pan_number' => 'PAN Number',
            'pan_type' => 'PAN Type',
            'tan_number' => 'TAN Number'
        ) );
    }

    // Render Uploaded Files meta box
    public function render_files_meta_box( $post ) {
        $file_fields = array(
            'company_registration_file' => 'Company Registration File',
            'gst_certificate' => 'GST Certificate',
            'udyam_certificate' => 'Udyam Certificate',
            'msme_declaration_signed' => 'MSME Declaration (Signed)',
            'bank_proof' => 'Bank Proof/Cancelled Cheque',
            'pan_card' => 'PAN Card'
        );
        
        // Add minimal inline styles only where WordPress doesn't provide alternatives
        echo '<style>
            .vrf-file-item { margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-left: 3px solid #2271b1; }
            .vrf-file-label { display: block; margin-bottom: 5px; font-weight: 600; }
            .vrf-file-link { color: #2271b1; text-decoration: none; }
        </style>';
        
        echo '<div class="inside">';
        foreach ( $file_fields as $key => $label ) {
            $attach_id = get_post_meta( $post->ID, $key, true );
            if ( $attach_id ) {
                $url = wp_get_attachment_url( $attach_id );
                $filename = basename( get_attached_file( $attach_id ) );
                if ( $url ) {
                    echo '<div class="vrf-file-item">';
                    echo '<span class="vrf-file-label">' . esc_html( $label ) . '</span>';
                    echo '<a href="' . esc_url( $url ) . '" class="vrf-file-link" target="_blank" download>';
                    echo '📎 ' . esc_html( $filename ) . '</a>';
                    echo '</div>';
                }
            }
        }
        echo '</div>';
    }

    // Helper function to render meta fields
    private function render_meta_fields( $post_id, $fields ) {
        echo '<table class="form-table" role="presentation">';
        echo '<tbody>';
        foreach ( $fields as $key => $label ) {
            $value = get_post_meta( $post_id, $key, true );
            if ( ! empty( $value ) ) {
                echo '<tr>';
                echo '<th scope="row">' . esc_html( $label ) . ':</th>';
                echo '<td>';
                
                // Handle array values (like vendor_type)
                if ( is_array( $value ) ) {
                    echo esc_html( implode( ', ', $value ) );
                } else {
                    // Handle long text with line breaks
                    echo nl2br( esc_html( $value ) );
                }
                
                echo '</td>';
                echo '</tr>';
            }
        }
        echo '</tbody>';
        echo '</table>';
    }

    // Get countries from database
    private function get_countries() {
        global $wpdb;
        $countries_table = $wpdb->prefix . 'vrf_countries';
        
        $countries = $wpdb->get_results(
            "SELECT id, name FROM $countries_table ORDER BY name ASC",
            ARRAY_A
        );
        
        return $countries;
    }

    // Database setup for location tables
    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create countries table
        $countries_table = $wpdb->prefix . 'vrf_countries';
        $sql_countries = "CREATE TABLE IF NOT EXISTS $countries_table (
            id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            iso3 char(3) DEFAULT NULL,
            numeric_code char(3) DEFAULT NULL,
            iso2 char(2) DEFAULT NULL,
            phonecode varchar(255) DEFAULT NULL,
            capital varchar(255) DEFAULT NULL,
            currency varchar(255) DEFAULT NULL,
            currency_name varchar(255) DEFAULT NULL,
            currency_symbol varchar(255) DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Create states table
        $states_table = $wpdb->prefix . 'vrf_states';
        $sql_states = "CREATE TABLE IF NOT EXISTS $states_table (
            id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            country_id mediumint(8) unsigned NOT NULL,
            country_code char(2) NOT NULL,
            state_code varchar(255) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY country_id (country_id)
        ) $charset_collate;";
        
        // Create cities table
        $cities_table = $wpdb->prefix . 'vrf_cities';
        $sql_cities = "CREATE TABLE IF NOT EXISTS $cities_table (
            id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            state_id mediumint(8) unsigned NOT NULL,
            state_code varchar(255) NOT NULL,
            country_id mediumint(8) unsigned NOT NULL,
            country_code char(2) NOT NULL,
            PRIMARY KEY (id),
            KEY state_id (state_id),
            KEY country_id (country_id)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_countries );
        dbDelta( $sql_states );
        dbDelta( $sql_cities );
        
        // Check if data needs to be imported
        $countries_count = $wpdb->get_var( "SELECT COUNT(*) FROM $countries_table" );
        if ( $countries_count == 0 ) {
            self::import_location_data();
        }
    }
    
    // Import location data from SQL files
    private static function import_location_data() {
        global $wpdb;
        
        $plugin_dir = plugin_dir_path( __FILE__ );
        
        // Import countries
        $countries_file = $plugin_dir . 'assets/countries.sql';
        if ( file_exists( $countries_file ) ) {
            $sql = file_get_contents( $countries_file );
            
            // Replace table name in SQL
            $sql = str_replace( 'countries', $wpdb->prefix . 'vrf_countries', $sql );
            
            // Remove foreign key constraints and references to other tables
            $sql = preg_replace( '/CONSTRAINT.*?FOREIGN KEY.*?\n/s', '', $sql );
            $sql = preg_replace( '/,\s*KEY.*?region.*?\n/s', '', $sql );
            
            // Execute SQL in chunks to avoid memory issues
            $statements = explode( ';', $sql );
            foreach ( $statements as $statement ) {
                $statement = trim( $statement );
                if ( ! empty( $statement ) && strpos( $statement, 'INSERT INTO' ) !== false ) {
                    $wpdb->query( $statement );
                }
            }
        }
        
        // Import states
        $states_file = $plugin_dir . 'assets/states.sql';
        if ( file_exists( $states_file ) ) {
            $sql = file_get_contents( $states_file );
            
            // Replace table name in SQL
            $sql = str_replace( 'states', $wpdb->prefix . 'vrf_states', $sql );
            
            // Execute SQL in chunks
            $statements = explode( ';', $sql );
            foreach ( $statements as $statement ) {
                $statement = trim( $statement );
                if ( ! empty( $statement ) && strpos( $statement, 'INSERT INTO' ) !== false ) {
                    $wpdb->query( $statement );
                }
            }
        }
        
        // Import cities
        $cities_file = $plugin_dir . 'assets/cities.sql';
        if ( file_exists( $cities_file ) ) {
            $sql = file_get_contents( $cities_file );
            
            // Replace table name in SQL
            $sql = str_replace( 'cities', $wpdb->prefix . 'vrf_cities', $sql );
            
            // Execute SQL in chunks
            $statements = explode( ';', $sql );
            foreach ( $statements as $statement ) {
                $statement = trim( $statement );
                if ( ! empty( $statement ) && strpos( $statement, 'INSERT INTO' ) !== false ) {
                    $wpdb->query( $statement );
                }
            }
        }
    }

}

new VRF_Plugin();

// Register activation hook
register_activation_hook( __FILE__, array( 'VRF_Plugin', 'activate' ) );