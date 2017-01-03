
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

        const rootElementSelector = '.toolset_extra_export_wrap';
        const modelDataElementId = 'toolset_extra_export_model_data';
        const importFileElementId = 'toolset_extra_export_import_file';

        var vm = function(preselectedSections) {
            var vm = this;

            vm.selectedSections = ko.observableArray(preselectedSections);

            vm.onExportClick = function() {

                vm.isExportInProgress(true);
                vm.downloadLink('');
                vm.exportErrorMessage('');

                // Initiate the export request
                var exportRequest = $.post({
                    url: ajaxurl,
                    data: {
                        action: 'toolset_ee_export',
                        wpnonce: self.importExportNonce,
                        selected_sections: vm.selectedSections(),
                        export_method: (isFileSaverSupported() ? 'saveas' : 'link')
                    }
                });

                var fail = function(result) {
                    if(_.has(result, 'data') && _.has(result.data, 'message')) {
                        vm.exportErrorMessage(result.data.message);
                    } else {
                        vm.exportErrorMessage('An unknown error has happened.');
                    }
                    console.log(result)
                };

                exportRequest.success(function(result) {

                    if(!_.has(result.data, 'output')) {
                        fail(result);
                        return;
                    }

                    if(isFileSaverSupported()) {
                        // Convert the file content encoded as base64 string into a Blob and then download it.
                        var blob = b64toBlob(result.data.output, 'application/zip, application/octet-stream');
                        saveAs(blob, 'toolset_extra_export.zip');
                    }

                    if(_.has(result.data, 'link')) {
                        vm.downloadLink(result.data.link);
                    }

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

            vm.downloadLink = ko.observable('');

            vm.exportErrorMessage = ko.observable('');

            vm.importRequirements = ko.observableArray();

            vm.isImportPossible = ko.pureComputed(function() {
                return (vm.importRequirements().length == 3 && vm.importFileName().length > 0 );
            });

            vm.importErrorMessage = ko.observable('');

            vm.isImportInProgress = ko.observable(false);

            vm.importOutput = ko.observable();

            vm.importFileName = ko.observable('');

            vm.importFileData = ko.observable();

            vm.onImportClick = function() {

                vm.isImportInProgress(true);
                vm.importOutput('Uploading the import file...');

                var fail = function(result) {
                    if(_.has(result, 'data') && _.has(result.data, 'message')) {
                        vm.importErrorMessage(result.data.message);
                    } else {
                        vm.importErrorMessage('An unknown error has happened.');
                    }
                    console.log(result);
                    vm.isImportInProgress(false);
                };

                var fileData = document.getElementById(importFileElementId).files[0];

                var formData = new FormData();
                formData.append('action', 'upload-attachment');
                formData.append('async-upload', fileData);
                formData.append('name', vm.importFileName());
                formData.append('_wpnonce', self.uploadNonce);

                $.ajax({
                    url: self.uploadUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    type: 'POST',
                    success: function(response) {
                        if(response.success) {
                            vm.importOutput('Processing the import file...');

                            return;

                            // todo :
                            $.post({
                                url: ajaxurl,
                                data: {
                                    action: 'toolset_ee_import',
                                    wpnonce: self.importExportNonce,
                                    import_file_url: response.data.url
                                }
                            })
                        } else {
                            fail(response);
                        }
                    },
                    fail: fail
                })
            }
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
            self.importExportNonce = self.modelData['ajax_nonce'];
            self.uploadNonce = self.modelData['upload_nonce'];
            self.uploadUrl = self.modelData['upload_url'];

            // Fire in the hole!
            //
            // We may have multiple roots because on the Toolset Export / Import page, the import and
            // export sections are rendered individually, reusing the same Twig template. This is required by
            // the page GUI coming from Toolset.
            var viewModel = new vm(self.modelData['preselected_sections'] || []);
            $(rootElementSelector).each(function(index, rootElement) {
                ko.applyBindings(viewModel, rootElement);
            });

        };

        init();

    }(jQuery);
});