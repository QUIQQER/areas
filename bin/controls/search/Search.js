/**
 *
 * @module package/quiqqer/areas/bin/controls/search/Search
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require qui/controls/buttons/Button
 * @require package/quiqqer/areas/bin/classes/Handler
 *
 * @event onLoaded
 * @event onSearch [self, result]
 * @event searchBegin [self]
 */
define('package/quiqqer/areas/bin/controls/search/Search', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'qui/utils/Form',
    'package/quiqqer/areas/bin/classes/Handler',
    'Locale'

], function (QUI, QUIControl, QUIButton, QUIFormUtils, Handler, QUILocale) {
    "use strict";

    var Areas = new Handler();
    var lg    = 'quiqqer/areas';

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/areas/bin/controls/search/Search',

        Binds: [
            '$onInject'
        ],

        options: {
            button: true
        },

        initialize: function (options) {
            this.parent(options);

            this.$Input  = null;
            this.$Button = null;

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * Return the DOMNode Element
         *
         * @returns {HTMLDivElement}
         */
        create: function () {
            var self = this,
                Elm  = this.parent();

            this.$Input = new Element('input', {
                styles     : {
                    'float': 'left',
                    width  : '100%'
                },
                placeholder: QUILocale.get(lg, 'control.select.placeholder'),
                events     : {
                    keyup: function (event) {
                        if (event.key === 'enter') {
                            self.search();
                        }
                    }
                }
            }).inject(Elm);

            if (this.getAttribute('button')) {
                this.$Button = new QUIButton({
                    icon  : 'fa fa-search',
                    styles: {
                        width: 60
                    }
                }).inject(Elm);

                this.$Input.setStyle('width', 'calc(100% - 60px)');
            }

            Elm.setStyles({
                'float': 'left',
                'width': '100%'
            });

            return Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            this.fireEvent('loaded');
        },

        /**
         * Set the focus to the input element
         */
        focus: function () {
            if (this.$Input) {
                this.$Input.focus();
            }
        },

        /**
         * Execute a search
         *
         * @return {Promise}
         */
        search: function () {
            this.fireEvent('searchBegin', [this]);

            return Areas.search(this.$Input.value).then(function (result) {
                this.fireEvent('search', [this, result]);
                return result;
            }.bind(this));
        }
    });
});
