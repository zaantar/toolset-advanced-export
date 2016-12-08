
var Toolset = Toolset || {};
Toolset.ExtraExport = Toolset.ExtraExport || {};


/**
 * Controller of the import page.
 *
 * Finds the relevant root element, creates a viewmodel and applies knockout bindings on it.
 *
 * @param $ jQuery instance.
 * @since 1.0
 */
jQuery(document).ready(function() {

    Toolset.ExtraExport.ExportPageController = new function($) {

        var self = this;

        const rootElementId = 'toolset_extra_export_wrap';
        const modelDataElementId = 'toolset_extra_export_model_data';

        var vm = function(preselectedSections) {
            var vm = this;

            vm.selectedSections = ko.observableArray(preselectedSections);

            vm.onExportClick = function() {

                vm.isExportInProgress(true);

                if(!isFileSaverSupported()) {
                    // todo force saving the file in wp_uploads and return a link instead
                }

                // Initiate the export request
                var exportRequest = $.post({
                    url: ajaxurl,
                    data: {
                        action: 'toolset_ee_export',
                        wpnonce: self.exportNonce,
                        selected_sections: vm.selectedSections()
                    }
                });

                var fail = function(result) {
                    // todo properly process results
                    console.log(result)
                };

                exportRequest.success(function(result) {

                    if(!_.has(result.data, 'output')) {
                        fail(result);
                        return;
                    }

                    // Convert the file content encoded as base64 string into a Blob and then download it.
                    var blob = b64toBlob(result.data.output, 'application/zip, application/octet-stream');
                    saveAs(blob, 'toolset_extra_export.zip');

                }).fail(function(result) {
                    fail(result);
                }).always(function() {
                    vm.isExportInProgress(false);
                });
            };

            vm.isExportPossible = ko.pureComputed(function() {
                return (0 < vm.selectedSections().length);
            });

            vm.isExportInProgress = ko.observable(false);

            vm.exportOutput = ko.observable();

        };


        /**
         * Thank you, http://stackoverflow.com/a/16245768/3191395.
         *
         * @param b64Data
         * @param contentType
         * @param sliceSize
         * @returns {*}
         */
        var b64toBlob = function(b64Data, contentType, sliceSize) {
            contentType = contentType || '';
            sliceSize = sliceSize || 512;

            var byteCharacters = atob(b64Data);
            var byteArrays = [];

            for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
                var slice = byteCharacters.slice(offset, offset + sliceSize);

                var byteNumbers = new Array(slice.length);
                for (var i = 0; i < slice.length; i++) {
                    byteNumbers[i] = slice.charCodeAt(i);
                }

                var byteArray = new Uint8Array(byteNumbers);

                byteArrays.push(byteArray);
            }

            var blob = new Blob(byteArrays, {type: contentType});
            return blob;
        };


        var isFileSaverSupported = function() {
            try {
                var isFileSaverSupported = !!new Blob;
            } catch (e) { }
            return isFileSaverSupported;
        };


        var getModelData = function() {
            return jQuery.parseJSON(WPV_Toolset.Utils.editor_decode64(jQuery('#' + modelDataElementId).html()));
        };

        var init = function() {

            // Retrieve and process data passed from PHP
            self.modelData = getModelData();
            self.exportNonce = self.modelData['ajax_nonce'];

            // Fire in the hole!
            ko.applyBindings(new vm(self.modelData['preselected_sections'] || []), document.getElementById(rootElementId));
        };

        init();

    }(jQuery);
});