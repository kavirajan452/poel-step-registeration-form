<?php
/*
Plugin Name: Vendor Registration Form
Description: Multi-step vendor registration form shortcode, stores submissions as custom post type 'registrations' with file attachments and displays downloads in admin.
Version: 1.0
Author: GitHub Copilot (adapted)
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class VRF_Plugin {

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_shortcode( 'vendor_registration_form', [ $this, 'render_shortcode' ] );
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
        if ( ! is_singular() && ! is_page() && ! has_shortcode( get_post_field( 'post_content', get_the_ID() ), 'vendor_registration_form' ) ) {
            // we will enqueue only when shortcode present; but WP doesn't give easy way here — enqueue unconditionally on frontend
        }
        wp_register_script( 'vendor-registration-js', plugin_dir_url( __FILE__ ) . 'assets/js/vendor-registration.js', array('jquery'), '1.0', true );
        wp_register_style( 'vendor-registration-css', plugin_dir_url( __FILE__ ) . 'assets/css/vendor-registration.css', array(), '1.0' );
        wp_enqueue_script( 'vendor-registration-js' );
        wp_enqueue_style( 'vendor-registration-css' );

        wp_localize_script( 'vendor-registration-js', 'vrf_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'vrf_nonce' ),
        ) );
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
                        <input type="text" name="organisation_name" required />
                    </div>

                    <div class="vrf-row">
                        <label>Company Registration / Trade License Number *</label>
                        <input type="text" name="company_registration_number" required />
                    </div>

                    <div class="vrf-row">
                        <label>Company Registration / Trade License file upload *</label>
                        <input type="file" name="company_registration_file" />
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
                            <option value="India">India</option>
                            <option value="USA">USA</option>
                            <option value="UK">UK</option>
                        </select>
                    </div>

                    <div class="vrf-row">
                        <label>State</label>
                        <select name="state" id="vrf-state">
                            <option value="">Select State</option>
                        </select>
                    </div>

                    <div class="vrf-row">
                        <label>City</label>
                        <select name="city" id="vrf-city">
                            <option value="">Select City</option>
                        </select>
                    </div>

                    <div class="vrf-row">
                        <label>Zip Code</label>
                        <input type="text" name="zip" />
                    </div>

                    <div class="vrf-row vrf-checks">
                        <label>Vendor Type</label>
                        <label><input type="checkbox" name="vendor_type[]" value="Goods Supplier"> Goods Supplier</label>
                        <label><input type="checkbox" name="vendor_type[]" value="Service Supplier"> Service Supplier</label>
                        <label><input type="checkbox" name="vendor_type[]" value="Transporter"> Transporter</label>
                        <label><input type="checkbox" name="vendor_type[]" value="Other"> Other</label>
                    </div>

                    <div class="vrf-row">
                        <label>Product / Services offered to POEL *</label>
                        <textarea name="products" rows="5"></textarea>
                    </div>

                    <div class="vrf-row-inline">
                        <div>
                            <label>Purchase Contact Person Name *</label>
                            <input type="text" name="purchase_contact_name" />
                        </div>
                        <div>
                            <label>Purchase Contact Person Phone No *</label>
                            <input type="text" name="purchase_contact_phone" />
                        </div>
                        <div>
                            <label>Purchase Contact Person Email *</label>
                            <input type="email" name="purchase_contact_email" />
                        </div>
                    </div>

                    <div class="vrf-row-inline">
                        <div>
                            <label>Accounts Contact Person Name *</label>
                            <input type="text" name="accounts_contact_name" />
                        </div>
                        <div>
                            <label>Accounts Contact Person Phone No *</label>
                            <input type="text" name="accounts_contact_phone" />
                        </div>
                        <div>
                            <label>Accounts Contact Person Email *</label>
                            <input type="email" name="accounts_contact_email" />
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
                        <label><input type="radio" name="gst_registered" value="yes"> Yes</label>
                        <label><input type="radio" name="gst_registered" value="no" checked> No</label>
                    </div>

                    <div class="vrf-row">
                        <label>GST Registration Number</label>
                        <input type="text" name="gst_number" />
                    </div>

                    <div class="vrf-row">
                        <label>Legal Name (as per GST)</label>
                        <input type="text" name="gst_legal_name" />
                    </div>

                    <div class="vrf-row">
                        <label>GST Certificate</label>
                        <input type="file" name="gst_certificate" />
                    </div>

                    <div class="vrf-row">
                        <label>Tax Payer Type</label>
                        <select name="taxpayer_type">
                            <option value="">Please select</option>
                            <option value="Regular">Regular</option>
                            <option value="Composition">Composition</option>
                        </select>
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
                        <label><input type="radio" name="msme_registered" value="yes"> Yes</label>
                        <label><input type="radio" name="msme_registered" value="no" checked> No</label>
                    </div>

                    <div class="vrf-row">
                        <label>MSME Type</label>
                        <select name="msme_type">
                            <option value="">Please select</option>
                            <option value="Micro">Micro</option>
                            <option value="Small">Small</option>
                            <option value="Medium">Medium</option>
                        </select>
                    </div>

                    <div class="vrf-row">
                        <label>Udyam Registration Number</label>
                        <input type="text" name="udyam_number" />
                    </div>

                    <div class="vrf-row">
                        <label>Udyam Certificate</label>
                        <input type="file" name="udyam_certificate" />
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
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" />
                    </div>

                    <div class="vrf-row">
                        <label>Account Number</label>
                        <input type="text" name="bank_account" />
                    </div>

                    <div class="vrf-row">
                        <label>IFSC</label>
                        <input type="text" name="ifsc" />
                    </div>

                    <div class="vrf-row">
                        <label>Cancelled Cheque / Bank Proof</label>
                        <input type="file" name="bank_proof" />
                    </div>

                    <div class="vrf-actions">
                        <button type="button" class="vrf-back">Back</button>
                        <button type="button" class="vrf-next">Next</button>
                    </div>
                </div>

                <!-- Step 5: TDS -->
                <div class="vrf-panel" data-panel="5" style="display:none;">
                    <h3 class="vrf-section-title">TDS</h3>

                    <div class="vrf-row">
                        <label>PAN Number</label>
                        <input type="text" name="pan_number" />
                    </div>

                    <div class="vrf-row">
                        <label>PAN Card</label>
                        <input type="file" name="pan_card" />
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
            'gst_registered','gst_number','gst_legal_name','taxpayer_type',
            'msme_registered','msme_type','udyam_number',
            'bank_name','bank_account','ifsc',
            'pan_number'
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

        // files: handle uploads using WP handle_upload and attach to media library
        $file_fields = array(
            'company_registration_file',
            'gst_certificate',
            'udyam_certificate',
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

        wp_send_json_success( array( 'message' => 'Registration submitted successfully.' ) );
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
        $country = isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : '';
        
        $states = array();
        if ( $country === 'India' ) {
            $states = array(
                'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
                'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
                'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
                'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
                'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
                'Uttar Pradesh', 'Uttarakhand', 'West Bengal'
            );
        } elseif ( $country === 'USA' ) {
            $states = array(
                'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California',
                'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia',
                'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
                'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland',
                'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri',
                'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey',
                'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio',
                'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina',
                'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
                'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
            );
        } elseif ( $country === 'UK' ) {
            $states = array(
                'England', 'Scotland', 'Wales', 'Northern Ireland'
            );
        }
        
        wp_send_json_success( array( 'states' => $states ) );
    }

    // AJAX handler for getting cities based on state
    public function ajax_get_cities() {
        check_ajax_referer( 'vrf_nonce', 'nonce' );
        $state = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
        
        $cities = array();
        // Sample cities for major Indian states
        if ( $state === 'Tamil Nadu' ) {
            $cities = array('Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tirunelveli');
        } elseif ( $state === 'Maharashtra' ) {
            $cities = array('Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Aurangabad');
        } elseif ( $state === 'Karnataka' ) {
            $cities = array('Bangalore', 'Mysore', 'Mangalore', 'Hubli', 'Belgaum', 'Gulbarga');
        } elseif ( $state === 'Delhi' ) {
            $cities = array('New Delhi', 'Central Delhi', 'North Delhi', 'South Delhi', 'East Delhi', 'West Delhi');
        } elseif ( $state === 'Gujarat' ) {
            $cities = array('Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar');
        } elseif ( $state === 'Rajasthan' ) {
            $cities = array('Jaipur', 'Jodhpur', 'Kota', 'Udaipur', 'Ajmer', 'Bikaner');
        } elseif ( $state === 'Uttar Pradesh' ) {
            $cities = array('Lucknow', 'Kanpur', 'Ghaziabad', 'Agra', 'Varanasi', 'Meerut');
        } elseif ( $state === 'West Bengal' ) {
            $cities = array('Kolkata', 'Howrah', 'Durgapur', 'Asansol', 'Siliguri');
        } elseif ( $state === 'California' ) {
            $cities = array('Los Angeles', 'San Francisco', 'San Diego', 'San Jose', 'Sacramento');
        } elseif ( $state === 'New York' ) {
            $cities = array('New York City', 'Buffalo', 'Rochester', 'Yonkers', 'Syracuse');
        } elseif ( $state === 'Texas' ) {
            $cities = array('Houston', 'Dallas', 'Austin', 'San Antonio', 'Fort Worth');
        } elseif ( $state === 'England' ) {
            $cities = array('London', 'Manchester', 'Birmingham', 'Liverpool', 'Leeds');
        }
        
        wp_send_json_success( array( 'cities' => $cities ) );
    }

}

new VRF_Plugin();