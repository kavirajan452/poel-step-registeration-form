(function($){
    $(document).ready(function(){
        var $form = $('#customer-registration-form');
        var $panels = $('.vrf-panel');
        var current = 1;
        
        // Trigger country change on page load to populate states for India
        $('#crf-country').trigger('change');

        // Toast notification function
        function showToast(message, type) {
            // Remove existing toast
            $('.vrf-toast').remove();
            
            var $toast = $('<div class="vrf-toast"></div>').addClass('vrf-toast-' + type).text(message);
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

        function validateTAN(tan) {
            var re = /^[A-Z]{4}[0-9]{5}[A-Z]{1}$/;
            return re.test(tan);
        }

        function validateGST(gst) {
            var re = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
            return re.test(gst);
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
            
            // TAN validation
            if (name === 'tan_number' && val && !validateTAN(val)) {
                $this.addClass('vrf-invalid');
                $this.after('<span class="vrf-error">Invalid TAN format (e.g., ABCD12345E)</span>');
                return;
            }
            
            // GST validation
            if (name === 'gst_number' && val && !validateGST(val)) {
                $this.addClass('vrf-invalid');
                $this.after('<span class="vrf-error">Invalid GST format</span>');
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
                // Get field label for better toast message
                var fieldLabel = $this.closest('.vrf-row').find('label').first().text().replace(' *', '').trim();
                showToast(fieldLabel + ': ' + result, 'error');
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
            }
        }

        // Remove highlight on checkbox/radio selection
        $form.on('change', 'input[type="checkbox"], input[type="radio"]', function() {
            $(this).closest('.vrf-row').removeClass('vrf-field-required');
        });

        // GST Registration conditional fields
        $('input[name="gst_registered"]').on('change', function() {
            var isGSTYes = $(this).val() === 'yes';
            toggleConditionalFields('.vrf-gst-fields', '.vrf-gst-conditional', isGSTYes, true);
            // GST number is required when GST registered is Yes
            $('.vrf-gst-fields input[name="gst_number"]').prop('required', isGSTYes);
            $('.vrf-gst-fields input[name="gst_certificate"]').prop('required', isGSTYes);
        });

        // Country change - load states
        $('#crf-country').on('change', function() {
            var country = $(this).val();
            $('#crf-state').html('<option value="">Loading...</option>');
            $('#crf-city').html('<option value="">Select City</option>');
            
            if (!country) {
                $('#crf-state').html('<option value="">Select State</option>');
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
                        $('#crf-state').html(options);
                    }
                },
                error: function() {
                    $('#crf-state').html('<option value="">Error loading states</option>');
                }
            });
        });

        // State change - load cities
        $('#crf-state').on('change', function() {
            var state = $(this).val();
            $('#crf-city').html('<option value="">Loading...</option>');
            
            if (!state) {
                $('#crf-city').html('<option value="">Select City</option>');
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
                        $('#crf-city').html(options);
                    }
                },
                error: function() {
                    $('#crf-city').html('<option value="">Error loading cities</option>');
                }
            });
        });

        // Validate current panel function (reusable for both Next button and tab clicks)
        function validateCurrentPanel() {
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
            
            // Special validation for customer type checkboxes (Step 1)
            if (current === 1) {
                var $customerTypeRow = $currentPanel.find('.vrf-customer-type').first().closest('.vrf-row');
                var customerTypeChecked = $currentPanel.find('.vrf-customer-type:checked').length > 0;
                if (!customerTypeChecked) {
                    $customerTypeRow.addClass('vrf-field-required');
                    showToast('Customer Type: Please select at least one option', 'error');
                    isValid = false;
                } else {
                    $customerTypeRow.removeClass('vrf-field-required');
                }
            }
            
            // Special validation for GST radio buttons (Step 2)
            if (current === 2) {
                var $gstRow = $('input[name="gst_registered"]').first().closest('.vrf-row');
                var gstRadioChecked = $('input[name="gst_registered"]:checked').length > 0;
                if (!gstRadioChecked) {
                    $gstRow.addClass('vrf-field-required');
                    showToast('GST Registration: Please select Yes or No', 'error');
                    isValid = false;
                } else {
                    $gstRow.removeClass('vrf-field-required');
                }
            }
            
            return isValid;
        }

        $('.vrf-next').on('click', function(){
            var isValid = validateCurrentPanel();
            
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
            
            // If clicking on current step, do nothing
            if (step === current) {
                return;
            }
            
            // If clicking on a previous step, allow navigation
            if (step < current) {
                showPanel(step);
            } else {
                // Clicking on a future step, validate current step first
                var isValid = validateCurrentPanel();
                
                if (!isValid) {
                    showToast('Please complete the current step before proceeding', 'error');
                    return;
                }
                
                showPanel(step);
            }
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
            
            // Validate customer type
            var $customerTypeRow = $('.vrf-customer-type').first().closest('.vrf-row');
            if ($('.vrf-customer-type:checked').length === 0) {
                $customerTypeRow.addClass('vrf-field-required');
                showToast('Customer Type: Please select at least one option', 'error');
                allOk = false;
            } else {
                $customerTypeRow.removeClass('vrf-field-required');
            }
            
            // Validate GST radio
            var $gstRow = $('input[name="gst_registered"]').first().closest('.vrf-row');
            if ($('input[name="gst_registered"]:checked').length === 0) {
                $gstRow.addClass('vrf-field-required');
                showToast('GST Registration: Please select Yes or No', 'error');
                allOk = false;
            } else {
                $gstRow.removeClass('vrf-field-required');
            }
            
            if (!allOk) {
                // Navigate to first invalid field or first required radio/checkbox
                var $first = $form.find('.vrf-invalid:visible, .vrf-field-required').first();
                if ($first.length > 0) {
                    var $panel = $first.closest('.vrf-panel');
                    if ($panel.length > 0) {
                        showPanel(parseInt($panel.data('panel'),10));
                    }
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
                        $('#crf-state').html('<option value="">Select State</option>');
                        $('#crf-city').html('<option value="">Select City</option>');
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
