/**
 * @module package/quiqqer/payment-gateway/bin/controls/PaymentDisplay
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/payments-gateway/bin/controls/frontend/PaymentDisplay', [

    'qui/QUI',
    'qui/controls/Control'

], function (QUI, QUIControl) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/payments-gateway/bin/controls/frontend/PaymentDisplay',

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * event: on import
         */
        $onImport: function () {
            return;

            var self   = this,
                Cancel = this.getElm().getElement('[name="cancel"]');

            Cancel.set('disabled', false);
            Cancel.addEvent('click', function (event) {
                event.stop();

                self.fireEvent('processingError', [this]);
            });
        }
    });
});