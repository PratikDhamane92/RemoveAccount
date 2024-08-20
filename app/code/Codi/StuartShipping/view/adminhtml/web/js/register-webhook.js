define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';

    return function (config) {
        $(document).on('click', '#register_webhook_button', function () {
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    form_key: FORM_KEY
                },
                showLoader: true,
                success: function (response) {
                    alert({
                        title: response.success ? $.mage.__('Success') : $.mage.__('Error'),
                        content: response.message
                    });
                },
                error: function () {
                    alert({
                        title: $.mage.__('Error'),
                        content: $.mage.__('An error occurred while registering the webhook.')
                    });
                }
            });
        });
    };
});