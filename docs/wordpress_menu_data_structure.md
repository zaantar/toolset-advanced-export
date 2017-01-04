# WordPress menu data structure

This document describes how WordPress menus are organized and, most importantly, stored. It's still a draft, 
crafted for WordPress 4.7.

Related core functions are defined in [nav-menu.php](https://developer.wordpress.org/reference/files/wp-includes/nav-menu.php/).

## Menus

Menus are stored as terms of the `nav_menu` taxonomy.

Relevant properties:

Propery name    | Meaning
----------------|--------------------
`name`          | Menu display name
`term_id`       | Menu ID
`slug`          | Menu slug

## Menu locations

Themes register their menu locations with [`register_nav_menu()`](https://developer.wordpress.org/reference/functions/register_nav_menu/).
[`get_registered_nav_menus()`](https://developer.wordpress.org/reference/functions/get_registered_nav_menus/) can be 
used to get the list of all locations.

However, for getting any relevant data, it's better to go directly for [`get_nav_menu_locations()`](https://developer.wordpress.org/reference/functions/get_nav_menu_locations/)
which is basically a wrapper for the `'nav_menu_locations'` theme_mod. It returns an associative array where
keys are location slugs and values are IDs of assigned menus.

## Menu items

Each menu item is represented by a post of the `nav_menu_item` type. It is possible to retrieve all items for one menu
via [`wp_get_nav_menu_items()`](https://developer.wordpress.org/reference/functions/wp_get_nav_menu_items/). 
It returns an array of `WP_Post` instances with additional menu-relevant postmeta.

Individual menu items can be saved via [`wp_update_nav_menu_item()`](https://developer.wordpress.org/reference/functions/wp_update_nav_menu_item/),
where `$menu_id` needs to point to an existing menu and `$menu_item_db_id` is either an ID of the underlying `nav_menu_item`
post or `"0"`, which will cause a new post to be created. `$menu_item_data` contains the menu properties but it's an associative
array with keys different than `nav_menu_item` postmeta.

Please note that although there's no explicit warning in the Codex, both of these functions are probably not intended 
for outside-core usage.

WP_Post property*                      | `$menu_item_data` key | Type      | Meaning 
--------------------------------------|-----------------------|-----------|----------------------------------------------- 
`db_id`, `ID`*                        | menu-item-db-id       | numeric   | ID of the menu item (`nav_menu_item` post) 
`post_name`*                          | ?                     | string    | Apparently, same as `ID`. Not sure if this fact is true or relevant. 
`post_status`*                        | menu-item-status      | string    | `'publish'` or `'draft'`. Draft items will not be displayed.
`type`                                | menu-item-type        | string    | Type of the menu item, `'custom'`: custom link, `'taxonomy'`: taxonomy archive, `'post_type'`: single post, `'post_type_archive'`: post type archive; doesn't seem to be supported by the GUI but there are plugins that offer this.
`type_label`                          | ?                     | string    | Display label for the type of the item. Probably not relevant.
`menu_order`                          | menu-item-position    | numeric   | Order of the menu item (relative to its parent).
`menu_item_parent`, `post_parent`*    | menu-item-parent-id   | numeric   | ID of the parent menu item or `0` for a root item.
`object_id`                           | menu-item-object-id   | numeric   | ID of the object the menu item points to. Depends on `type`, `'custom'`: identical to db_id (and ignored by `wp_update_nav_menu_item()`), `'taxonomy'`: ID of the taxonomy _term_, `'post_type'`: ID of the post, `'post_type_archive'`: ignored.
`object`                              | menu-item-object      | string    | Another reference to the object the menu item points to. Depends on `type`, `'custom'`: the string `'custom'` (ignored by `wp_update_nav_menu_item()`), `'taxonomy'`: taxonomy slug, `'post_type'`: post type slug, `'post_type_archive'`: post type slug.
`url`                                 | menu-item-url         | string    | URL to the menu item target. For custom links, it's the actual link, for taxonomy it's the link to the archive, for post it's a link to the post. Non-custom URLs are ignored by `wp_update_nav_menu_item()`
`title`, `post_title`*                | menu-item-title       | string    | Title (display label) of the menu item.
`target`                              | menu-item-target      | string    | The `target` attribute of the rendered link. WordPress GUI supports `'_blank'` or `''`. 
`attr_title`, `post_excerpt`*         | menu-item-attr-title  | string    | The `title` attribute of the rendered link.
`classes`                             | menu-item-classes     | array     | CSS classes for the menu item.
`xfn`                                 | menu-item-xfn         | string    | The [XHTML Friends Network definition](https://codex.wordpress.org/Defining_Relationships_with_XFN).
`post_content`*                       | menu-item-description | string    | Custom description of the menu item. 

Properties marked with an asterisk represent post fields, the rest is postmeta. 

I suggest not to rely only on the post fields. Further research of [`wp_setup_nav_menu_item()`](https://core.trac.wordpress.org/browser/tags/4.7/src/wp-includes/nav-menu.php#L824)
indicates that under some circumstances the menu items might be term objects instead of posts.