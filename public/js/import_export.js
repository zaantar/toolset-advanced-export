
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

                var posting = $.post({
                    url: ajaxurl,
                    data: {
                        action: 'toolset_ee_export',
                        wpnonce: self.exportNonce,
                        selected_sections: vm.selectedSections()
                    }
                });

                posting.success(function(result) {

                    vm.exportOutput(result.data.output);

                }).fail(function(result) {

                    // todo properly process results
                    console.log(result)

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