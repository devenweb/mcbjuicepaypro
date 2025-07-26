jQuery(document).ready(function($) {
    function initMediaUploader(buttonClass, inputName, previewClass) {
        $(document).on('click', buttonClass, function(e) {
            e.preventDefault();
            var button = $(this);
            var input = $('input[name="' + inputName + '"]');
            var preview = button.siblings(previewClass);

            var mediaUploader = wp.media({
                title: 'Select or Upload Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                input.val(attachment.url);
                preview.html('<img src="' + attachment.url + '" style="max-width: 200px;" />');
            });

            mediaUploader.open();
        });
    }

    initMediaUploader('.upload_qr_code_button', 'woocommerce_mcb_juice_qr_gateway_premium_qr_code_url', '.qr-code-preview');
    initMediaUploader('.upload_bank_logo_button', 'woocommerce_mcb_juice_qr_gateway_premium_bank_logo', '.bank-logo-preview');
});