/**
 * Area handler
 * Create and edit areas
 *
 * @module package/quiqqer/areas/bin/controls/Areas
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/areas/bin/controls/Areas', [

    'qui/QUI',
    'qui/controls/desktop/Panel',
    'qui/controls/buttons/Button',
    'qui/controls/windows/Confirm',
    'controls/grid/Grid',
    'Locale',
    'Mustache',
    'package/quiqqer/areas/bin/classes/Handler',

    'text!package/quiqqer/areas/bin/controls/AreasAdd.html',
    'css!package/quiqqer/areas/bin/controls/Areas.css'

], function (QUI, QUIPanel, QUIButton, QUIConfirm, Grid, QUILocale, Mustache, Handler, templateAdd) {
    "use strict";

    var lg    = 'quiqqer/areas';
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
                title: QUILocale.get(lg, 'menu.erp.areas.panel.title'),
                icon : 'fa fa-globe'
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
                textimage: 'fa fa-plus',
                events   : {
                    onClick: this.createChild
                }
            });

            this.addButton({
                name     : 'delete',
                text     : QUILocale.get('quiqqer/system', 'delete'),
                textimage: 'fa fa-trash',
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
                pagination       : true,
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

            Areas.getList({
                perPage: this.$Grid.options.perPage,
                page   : this.$Grid.options.page
            }).then(function (data) {
                if (!data.total && importCheck) {
                    require(['package/quiqqer/areas/bin/controls/Import'], function (AreaImport) {
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

                for (var i = 0, len = data.data.length; i < len; i++) {
                    dataEntry = data.data[i];

                    gridData.push({
                        id       : dataEntry.id,
                        title    : QUILocale.get(
                            'quiqqer/areas',
                            'area.' + dataEntry.id + '.title'
                        ),
                        countries: dataEntry.countries
                    });
                }

                data.data = gridData;

                self.$Grid.setData(data);
                self.Loader.hide();
            });
        },

        /**
         * Opens the add area dialog
         */
        createChild: function () {
            var self = this;

            new QUIConfirm({
                title      : QUILocale.get(lg, 'window.create.title'),
                text       : QUILocale.get(lg, 'window.create.text'),
                information: QUILocale.get(lg, 'window.create.information') + '<div class="container"></div>',
                texticon   : 'fa fa-plus',
                icon       : 'fa fa-plus',
                autoclose  : false,
                maxHeight  : 300,
                maxWidth   : 450,

                cancel_button: {
                    text     : QUILocale.get('quiqqer/quiqqer', 'cancel'),
                    textimage: 'fa fa-remove'
                },
                ok_button    : {
                    text     : QUILocale.get('quiqqer/quiqqer', 'create'),
                    textimage: 'fa fa-plus'
                },

                events: {
                    onOpen  : function (Win) {
                        var Container = Win.getContent().getElement('.container');

                        Container.set({
                            html  : Mustache.render(templateAdd, {
                                label: QUILocale.get(lg, 'window.create.label')
                            }),
                            styles: {
                                paddingTop: 10
                            }
                        });

                        var Input = Container.getElement('input');

                        Input.setStyles({
                            marginTop: 10,
                            maxWidth : 240,
                            width    : '100%'
                        });

                        Input.addEvent('keyup', function (event) {
                            if (event.key === 'enter') {
                                Win.submit();
                            }
                        });

                        Input.focus();
                    },
                    onSubmit: function (Win) {
                        var Input = Win.getContent().getElement('input');

                        if (Input.value === '') {
                            return;
                        }

                        Win.Loader.show();

                        Areas.createChild({
                            title: Input.value
                        }).then(function (areaId) {
                            self.refresh();
                            Win.close();

                            self.editChild(areaId);
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
                title      : QUILocale.get(lg, 'window.delete.title'),
                text       : QUILocale.get(lg, 'window.delete.text'),
                information: QUILocale.get(lg, 'window.delete.information', {
                    areaList: areaList
                }),
                icon       : 'fa fa-trash',
                textimage  : 'fa fa-trash',
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

                        require(['package/quiqqer/areas/bin/controls/AreaEdit'], function (AreaEdit) {
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
                                textimage: 'fa fa-save',
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
