/*!
 * Image (upload) dialog plugin for Editor.md
 *
 * @file        image-dialog.js
 * @author      pandao
 * @version     1.3.4
 * @updateTime  2015-06-09
 * {@link       https://github.com/pandao/editor.md}
 * @license     MIT
 */

(function() {

    var factory = function (exports) {

		var pluginName   = "drupal-image-dialog";

		exports.fn.drupalImageDialog = function() {

            var _this       = this;
            var cm          = this.cm;
            var lang        = this.lang;
            var editor      = this.editor;
            var settings    = this.settings;
            var cursor      = cm.getCursor();
            var selection   = cm.getSelection();
            var imageLang   = lang.dialog.image;
            var classPrefix = this.classPrefix;
            var iframeName  = classPrefix + "image-iframe";
            var dialogName  = classPrefix + pluginName, dialog;

            cm.focus();
                var saveCallback = function(data) {
                var alt = data.attributes.alt;
                var url = data.attributes.src;
                this.cm.replaceSelection("![" + alt + "](" + url + ")");
                this.lockScreen(false);
            }.bind(_this);

            this.lockScreen();
            Drupal.editormd.openDialog(
                _this,
                Drupal.url('editor/dialog/image/' + _this.settings.drupal.format),
                null,
                saveCallback,
                {
                    dialogClass: "editor-image-dialog",
                    title: "Insert Image"
                }
            );
		};

	};

	// CommonJS/Node.js
	if (typeof require === "function" && typeof exports === "object" && typeof module === "object")
    {
        module.exports = factory;
    }
	else if (typeof define === "function")  // AMD/CMD/Sea.js
    {
		if (define.amd) { // for Require.js

			define(["editormd"], function(editormd) {
                factory(editormd);
            });

		} else { // for Sea.js
			define(function(require) {
                var editormd = require("./../../editormd");
                factory(editormd);
            });
		}
	}
	else
	{
        factory(window.editormd);
	}

})();
