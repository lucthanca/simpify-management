define([
    'jquery'
], function ($) {
    'use strict';

    return function (target) {
        $.validator.addMethod(
            'validate-shopify-api-version',
            function (value) {
                const regex = /([0-9]{4}-[0-9]{2})|unstable/;
                return !($.mage.isEmpty(value) || !regex.test(value));

            },
            $.mage.__('Version string must be of YYYY-MM or unstable')
        );
        return target;
    }
});
