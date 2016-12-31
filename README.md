# Editor.md for Drupal

- [Editor.md](https://github.com/pandao/editor.md): The open source embeddable online markdown editor (component), based on CodeMirror & jQuery & Marked.

## TODOS:

- Implement PluginManager like CKeditor
- IMCE Integration

## Notes:

- the `editor.md` js file used in this module has been modified. `settings.toolbarHandles` take priority so that plugin can hack editor.md's default behaviours easily
 
 Origin
 
 ```javascript
 if (typeof toolbarIconHandlers[name] !== "undefined") 
 {
     $.proxy(toolbarIconHandlers[name], _this)(cm);
 }
 else 
 {
     if (typeof settings.toolbarHandlers[name] !== "undefined") 
     {
         $.proxy(settings.toolbarHandlers[name], _this)(cm, icon, cursor, selection);
     }
 }
 ```
 
 Altered
 
 ```javascript
 if (typeof settings.toolbarHandlers[name] !== "undefined")
 {
     $.proxy(settings.toolbarHandlers[name], _this)(cm, icon, cursor, selection);
 }
 else 
 {
     if (typeof toolbarIconHandlers[name] !== "undefined")
     {
         $.proxy(toolbarIconHandlers[name], _this)(cm);
     }
 }
 ```