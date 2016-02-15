/**
 * Area edit control
 *
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require qui/controls/buttons/Button
 * @require Locale
 * @require package/quiqqer/areas/bin/classes/Handler
 * @require package/quiqqer/translator/bin/controls/VariableTranslation
 * @require text!package/quiqqer/areas/bin/controls/AreasSettings.html
 * @require css!package/quiqqer/areas/bin/controls/Areas.css
 */
define('package/quiqqer/areas/bin/controls/AreaEdit', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'Locale',
    'package/quiqqer/areas/bin/classes/Handler',
    'package/quiqqer/translator/bin/controls/VariableTranslation',

    'text!package/quiqqer/areas/bin/controls/AreasSettings.html',
    'css!package/quiqqer/areas/bin/controls/Areas.css'

], function (QUI, QUIControl, QUIButton, QUILocale, Handler, Translation, templateAreasSettings) {
    "use strict";

    var lg = 'quiqqer/areas';

    var Areas = new Handler();

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/areas/bin/controls/AreaEdit',

        Binds: [
            '$onInject',
            '$removeSelectedCountries',
            '$addSelectedCountries'
        ],

        options: {
            areaId: false
        },

        initialize: function (options) {
            this.parent(options);

            this.$SelectOwnCountries       = null;
            this.$SelectAvailableCountries = null;

            this.$Text = null;

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
            this.$Elm = this.parent();
            this.$Elm.set('html', templateAreasSettings);

            return this.$Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            var areaId = this.getAttribute('areaId');

            Promise.all([
                Areas.getChild(areaId),
                Areas.getUnAssignedCountries()
            ]).then(function (result) {

                var data       = result[0],
                    unassigned = result[1],
                    Content    = this.getElm();

                var IdField, countries;

                IdField = Content.getElement(
                    '[name="areaId"]'
                );

                this.$Text = Content.getElement(
                    '.quiqqer-areas-setting-areaTitle'
                );

                this.$SelectOwnCountries = Content.getElement(
                    '[name="areaCountries"]'
                );

                this.$SelectAvailableCountries = Content.getElement(
                    '[name="availableCountries"]'
                );

                IdField.value = areaId;

                new Translation({
                    'group': 'quiqqer/areas',
                    'var'  : 'area.' + areaId + '.title'
                }).inject(this.$Text);

                // countries
                countries = [];

                if (data.countries !== '') {
                    countries = data.countries.split(',');
                }

                for (var i = 0, len = countries.length; i < len; i++) {
                    new Element('option', {
                        html : QUILocale.get(
                            'quiqqer/countries',
                            'country.' + countries[i]
                        ),
                        value: countries[i]
                    }).inject(this.$SelectOwnCountries);
                }

                for (i = 0, len = unassigned.length; i < len; i++) {
                    new Element('option', {
                        html : QUILocale.get(
                            'quiqqer/countries',
                            'country.' + unassigned[i]
                        ),
                        value: unassigned[i]
                    }).inject(this.$SelectAvailableCountries);
                }

                new QUIButton({
                    text     : QUILocale.get(lg, 'control.AreaEdit.btn.delete'),
                    textimage: 'fa fa-angle-right',
                    events   : {
                        click: this.$removeSelectedCountries
                    },
                    styles   : {
                        'float': 'right'
                    }
                }).inject(this.$SelectOwnCountries.getParent());


                new QUIButton({
                    text     : QUILocale.get(lg, 'control.AreaEdit.btn.add'),
                    textimage: 'fa fa-angle-left',
                    events   : {
                        click: this.$addSelectedCountries
                    }
                }).inject(this.$SelectAvailableCountries.getParent());

                this.fireEvent('loaded');

            }.bind(this));
        },

        /**
         * remove selected countries to the area
         */
        $removeSelectedCountries: function () {
            var selected = this.$SelectOwnCountries.getElements(':selected');

            selected.each(function (Elm) {
                Elm.inject(this.$SelectAvailableCountries);
            }.bind(this));
        },

        /**
         * add selected countries to the area
         */
        $addSelectedCountries: function () {
            var selected = this.$SelectAvailableCountries.getElements(':selected');

            selected.each(function (Elm) {
                Elm.inject(this.$SelectOwnCountries);
            }.bind(this));
        },

        /**
         * Save the area
         *
         * @return {Promise}
         */
        save: function () {
            var selected  = this.$SelectOwnCountries.getElements('option');
            var countries = selected.map(function (Option) {
                return Option.value;
            });

            return Areas.save(this.getAttribute('areaId'), {
                countries: countries.join(',')
            });
        }
    });
});
