# Toolset dependencies

Currently, this plugin has several dependencies on Toolset. 
When/if it's turned into a completely standalone solution, this needs to be
handled properly.

## Existing dependencies

- `Toolset_Result` and `Toolset_Result_Set` are being used throughout the code.
- The `toolset-utils` script is needed for `WPV_Toolset.Utils.editor_decode64()`. 

## Solved dependencies

- `toolset_getarr()`, `toolset_ensarr()` and `toolset_getnest()` have an alternative 
  definition in `functions.php`.
- Several dependencies when extending the Toolset Export / Import page. 
  Not a problem since this page doesn't show without Toolset.
