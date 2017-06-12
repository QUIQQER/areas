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
 * @event onDblClick [self]
 */
define('package/quiqqer/areas/bin/controls/search/Result', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'controls/grid/Grid',
    'Locale'

], function (QUI, QUIControl, QUIButton, Grid, QUILocale) {
    "use strict";

    var lg = 'quiqqer/areas';

    return new Class({
        Extends: QUIControl,
        Type   : 'package/quiqqer/areas/bin/controls/search/Result',

        Binds: [
            '$onInject'
        ],

        options: {
            multipleSelection: true
        },

        initialize: function (options) {
            this.parent(options);

            this.$Grid = null;

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * Return the DOMNode Element
         * @returns {HTMLDivElement}
         */
        create: function () {
            var Elm = this.parent();

            Elm.set('html', '');

            Elm.setStyles({
                'float' : 'left',
                'height': '100%',
                'width' : '100%'
            });

            var Container = new Element('div').inject(Elm);

            this.$Grid = new Grid(Container, {
                multipleSelection: this.getAttribute('multipleSelection'),
                columnModel      : [{
                    header   : QUILocale.get('quiqqer/system', 'id'),
                    dataIndex: 'id',
                    dataType : 'number',
                    width    : 60
                }, {
                    header   : QUILocale.get(lg, 'area.grid.areaname.title'),
                    dataIndex: 'title',
                    dataType : 'string',
                    width    : 200
                }, {
                    header   : QUILocale.get(lg, 'area.grid.areaname.countries'),
                    dataIndex: 'countries',
                    dataType : 'string',
                    width    : 300
                }]
            });

            this.$Grid.addEvent('onDblClick', function () {
                this.fireEvent('dblClick', [this]);
            }.bind(this));

            return Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            this.fireEvent('loaded');
        },

        /**
         * Set data to the grid
         *
         * @param {Object} data - grid data
         */
        setData: function (data) {
            if (!this.$Grid) {
                return;
            }

            this.$Grid.setData(data);
        },

        /**
         * Return the selected data
         *
         * @returns {Array}
         */
        getSelected: function () {
            if (!this.$Grid) {
                return [];
            }

            return this.$Grid.getSelectedData();
        },

        /**
         * Resize the control
         *
         * @return {Promise}
         */
        resize: function () {
            var size = this.getElm().getSize();

            this.$Grid.setWidth(size.x);
            this.$Grid.setHeight(size.y);

            return this.$Grid.resize();
        }
    });
});
