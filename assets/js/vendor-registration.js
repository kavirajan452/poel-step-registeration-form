(function($){
    $(document).ready(function(){
        var $form = $('#vendor-registration-form');
        var $panels = $('.vrf-panel');
        var current = 1;
        
        // Trigger country change on page load to populate states for India
        $('#vrf-country').trigger('change');

        // Toast notification function
        function showToast(message, type) {
            // Remove existing toast
            $('.vrf-toast').remove();
            
            var $toast = $('<div class="vrf-toast vrf-toast-' + type + '">' + message + '</div>');
            $('body').append($toast);
            
            setTimeout(function() {
                $toast.addClass('vrf-toast-show');
            }, 100);
            
            setTimeout(function() {
                $toast.removeClass('vrf-toast-show');
                setTimeout(function() {
                    $toast.remove();
                }, 300);
            }, 3000);
        }

        function showPanel(n){
            $panels.hide();
            $('.vrf-panel[data-panel="'+n+'"]').show();
            $('.vrf-step').removeClass('active');
            $('.vrf-step[data-step="'+n+'"]').addClass('active');
            current = n;
            $('html,body').animate({scrollTop: $('.vrf-title').offset().top - 20}, 300);
        }

        // Real-time validation functions
        function validateEmail(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function validatePhone(phone) {
            var re = /^[0-9]{10}$/;
            return re.test(phone.replace(/[\s\-\(\)]/g, ''));
        }

        function validatePAN(pan) {
            var re = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
            return re.test(pan);
        }

        function validateGST(gst) {
            var re = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
            return re.test(gst);
        }

        function validateIFSC(ifsc) {
            var re = /^[A-Z]{4}0[A-Z0-9]{6}$/;
            return re.test(ifsc);
        }
        
        function validateUdyam(udyam) {
            // Udyam format: UDYAM-XX-00-0000000 (19 characters)
            // Note: This validates the format structure but not actual state codes
            var re = /^UDYAM-[A-Z]{2}-\d{2}-\d{7}$/;
            return re.test(udyam);
        }
        
        function validateAccountNumber(account) {
            // Account number should be 9-18 digits
            var re = /^[0-9]{9,18}$/;
            return re.test(account);
        }

        function validateFile(input) {
            if (input.files && input.files[0]) {
                var file = input.files[0];
                var maxSize = 2 * 1024 * 1024; // 2MB
                var allowedTypes = ['image/jpeg', 'image/jpg', 'application/pdf'];
                
                if (file.size > maxSize) {
                    return 'File size must not exceed 2MB';
                }
                
                if (allowedTypes.indexOf(file.type) === -1) {
                    return 'File must be jpg, jpeg, or pdf format';
                }
            }
            return true;
        }

        // Real-time validation on input
        $form.on('input change', 'input, select, textarea', function() {
            var $this = $(this);
            var val = $this.val();
            var name = $this.attr('name');
            var type = $this.attr('type');
            
            // Remove previous error
            $this.removeClass('vrf-invalid');
            $this.next('.vrf-error').remove();
            
            // Required field validation
            if ($this.prop('required') && !val) {
                $this.addClass('vrf-invalid');
                return;
            }
            
            // Email validation
            if (type === 'email' && val && !validateEmail(val)) {
                $this.addClass('vrf-invalid');
                $this.after('<span class="vrf-error">Invalid email format</span>');
                return;
            }
            
            // Phone validation
            if (name && name.indexOf('phone') !== -1 && val && !validatePhone(val)) {
                $this.addClass('vrf-invalid');
                $this.after('<span class="vrf-error">Invalid phone number (10 digits required)</span>');
                return;
            }
            
            // PAN validation
            if (name === 'pan_number' && val && !validatePAN(val)) {
                $this.addClass('vrf-invalid');
                $this.after('<span class="vrf-error">Invalid PAN format (e.g., ABCDE1234F)</span>');
                return;
            }
            
            // GST validation
            if (name === 'gst_number' && val && !validateGST(val)) {
                $this.addClass('vrf-invalid');
                $this.after('<span class="vrf-error">Invalid GST format</span>');
                return;
            }
            
            // IFSC validation
            if (name === 'ifsc' && val && !validateIFSC(val)) {
                $this.addClass('vrf-invalid');
                $this.after('<span class="vrf-error">Invalid IFSC code (e.g., ABCD0123456)</span>');
                return;
            }
            
            // Udyam number validation
            if (name === 'udyam_number' && val && !validateUdyam(val)) {
                $this.addClass('vrf-invalid');
                $this.after('<span class="vrf-error">Invalid Udyam format (e.g., UDYAM-XX-00-0000000)</span>');
                return;
            }
            
            // Account number validation
            if (name === 'bank_account' && val && !validateAccountNumber(val)) {
                $this.addClass('vrf-invalid');
                $this.after('<span class="vrf-error">Invalid account number (9-18 digits)</span>');
                return;
            }
        });

        // File validation on change
        $form.on('change', 'input[type="file"]', function() {
            var $this = $(this);
            $this.removeClass('vrf-invalid');
            $this.next('.vrf-error').remove();
            
            var result = validateFile(this);
            if (result !== true) {
                $this.addClass('vrf-invalid');
                $this.after('<span class="vrf-error">' + result + '</span>');
                $this.val('');
                showToast(result, 'error');
            }
        });

        // Helper function for conditional field management
        function toggleConditionalFields(containerSelector, fieldSelector, shouldShow, isRequired) {
            var $container = $(containerSelector);
            var $fields = $(fieldSelector);
            
            if (shouldShow) {
                $container.show();
                $fields.prop('required', isRequired);
            } else {
                $container.hide();
                $fields.prop('required', false).removeClass('vrf-invalid').val('');
                $container.find('.vrf-error').remove();
                // Clear radio buttons
                $fields.filter(':radio').prop('checked', false);
            }
        }

        // GST Registration conditional fields
        $('input[name="gst_registered"]').on('change', function() {
            var isGSTYes = $(this).val() === 'yes';
            toggleConditionalFields('.vrf-gst-fields', '.vrf-gst-conditional, .vrf-gst-conditional-radio', isGSTYes, true);
        });

        // MSME Registration conditional fields
        $('input[name="msme_registered"]').on('change', function() {
            var isMSMEYes = $(this).val() === 'yes';
            toggleConditionalFields('.vrf-msme-yes-fields', '.vrf-msme-conditional', isMSMEYes, true);
            toggleConditionalFields('.vrf-msme-no-fields', '.vrf-msme-no-conditional', !isMSMEYes, true);
        });

        // Country change - load states
        $('#vrf-country').on('change', function() {
            var country = $(this).val();
            $('#vrf-state').html('<option value="">Loading...</option>');
            $('#vrf-city').html('<option value="">Select City</option>');
            
            if (!country) {
                $('#vrf-state').html('<option value="">Select State</option>');
                return;
            }
            
            $.ajax({
                url: vrf_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'vrf_get_states',
                    country: country,
                    nonce: vrf_ajax.nonce
                },
                success: function(resp) {
                    if (resp.success && resp.data.states) {
                        var options = '<option value="">Select State</option>';
                        $.each(resp.data.states, function(i, state) {
                            options += '<option value="' + state + '">' + state + '</option>';
                        });
                        $('#vrf-state').html(options);
                    }
                },
                error: function() {
                    $('#vrf-state').html('<option value="">Error loading states</option>');
                }
            });
        });

        // State change - load cities
        $('#vrf-state').on('change', function() {
            var state = $(this).val();
            $('#vrf-city').html('<option value="">Loading...</option>');
            
            if (!state) {
                $('#vrf-city').html('<option value="">Select City</option>');
                return;
            }
            
            $.ajax({
                url: vrf_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'vrf_get_cities',
                    state: state,
                    nonce: vrf_ajax.nonce
                },
                success: function(resp) {
                    if (resp.success && resp.data.cities) {
                        var options = '<option value="">Select City</option>';
                        $.each(resp.data.cities, function(i, city) {
                            options += '<option value="' + city + '">' + city + '</option>';
                        });
                        $('#vrf-city').html(options);
                    }
                },
                error: function() {
                    $('#vrf-city').html('<option value="">Error loading cities</option>');
                }
            });
        });

        $('.vrf-next').on('click', function(){
            // Validate current panel before moving forward
            var isValid = true;
            var $currentPanel = $('.vrf-panel[data-panel="'+current+'"]');
            
            // Check required fields
            $currentPanel.find('input, select, textarea').each(function() {
                var $this = $(this);
                // Skip hidden elements
                if (!$this.is(':visible')) return;
                
                if ($this.prop('required') && !$this.val()) {
                    $this.addClass('vrf-invalid');
                    isValid = false;
                } else if ($this.hasClass('vrf-invalid')) {
                    isValid = false;
                }
            });
            
            // Special validation for vendor type checkboxes (Step 1)
            if (current === 1) {
                var vendorTypeChecked = $currentPanel.find('.vrf-vendor-type:checked').length > 0;
                if (!vendorTypeChecked) {
                    showToast('Please select at least one Vendor Type', 'error');
                    isValid = false;
                }
            }
            
            // Special validation for GST radio buttons (Step 2)
            if (current === 2) {
                var gstRadioChecked = $('input[name="gst_registered"]:checked').length > 0;
                if (!gstRadioChecked) {
                    showToast('Please select GST Registration status', 'error');
                    isValid = false;
                }
                
                // If GST is Yes, validate GST conditional radios
                if ($('input[name="gst_registered"]:checked').val() === 'yes') {
                    var einvoiceChecked = $('input[name="einvoice_applicability"]:checked').length > 0;
                    var filingChecked = $('input[name="return_filing_frequency"]:checked').length > 0;
                    if (!einvoiceChecked || !filingChecked) {
                        showToast('Please fill all required GST fields', 'error');
                        isValid = false;
                    }
                }
            }
            
            // Special validation for MSME radio buttons (Step 3)
            if (current === 3) {
                var msmeRadioChecked = $('input[name="msme_registered"]:checked').length > 0;
                if (!msmeRadioChecked) {
                    showToast('Please select MSME Registration status', 'error');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                showToast('Please fill all required fields correctly', 'error');
                return;
            }
            
            if (current < $panels.length) {
                showPanel(current+1);
            }
        });

        $('.vrf-back').on('click', function(){
            if (current > 1) {
                showPanel(current-1);
            }
        });

        $('.vrf-step').on('click', function(){
            var step = parseInt($(this).data('step'),10);
            showPanel(step);
        });

        $form.on('submit', function(e){
            e.preventDefault();

            // Validate all required fields
            var allOk = true;
            var $required = $form.find('[required]');
            $required.each(function(){
                // Skip hidden elements
                if (!$(this).is(':visible')) return;
                
                if (!$(this).val()) {
                    $(this).addClass('vrf-invalid');
                    allOk = false;
                } else {
                    $(this).removeClass('vrf-invalid');
                }
            });
            
            // Check for any validation errors
            if ($form.find('.vrf-invalid:visible').length > 0) {
                allOk = false;
            }
            
            // Validate vendor type
            if ($('.vrf-vendor-type:checked').length === 0) {
                showToast('Please select at least one Vendor Type', 'error');
                allOk = false;
            }
            
            // Validate GST radio
            if ($('input[name="gst_registered"]:checked').length === 0) {
                showToast('Please select GST Registration status', 'error');
                allOk = false;
            }
            
            // Validate MSME radio
            if ($('input[name="msme_registered"]:checked').length === 0) {
                showToast('Please select MSME Registration status', 'error');
                allOk = false;
            }
            
            if (!allOk) {
                showToast('Please fill all required fields correctly', 'error');
                // Navigate to first invalid
                var $first = $form.find('.vrf-invalid:visible').first();
                if ($first.length > 0) {
                    var $panel = $first.closest('.vrf-panel');
                    showPanel(parseInt($panel.data('panel'),10));
                }
                return;
            }

            // Build FormData for AJAX including files
            var fd = new FormData( $form[0] );
            fd.append('action', 'vrf_submit');

            showToast('Submitting your registration...', 'info');

            $.ajax({
                url: vrf_ajax.ajax_url,
                method: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                success: function(resp){
                    if (resp.success) {
                        showToast(resp.data.message || 'Registration submitted successfully!', 'success');
                        $form[0].reset();
                        $('#vrf-state').html('<option value="">Select State</option>');
                        $('#vrf-city').html('<option value="">Select City</option>');
                        showPanel(1);
                    } else {
                        showToast(resp.data && resp.data.message ? resp.data.message : 'Submission failed.', 'error');
                    }
                },
                error: function(){
                    showToast('An error occurred during submission. Please try again.', 'error');
                }
            });
        });

    });
})(jQuery);