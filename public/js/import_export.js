
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

    Toolset.ExtraExport.ExportPageViewModel = new function($) {

        var self = this;

        const rootElementId = 'toolset_extra_export_wrap';
        const modelDataElementId = 'toolset_extra_export_model_data';

        var vm = function(preselectedSections) {
            var vm = this;

            vm.selectedSections = ko.observableArray(preselectedSections);

            vm.onExportClick = function() {
                alert( 'click!' )
            };

            vm.isExportPossible = ko.pureComputed(function() {
                return (0 < vm.selectedSections().length);
            });
        };

        var getModelData = function() {
            return jQuery.parseJSON(WPV_Toolset.Utils.editor_decode64(jQuery('#' + modelDataElementId).html()));
        };

        var init = function() {

            // Retrieve and process data passed from PHP
            self.modelData = getModelData();

            // Fire in the hole!
            ko.applyBindings(new vm(self.modelData['preselected_sections'] || []), document.getElementById(rootElementId));
        };

        init();

    }(jQuery);
});