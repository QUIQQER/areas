/**
 * @module package/quiqqer/areas/bin/controls/Areas
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/elements/Select
 * @require Ajax
 * @require Locale
 */
define('package/quiqqer/areas/bin/controls/Select', [

    'qui/QUI',
    'qui/controls/elements/Select',
    'Ajax',
    'Locale'

], function (QUI, QUIElementSelect, QUIAjax, QUILocale) {
    "use strict";

    return new Class({

        Extends: QUIElementSelect,
        Type   : 'package/quiqqer/areas/bin/controls/Select',

        Binds: [
            'areaSearch'
        ],

        initialize: function (options) {
            this.parent(options);

            this.setAttribute('Search', this.areaSearch);
            this.setAttribute('icon', 'fa fa-globe');
            this.setAttribute('child', 'package/quiqqer/areas/bin/controls/SelectItem');
            this.setAttribute('searchbutton', false);

            this.setAttribute(
                'placeholder',
                QUILocale.get('quiqqer/areas', 'control.select.placeholder')
            );
        },

        /**
         * Search areas
         *
         * @param {String} value
         * @returns {Promise}
         */
        areaSearch: function (value) {
            return new Promise(function (resolve) {
                QUIAjax.get('package_quiqqer_areas_ajax_search', resolve, {
                    'package': 'quiqqer/areas',
                    params   : JSON.encode({
                        where_or: {
                            title    : value,
                            countries: value
                        },
                        limit   : '0,10'
                    })
                });
            });
        }
    });
});