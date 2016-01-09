/**
 * Area handler
 * Create and edit areas
 *
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/desktop/Panel
 * @require qui/controls/buttons/Button
 * @require controls/grid/Grid
 * @require Locale
 * @require package/quiqqer/areas/bin/classes/Handler
 * @require css!package/quiqqer/areas/bin/controls/Areas.css
 */
define('package/quiqqer/areas/bin/controls/Areas', [

    'qui/QUI',
    'qui/controls/desktop/Panel',
    'qui/controls/buttons/Button',
    'qui/controls/windows/Confirm',
    'controls/grid/Grid',
    'Locale',
    'package/quiqqer/areas/bin/classes/Handler',

    'text!package/quiqqer/areas/bin/controls/AreasAdd.html',
    'css!package/quiqqer/areas/bin/controls/Areas.css'

], function (QUI, QUIPanel, QUIButton, QUIConfirm, Grid, QUILocale, Handler, templateAdd) {
    "use strict";

    var lg = 'quiqqer/areas';

    var Areas = new Handler();

    return new Class({

        Extends: QUIPanel,
        Type   : 'package/quiqqer/areas/bin/controls/Areas',

        Binds: [
            'createChild',
            'editChild',
            'deleteChild',
            'deleteChildren',
            'refresh',
            '$onCreate',
            '$onResize'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Grid = null;

            this.setAttributes({
                'title': QUILocale.get(lg, 'menu.erm.areas.panel.title')
            });

            this.addEvents({
                onCreate: this.$onCreate,
                onResize: this.$onResize
            });
        },

        /**
         * event : on create
         */
        $onCreate: function () {

            // buttons
            this.addButton({
                name     : 'add',
                text     : QUILocale.get('quiqqer/system', 'add'),
                textimage: 'icon-plus',
                events   : {
                    onClick: this.createChild
                }
            });

            this.addButton({
                name     : 'delete',
                text     : QUILocale.get('quiqqer/system', 'delete'),
                textimage: 'icon-trash',
                disabled : true,
                events   : {
                    onClick: function () {

                        var selected = this.$Grid.getSelectedData();

                        var ids = selected.map(function (data) {
                            return data.id;
                        });

                        this.deleteChildren(ids);
                    }.bind(this)
                }
            });

            // Grid
            var self = this;

            var Container = new Element('div').inject(
                this.getContent()
            );

            this.$Grid = new Grid(Container, {
                multipleSelection: true,
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

            this.$Grid.addEvents({
                onRefresh : this.refresh,
                onDblClick: function (event) {
                    self.editChild(
                        self.$Grid.getDataByRow(event.row).id
                    );
                },
                onClick   : function () {
                    var selected = self.$Grid.getSelectedData();

                    if (selected.length) {
                        self.getButtons('delete').enable();
                    } else {
                        self.getButtons('delete').disable();
                    }
                }
            });

            this.$Grid.refresh();
        },

        /**
         * event : on resize
         */
        $onResize: function () {
            if (!this.$Grid) {
                return;
            }

            var Body = this.getContent();

            if (!Body) {
                return;
            }


            var size = Body.getSize();

            this.$Grid.setHeight(size.y - 40);
            this.$Grid.setWidth(size.x - 40);
        },

        /**
         * refresh the display
         *
         * @param {Boolean} [importCheck] - import check if no data exists, default = true
         */
        refresh: function (importCheck) {

            var self = this;

            importCheck = importCheck || true;

            this.Loader.show();
            this.getButtons('delete').disable();

            Areas.getList().then(function (data) {

                if (!data.length && importCheck) {
                    require([
                        'package/quiqqer/areas/bin/controls/Import'
                    ], function (AreaImport) {
                        new AreaImport({
                            events: {
                                onClose  : function () {
                                    self.Loader.hide();
                                },
                                onSuccess: function () {
                                    self.refresh(true);
                                }
                            }
                        }).open();
                    });

                    return;
                }

                var dataEntry,
                    gridData = [];

                for (var i = 0, len = data.length; i < len; i++) {
                    dataEntry = data[i];

                    gridData.push({
                        id       : dataEntry.id,
                        title    : QUILocale.get(
                            dataEntry.title[0],
                            dataEntry.title[1]
                        ),
                        countries: dataEntry.countries
                    });
                }

                self.$Grid.setData({
                    data: gridData
                });

                self.Loader.hide();
            });
        },

        /**
         * Opens the add area dialog
         */
        createChild: function () {
            var self = this;

            new QUIConfirm({
                title      : 'Neue Zone anlegen',
                text       : 'Neue Zone anlegen',
                information: 'Nach dem Anlegen der Zone können Sie Länder der Zone zuweisen.' +
                             '<div class="container"></div>',
                texticon   : 'icon-plus fa fa-plus',
                icon       : 'icon-plus fa fa-plus',
                autoclose  : false,
                maxHeight  : 300,
                maxWidth   : 450,
                events     : {
                    onOpen  : function (Win) {
                        var Container = Win.getContent().getElement('.container');

                        Container.set({
                            html  : templateAdd,
                            styles: {
                                paddingTop: 10
                            }
                        });

                        Container.getElement('input').setStyles({
                            marginTop: 10,
                            maxWidth : 240,
                            width    : '100%'
                        }).focus();
                    },
                    onSubmit: function (Win) {
                        var Input = Win.getContent().getElement('input');

                        if (Input.value === '') {
                            return;
                        }

                        Win.Loader.show();

                        Areas.createChild().then(function (areaId) {
                            self.refresh();
                            Win.close();

                            self.edit(areaId);
                        });
                    }
                }
            }).open();
        },

        /**
         * Opens the delete dialog for one area
         */
        deleteChildren: function (areaIds) {
            var self     = this;
            var areaList = areaIds.join(', ');

            new QUIConfirm({
                title      : 'Markierte Zonen löschen',
                text       : 'Möchten Sie folgende Zonen wirklich löschen?',
                information: 'Die Zonen sind nach dem Löschvorgang nicht ' +
                             'wieder herstellbar. Folgende Zonen werden gelöscht:<br /><br />' +
                             areaList,
                icon       : 'icon-trash',
                textimage  : 'icon-trash',
                maxHeight  : 300,
                maxWidth   : 450,
                events     : {
                    onSubmit: function (Win) {
                        Win.Loader.show();
                        self.Loader.show();

                        Areas.deleteChildren(areaIds).then(function () {
                            self.$Grid.setData({
                                data: []
                            });

                            self.refresh();
                            Win.close();
                        });
                    }
                }
            }).open();
        },

        /**
         * opens the edit sheet
         *
         * @param {Number} areaId
         */
        editChild: function (areaId) {
            var self = this;

            self.Loader.show();

            this.createSheet({
                events: {
                    onShow: function (Sheet) {

                        Sheet.getContent().set({
                            styles: {
                                padding: 20
                            }
                        });

                        require([
                            'package/quiqqer/areas/bin/controls/AreaEdit'
                        ], function (AreaEdit) {

                            var Area = new AreaEdit({
                                areaId: areaId,
                                events: {
                                    onLoaded: function () {
                                        self.Loader.hide();
                                    }
                                }
                            }).inject(Sheet.getContent());

                            Sheet.addButton({
                                text     : QUILocale.get('quiqqer/system', 'save'),
                                textimage: 'icon-save',
                                events   : {
                                    click: function () {
                                        self.Loader.show();

                                        Area.save().then(function () {
                                            self.refresh();
                                        });
                                    }
                                }
                            });
                        });

                    }
                }
            }).show();
        }
    });
});
