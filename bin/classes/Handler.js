/**
 * Area handler
 * Create and edit areas
 *
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 * @require Ajax
 */
define('package/quiqqer/areas/bin/classes/Handler', [

    'qui/QUI',
    'qui/classes/DOM',
    'Ajax'

], function (QUI, QUIDOM, Ajax) {
    "use strict";

    return new Class({

        Extends: QUIDOM,
        Type   : 'package/quiqqer/areas/bin/classes/Handler',

        /**
         * Search areas
         *
         * @param {Object} [params] - query params
         * @returns {Promise}
         */
        search: function (params) {
            params = params || {};

            return new Promise(function (resolve, reject) {
                Ajax.get('package_quiqqer_areas_ajax_search', resolve, {
                    'package': 'quiqqer/areas',
                    onError  : reject,
                    params   : JSON.encode(params)
                });
            });
        },

        /**
         *
         * @param {number} areaId
         * @returns {Promise}
         */
        getChild: function (areaId) {
            return new Promise(function (resolve, reject) {
                Ajax.get('package_quiqqer_areas_ajax_get', resolve, {
                    'package': 'quiqqer/areas',
                    onError  : reject,
                    id       : parseInt(areaId)
                });
            });
        },

        /**
         * Search areas and return the result for a grid
         *
         * @param {Object} params
         * @returns {Promise}
         */
        getList: function (params) {
            params = params || {};

            return new Promise(function (resolve, reject) {
                Ajax.get('package_quiqqer_areas_ajax_list', resolve, {
                    'package': 'quiqqer/areas',
                    onError  : reject,
                    params   : JSON.encode(params)
                });
            });
        },

        /**
         * Return all unassigned countries
         *
         * @returns {Promise}
         */
        getUnAssignedCountries: function () {
            return new Promise(function (resolve, reject) {
                Ajax.get('package_quiqqer_areas_ajax_getUnAssignedCountries', resolve, {
                    'package': 'quiqqer/areas',
                    onError  : reject
                });
            });
        },

        /**
         * Create a new area
         *
         * @params {Array} [params] - area attributes
         * @returns {Promise}
         */
        createChild: function (params) {
            return new Promise(function (resolve, reject) {
                Ajax.post('package_quiqqer_areas_ajax_create', function (result) {

                    require([
                        'package/quiqqer/translator/bin/classes/Translator'
                    ], function (Translator) {
                        new Translator().refreshLocale().then(function () {
                            resolve(result);
                        });
                    });
                }, {
                    'package': 'quiqqer/areas',
                    onError  : reject,
                    params   : JSON.encode(params)
                });
            });
        },

        /**
         * Delete an area
         *
         * @param {Number} areaId - Area-ID
         * @returns {Promise}
         */
        deleteChild: function (areaId) {
            return new Promise(function (resolve, reject) {
                Ajax.post('package_quiqqer_areas_ajax_deleteChild', resolve, {
                    'package': 'quiqqer/areas',
                    onError  : reject,
                    areaId   : areaId
                });
            });
        },

        /**
         * Delete multible areas
         *
         * @param {Array} areaIds - array of Area-IDs
         * @returns {Promise}
         */
        deleteChildren: function (areaIds) {
            return new Promise(function (resolve, reject) {
                Ajax.post('package_quiqqer_areas_ajax_deleteChildren', resolve, {
                    'package': 'quiqqer/areas',
                    onError  : reject,
                    areaIds  : JSON.encode(areaIds)
                });
            });
        },

        /**
         * Save an area
         *
         * @param {Number} areaId
         * @param {Object} data - area attributes
         */
        save: function (areaId, data) {
            return new Promise(function (resolve, reject) {
                Ajax.post('package_quiqqer_areas_ajax_update', resolve, {
                    'package': 'quiqqer/areas',
                    onError  : reject,
                    areaId   : areaId,
                    params   : JSON.encode(data)
                });
            });
        }
    });
});
