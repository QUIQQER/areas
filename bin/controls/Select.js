/**
 * @module package/quiqqer/areas/bin/controls/Areas
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/areas/bin/controls/Select', [

    'qui/QUI',
    'qui/controls/elements/Select',
    'package/quiqqer/areas/bin/classes/Handler',
    'Ajax',
    'Locale'

], function (QUI, QUIElementSelect, Handler, QUIAjax, QUILocale) {
    "use strict";

    var Areas = new Handler();

    return new Class({

        Extends: QUIElementSelect,
        Type   : 'package/quiqqer/areas/bin/controls/Select',

        Binds: [
            'areaSearch',
            '$onSearchButtonClick'
        ],

        initialize: function (options) {
            this.parent(options);

            this.setAttribute('Search', this.areaSearch);
            this.setAttribute('icon', 'fa fa-globe');
            this.setAttribute('child', 'package/quiqqer/areas/bin/controls/SelectItem');
            this.setAttribute('searchbutton', true);

            this.setAttribute(
                'placeholder',
                QUILocale.get('quiqqer/areas', 'control.select.placeholder')
            );

            this.addEvents({
                onSearchButtonClick: this.$onSearchButtonClick
            });
        },

        /**
         * Search areas
         *
         * @param {String} value
         * @returns {Promise}
         */
        areaSearch: function (value) {
            return Areas.search(value, {
                limit: '0,10'
            });
        },

        /**
         * event : on search button click
         *
         * @param {Object} self - select object
         * @param {Object} Btn - button object
         */
        $onSearchButtonClick: function (self, Btn) {
            Btn.setAttribute('icon', 'fa fa-spinner fa-spin');

            require(['package/quiqqer/areas/bin/controls/search/Window'], function (Window) {
                new Window({
                    autoclose: true,
                    multiple : this.getAttribute('multiple'),
                    events   : {
                        onSubmit: function (Win, data) {
                            data = data.map(function (Entry) {
                                return parseInt(Entry.id);
                            });

                            for (var i = 0, len = data.length; i < len; i++) {
                                this.addItem(data[i]);
                            }
                        }.bind(this)
                    }
                }).open();

                Btn.setAttribute('icon', 'fa fa-search');
            }.bind(this));
        }
    });
});