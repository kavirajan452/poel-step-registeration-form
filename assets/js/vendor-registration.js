(function($){
    $(document).ready(function(){
        var $form = $('#vendor-registration-form');
        var $panels = $('.vrf-panel');
        var current = 1;

        function showPanel(n){
            $panels.hide();
            $('.vrf-panel[data-panel="'+n+'"]').show();
            $('.vrf-step').removeClass('active');
            $('.vrf-step[data-step="'+n+'"]').addClass('active');
            current = n;
            $('html,body').animate({scrollTop: $('.vrf-title').offset().top - 20}, 300);
        }

        $('.vrf-next').on('click', function(){
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

        // simple client-side validation for required inputs within visible panel
        function validateCurrentPanel(){
            var ok = true;
            $('.vrf-panel[data-panel="'+current+'"]').find('[required]').each(function(){
                if ( ! $(this).val() ) {
                    ok = false;
                    $(this).addClass('vrf-invalid');
                } else {
                    $(this).removeClass('vrf-invalid');
                }
            });
            return ok;
        }

        $form.on('submit', function(e){
            e.preventDefault();

            // optional: validate all required fields
            var $required = $form.find('[required]');
            var allOk = true;
            $required.each(function(){
                if (!$(this).val()) {
                    $(this).addClass('vrf-invalid');
                    allOk = false;
                } else {
                    $(this).removeClass('vrf-invalid');
                }
            });
            if (!allOk) {
                $('#vrf-message').show().text('Please fill required fields.').css('color','red');
                // navigate to first invalid
                var $first = $form.find('.vrf-invalid').first();
                var $panel = $first.closest('.vrf-panel');
                showPanel(parseInt($panel.data('panel'),10));
                return;
            }

            // build FormData for AJAX including files
            var fd = new FormData( $form[0] );
            fd.append('action', 'vrf_submit');

            $('#vrf-message').show().text('Submitting...').css('color','#333');

            $.ajax({
                url: vrf_ajax.ajax_url,
                method: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                success: function(resp){
                    if (resp.success) {
                        $('#vrf-message').css('color','green').text(resp.data.message);
                        $form[0].reset();
                        showPanel(1);
                    } else {
                        $('#vrf-message').css('color','red').text(resp.data && resp.data.message ? resp.data.message : 'Submission failed.');
                    }
                },
                error: function(){
                    $('#vrf-message').css('color','red').text('An error occurred during submission.');
                }
            });
        });

    });
})(jQuery);