/**
 * Opens a area search window
 *
 * @module package/quiqqer/areas/bin/controls/search/Search
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/areas/bin/controls/search/Window', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/windows/Confirm',
    'package/quiqqer/areas/bin/classes/Handler',
    'Locale',

    'css!package/quiqqer/areas/bin/controls/search/Window.css'

], function (QUI, QUIControl, QUIConfirm, Handler, QUILocale) {
    "use strict";

    var lg = 'quiqqer/areas';

    return new Class({

        Extends: QUIConfirm,
        Type   : 'package/quiqqer/areas/bin/controls/search/Window',

        Binds: [
            '$onOpen'
        ],

        options: {
            maxHeight: 600,
            maxWidth : 800,
            icon     : 'fa fa-globe',
            title    : QUILocale.get(lg, 'control.search.window.title'),
            autoclose: false,

            cancel_button: {
                text     : QUILocale.get('quiqqer/system', 'cancel'),
                textimage: 'fa fa-remove'
            },
            ok_button    : {
                text     : QUILocale.get('quiqqer/system', 'accept'),
                textimage: 'fa fa-globe'
            }
        },

        initialize: function (options) {
            this.parent(options);

            this.$Search = null;
            this.$Result = null;

            this.addEvents({
                onOpen: this.$onOpen
            });
        },

        /**
         * Return the DOMNode Element
         *
         * @returns {HTMLDivElement}
         */
        $onOpen: function (Win) {
            var self    = this,
                Content = Win.getContent();

            Win.Loader.show();

            Content.set('html', '');
            Content.addClass('areas-search');

            this.$ResultContainer = new Element('div', {
                'class': 'areas-search-resultContainer'
            }).inject(Content);

            this.$SearchContainer = new Element('div', {
                'class': 'areas-search-searchContainer'
            }).inject(Content);

            require([
                'package/quiqqer/areas/bin/controls/search/Search',
                'package/quiqqer/areas/bin/controls/search/Result'
            ], function (Search, Result) {
                self.$Search = new Search({
                    button: true,
                    events: {
                        onLoaded: function () {
                            self.Loader.hide();
                        },
                        onSearch: function (Search, result) {
                            self.$renderResult(result);
                        }
                    }
                }).inject(self.$SearchContainer);

                self.$Result = new Result({
                    events: {
                        onDblClick: function () {
                            self.submit();
                        }
                    }
                }).inject(self.$ResultContainer);

                self.$Result.resize();
                self.$Search.focus();
                self.$Search.search();
            });
        },

        /**
         * Submit
         */
        submit: function () {
            if (!this.$Result) {
                return;
            }

            this.fireEvent('submit', [this, this.$Result.getSelected()]);

            if (this.getAttribute('autoclose')) {
                this.close();
            }
        },

        /**
         * Render the result
         *
         * @param result
         */
        $renderResult: function (result) {
            this.$Result.setData({
                data: result
            });
        }
    });
});
