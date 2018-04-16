/**
 * Area edit control
 *
 * @module package/quiqqer/areas/bin/controls/AreaEdit
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/areas/bin/controls/AreaEdit', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'Locale',
    'Mustache',
    'package/quiqqer/areas/bin/classes/Handler',
    'package/quiqqer/translator/bin/controls/Update',

    'text!package/quiqqer/areas/bin/controls/AreasSettings.html',
    'css!package/quiqqer/areas/bin/controls/Areas.css'

], function (QUI, QUIControl, QUIButton, QUILocale, Mustache, Handler, Translation, templateAreasSettings) {
    "use strict";

    var lg    = 'quiqqer/areas';
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
            this.$Translation              = null;

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
            this.$Elm.set('html', Mustache.render(templateAreasSettings, {}));
            this.$Elm.addClass('quiqqer-areas-edit');

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

                this.$SelectOwnCountries = Content.getElement(
                    '[name="areaCountries"]'
                );

                this.$SelectAvailableCountries = Content.getElement(
                    '[name="availableCountries"]'
                );

                IdField.value = areaId;


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


                // translation
                var Text = Content.getElement(
                    '.quiqqer-areas-setting-areaTitle'
                );

                Text.set({
                    'data-qui': 'package/quiqqer/translator/bin/controls/Update',

                    'data-qui-options-group'  : 'quiqqer/areas',
                    'data-qui-options-var'    : 'area.' + areaId + '.title',
                    'data-qui-options-package': 'quiqqer/areas'
                });

                QUI.parse(this.getElm()).then(function () {
                    this.$Translation = QUI.Controls.getById(Text.get('data-quiid'));
                    this.fireEvent('loaded');
                }.bind(this));
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
            var selected  = this.$SelectOwnCountries.getElements('option'),
                countries = selected.map(function (Option) {
                    return Option.value;
                });

            return this.$Translation.save().then(function () {
                return Areas.save(this.getAttribute('areaId'), {
                    countries: countries.join(',')
                });
            }.bind(this)).then(function () {
                this.fireEvent('save', [this]);
            }.bind(this));
        }
    });
});
