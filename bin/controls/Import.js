/**
 * Import window
 *
 * @module package/quiqqer/areas/bin/controls/Import
 * @author www.pcsg.de (Henning Leutz)
 *
 * @event onSuccess
 */
define('package/quiqqer/areas/bin/controls/Import', [

    'qui/QUI',
    'qui/controls/windows/Confirm',
    'qui/controls/buttons/Select',
    'Locale',
    'Ajax',
    'controls/upload/Form',

    'text!package/quiqqer/areas/bin/controls/Import.html'

], function (QUI,
             QUIConfirm,
             QUISelect,
             QUILocale,
             QUIAjax,
             UploadForm,
             templateImport) {
    "use strict";

    return new Class({

        Extends: QUIConfirm,
        Type   : 'package/quiqqer/areas/bin/controls/Import',

        Binds: [
            '$onOpen',
            '$onSubmit'
        ],

        initialize: function (options) {

            this.setAttributes({
                maxHeight: 460,
                maxWidth : 690,
                title    : 'Noch keine Zonen vorhanden', // #locale
                icon     : 'fa fa-upload',
                autoclose: false
            });

            this.parent(options);

            this.$Select = null;
            this.$Upload = null;

            this.addEvents({
                onOpen  : this.$onOpen,
                onSubmit: this.$onSubmit
            });
        },

        /**
         * events: on open
         */
        $onOpen: function () {
            var self = this;

            this.Loader.show();
            this.getContent().set('html', templateImport);

            var Content   = this.getContent(),
                Available = Content.getElement('.available-imports'),
                Upload    = Content.getElement('.own-import');

            this.$Upload = new UploadForm({
                maxuploads: 1
            }).inject(Upload);

            this.$Upload.setParam(
                'onfinish',
                'package_quiqqer_areas_ajax_import_upload'
            );

            this.$Select = new QUISelect({
                styles: {
                    width: '100%'
                },
                events: {
                    onChange: function (value) {
                        if (value !== '') {
                            self.$Upload.disable();
                        } else {
                            self.$Upload.enable();
                        }
                    }
                }
            }).inject(Available);

            QUIAjax.get('package_quiqqer_areas_ajax_import_available', function (result) {
                self.$Select.appendChild('&nbsp;', '');

                for (var i = 0, len = result.length; i < len; i++) {
                    self.$Select.appendChild(
                        QUILocale.get(
                            result[i].locale[0],
                            result[i].locale[1]
                        ),
                        result[i].file
                    );
                }

                self.Loader.hide();
            }, {
                'package': 'quiqqer/areas'
            });
        },

        /**
         * event : on submit
         */
        $onSubmit: function () {
            this.Loader.show();

            var self        = this,
                selectValue = this.$Select.getValue();

            if (!selectValue || selectValue === '') {
                this.$Upload.addEvent('onComplete', function () {
                    self.Loader.hide();
                    self.close();
                    self.fireEvent('success');
                });

                this.$Upload.submit();
                return;
            }

            QUIAjax.get('package_quiqqer_areas_ajax_import_preconfigure', function () {
                require(['package/quiqqer/translator/bin/classes/Translator'], function (Translator) {
                    new Translator().refreshLocale().then(function () {
                        self.close();
                        self.fireEvent('success');
                    });
                });
            }, {
                'package' : 'quiqqer/areas',
                importName: selectValue
            });
        }
    });
});
