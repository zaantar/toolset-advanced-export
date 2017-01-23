
//noinspection JSUnusedAssignment
var Toolset = Toolset || {};
Toolset.AdvancedExport = Toolset.AdvancedExport || {};


/**
 * Controller of the import page.
 *
 * Finds the relevant root element, creates a viewmodel and applies knockout bindings on it.
 *
 * @param $ jQuery instance.
 * @since 1.0
 */
jQuery(document).ready(function() {

    Toolset.AdvancedExport.ExportPageController = new function($) {

        var self = this;

        const rootElementSelector = '.toolset_advanced_export_wrap';
        const modelDataElementId = 'toolset_advanced_export_model_data';
        const importFileElementId = 'toolset_advanced_export_import_file';

        var vm = function(preselectedSections) {
            var vm = this;

            vm.selectedSections = ko.observableArray(preselectedSections);

            vm.onExportClick = function() {

                vm.isExportInProgress(true);
                vm.downloadLink('');
                vm.exportErrorMessage('');

                var handleExportFailure = function(result) {
                    if(_.has(result, 'data') && _.has(result.data, 'message')) {
                        vm.exportErrorMessage(result.data.message);
                    } else {
                        vm.exportErrorMessage(self.l10n['unknown_error']);
                    }
                    console.log(result)
                };

                // Initiate the export request
                var exportRequest = $.post({
                    url: ajaxurl,
                    data: {
                        action: 'toolset_advanced_export_do_export',
                        wpnonce: self.importExportNonce,
                        selected_sections: vm.selectedSections(),
                        export_method: (isFileSaverSupported() ? 'saveas' : 'link')
                    },
                    error: handleExportFailure
                });

                exportRequest.success(function(result) {

                    if(!_.has(result.data, 'output')) {
                        handleExportFailure(result);
                        return;
                    }

                    if(isFileSaverSupported()) {
                        // Convert the file content encoded as base64 string into a Blob and then download it.
                        var blob = b64toBlob(result.data.output, 'application/zip, application/octet-stream');
                        //noinspection JSUnresolvedVariable
                        var fileName = (_.has(result.data, 'fileName') ? result.data.fileName : 'toolset_advanced_export.zip');
                        saveAs(blob, fileName);
                    }

                    if(_.has(result.data, 'link')) {
                        vm.downloadLink(result.data.link);
                    }

                });

                exportRequest.always(function() {
                    vm.isExportInProgress(false);
                });
            };

            vm.isExportPossible = ko.pureComputed(function() {
                return (0 < vm.selectedSections().length);
            });

            vm.isExportInProgress = ko.observable(false);

            vm.downloadLink = ko.observable('');

            vm.exportErrorMessage = ko.observable('');

            /** Values representing confirmation checkboxes before import. */
            vm.importRequirements = ko.observableArray();

            /** All three checkboxes must be checked and a file selected. */
            vm.isImportPossible = ko.pureComputed(function() {
                return (vm.importRequirements().length == 3 && vm.importFileName().length > 0 );
            });

            vm.importErrorMessage = ko.observable('');

            vm.isImportInProgress = ko.observable(false);

            vm.isImportButtonEnabled = ko.pureComputed(function() {
                return ( vm.isImportPossible() && ! vm.isImportInProgress() );
            });

            vm.importOutput = ko.observable();

            vm.importFileName = ko.observable('');

            vm.importFileData = ko.observable();

            /**
             * Deal with any sort of AJAX call failure on import.
             */
            var handleImportFailure = function(result) {
                if(_.has(result, 'data') && _.has(result.data, 'message')) {
                    vm.importErrorMessage(result.data.message);
                } else {
                    vm.importErrorMessage(self.l10n['unknown_error']);
                }
                vm.isImportInProgress(false);
                vm.importOutput('');
            };


            /**
             * Import a file that is already uploaded as a WordPress attachment.
             *
             * @param attachmentId
             */
            var makeImportCall = function(attachmentId) {
                $.post({
                    url: ajaxurl,
                    data: {
                        action: 'toolset_advanced_export_do_import',
                        wpnonce: self.importExportNonce,
                        attachment_id: attachmentId
                    },
                    success: function(response) {
                        if(_.has(response, 'success') && response.success) {
                            vm.isImportInProgress(false);
                            vm.importOutput(response.data.message);
                        } else {
                            handleImportFailure(response);
                        }
                    },
                    error: handleImportFailure,
                    statusCode: {
                        502: handleImportFailure
                    }
                });
            };


            /**
             * Upload a file via WordPress media async upload mechanism.
             *
             * @see https://www.sitepoint.com/enabling-ajax-file-uploads-in-your-wordpress-plugin/
             *
             * @param fileData Raw file data
             * @param successCalback Function that will be called when the upload is successfull.
             *     It will get an attachment ID as a first parameter.
             */
            var uploadFile = function(fileData, successCalback) {
                var formData = new FormData();
                formData.append('action', 'upload-attachment');
                formData.append('async-upload', fileData);
                formData.append('name', vm.importFileName());
                formData.append('_wpnonce', self.uploadNonce);

                var uploadRequest = $.ajax({
                    url: self.uploadUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    type: 'POST',
                    error: handleImportFailure
                });

                uploadRequest.success(function(response) {
                    if(response.success) {
                        vm.importOutput(self.l10n['processing_import_file']);
                        successCalback(response.data.id);
                    } else {
                        handleImportFailure(response);
                    }
                });
            };


            /**
             * Perform the import.
             *
             * First, upload the import file and then make another call to import its contents.
             */
            vm.onImportClick = function() {

                vm.isImportInProgress(true);
                vm.importErrorMessage('');
                vm.importOutput(self.l10n['uploading_import_file']);

                var fileData = document.getElementById(importFileElementId).files[0];

                uploadFile(fileData, makeImportCall);
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


        var setupKnockout = function() {

            var enablePrimary = function(element, valueAccessor) {
                var isEnabled = ko.unwrap(valueAccessor());
                if(isEnabled) {
                    $(element).prop('disabled', false).addClass('button-primary');
                } else {
                    $(element).prop('disabled', true).removeClass('button-primary');
                }
            };

            /**
             * Disable primary button and update its class.
             *
             * @since 2.0
             */
            ko.bindingHandlers.enablePrimary = {
                init: enablePrimary,
                update: enablePrimary
            };

        };


        var init = function() {

            setupKnockout();

            // Retrieve and process data passed from PHP
            self.modelData = getModelData();
            self.importExportNonce = self.modelData['ajax_nonce'];
            self.uploadNonce = self.modelData['upload_nonce'];
            self.uploadUrl = self.modelData['upload_url'];
            self.l10n = self.modelData['l10n'];

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