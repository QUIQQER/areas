/**
 * @module package/quiqqer/areas/bin/controls/Areas
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/areas/bin/controls/SelectItem', [

    'qui/QUI',
    'qui/controls/elements/SelectItem',
    'Ajax'

], function (QUI, QUIElementSelectItem, QUIAjax) {
    "use strict";

    return new Class({

        Extends: QUIElementSelectItem,
        Type   : 'package/quiqqer/areas/bin/controls/SelectItem',

        Binds: [
            'refresh'
        ],

        initialize: function (options) {
            this.parent(options);

            this.setAttribute('icon', 'fa fa-globe');
        },

        /**
         * Refresh the display
         *
         * @returns {Promise}
         */
        refresh: function () {
            return new Promise(function (resolve) {
                QUIAjax.get('package_quiqqer_areas_ajax_get', function (result) {
                    this.$Text.set({
                        html: result.title
                    });

                    resolve();

                }.bind(this), {
                    'package': 'quiqqer/areas',
                    id       : this.getAttribute('id')
                });
            }.bind(this));
        }
    });
});