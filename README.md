# Toolset Extra Export

A WordPress plugin that exports and imports additional WordPress settings that are not included in the standard WordPress export file.

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

 - the same theme is active on export and on import.
 - posts, taxonomies and terms referenced in menus or settings are already imported, 
 and that post GUIDs haven't been changed.
 
If this is not the case, the import may turn out to be incomplete or downright broken.