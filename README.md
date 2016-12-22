# Toolset Extra Export

Made primarily as a support plugin for 
[Toolset-based themes](https://wp-types.com/2016/12/sneak-preview-toolset-based-themes/).

This plugin allows you to export various settings which are not covered by the 
WordPress XML export. The purpose of exporting and importing these settings is to 
allow users of your Toolset-based theme to receive an exact copy of your site 
without having to set options manually.

Currently, there is a GUI for export and an API for both import and export.

## Usage

For generating an export file, 

1. Make sure your site meets all the requirements described below.
2. Download, install and activate this plugin.
3. Go to `Toolset` &rarr; `Export / Import` in the WordPress admin menu
 and select the `Theme (TBT)` tab.
4. Choose what do you want to export and click on the `Export` button.
5. The export file will be downloaded (or, for very old browsers, you 
 will see a download link).

## API

This plugin offers a filter hook API for export and import.

It is possible to export or import only certain section of settings:

- `settings_reading`: Reading settings _(Settings &rarr; Reading)_
- `appearance_customize`: Customizer setup _(Appearance &rarr; Customize)_
- `appearance_menu`: Menu setup _(Appearance &rarr; Menus)_
- `appearance_widgets`: Widget setup _(Appearance &rarr; Widgets)_

### `toolset_export_extra_wordpress_data`

The hook exports selected WordPress sections and returns them as one (nested) associative array.

```
@param null
@param string[] $sections_to_export Names of the sections to export.
@return array Exported data, one element for each section, indexed by section name.
```

#### Example

```php
try{
    $export_data = apply_filters( 
        'toolset_export_extra_wordpress_data', 
        null, 
        ['appearance_widgets', 'settings_reading'] 
    );
} catch( \Exception $e ) {
    // TODO handle errors
}
```

#### Note

There are other similar filters available:

- `toolset_export_extra_wordpress_data_json`: Export the data as a JSON string.
- `toolset_export_extra_wordpress_data_raw`: Only for internal usage within Toolset.

### `toolset_import_extra_wordpress_data`

The hook imports selected WordPress sections provided as a (nested) associative array. 
This is the import counterpart of `toolset_export_extra_wordpress_data`.

```
@param null
@param string[] $sections_to_import Names of the sections to import.
@param array $import_data Associative array with section data (as arrays), with section names as keys.
@return \Toolset_Result_Set Operation results.
```

#### Example
 
```php
try{
    $results = apply_filters( 
        'toolset_import_extra_wordpress_data', 
        null, 
        ['appearance_menu'], 
        $data 
    );
} catch( \Exception $e ) {
    // TODO handle errors
}
```

## Requirements and limitations

### PHP

This plugin requires PHP 5.6 or above.

### WordPress

Tested with WordPress 4.7.

### Toolset

Currently, it runs only with development version of Toolset because of several [dependencies](docs/toolset_dependencies.md).

One thing that needs to be handled if the plugin is to be used in a standalone 
mode is the rewriting of imported URLs (for example, to header images in 
Customizer settings) and importing the actual files.

### Browsers

Recommended (but not required) browsers:  

- Firefox 20+
- Chrome
- Edge
- IE 10+
- Opera 15+
- Safari 6.1+

### Import limitations

It is required and assumed that 

 - the same theme is active on export and on import,
 - the same plugins that register active widgets are active on export and on import,
 - posts, taxonomies and terms referenced in menus or settings are already imported, 
 and that post GUIDs haven't been changed.
 
If this is not the case, the import may turn out to be incomplete or downright broken.

## Credits

The relevant part was, with the deepest gratitude to its authors, taken from the 
[Widget Importer && Exporter](https://wordpress.org/plugins/widget-importer-exporter/) 
plugin with only very little changes.

Made with :heart: for [Toolset](http://toolset.com) and [OnTheGoSystems](http://onthegosystems.com).