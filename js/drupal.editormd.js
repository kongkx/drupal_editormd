(function (Drupal, debounce, editormd, $) {
  'use strict';

  /**
   * @namespace
   */
  Drupal.editors.editormd = {
    /**
     * Editor attach callback.
     *
     * @param {HTMLElement} element
     *   The element to attach the editor to.
     * @param {string} format
     *   The text format for the editor.
     *ff
     * @return {bool}
     *
     */
    attach: function (element, format) {

      if (format.editorSettings.toolbarIcons == 'custom' && format.editorSettings.customToolbarIcons) {
        format.editorSettings.toolbarIcons = function() {
          var icons = JSON.parse(format.editorSettings.customToolbarIcons);
          if (format.editorSettings.imageUpload) {
            icons.push('image');
          }
          return icons;
        }
      }
      if (format.editorSettings.imageUpload) {
        format.editorSettings.toolbarHandlers = {};
        format.editorSettings.toolbarHandlers['image'] = function() {
          this.executePlugin("drupalImageDialog", "drupal-image-dialog/drupal-image-dialog");
        }
      }
      if (format.editorSettings.default_view == 'pure_markdown') {
        format.editorSettings.watch = false;
      }

      var $element = $(element).hide();
      $('<div>').attr('id', element.id + '-editormd').attr('data-editor-for', element.id).insertAfter($element);

      var instanceEditor = editormd(element.id + '-editormd', Object.assign(
          {}, format.editorSettings, { appendMarkdown: $element.val() })
      );

      $element.data('editormd', instanceEditor);
      return !!instanceEditor;

    },
    detach: function (element, format, trigger) {
      var $element = $(element);
      var editor = $element.data('editormd');
      if (trigger === 'serialize') {
        element.value = editor.getMarkdown()
      } else {
        $element.show();
        editor.editor.remove();
      }
    },
    onChange: function (element, callback) {
      var editor = $(element).data('editormd');
      if (editor) {
        editor.on('change', debounce(function () {
          var val = editor.getMarkdown();
          element.value = val;
          callback(val);
        }, 400));
      }
      return !!editor;
    }
  };

  Drupal.editormd = {
    /**
     * Variable storing the current dialog's save callback.
     *
     * @type {?function}
     */
    saveCallback: null,

    /**
     * Open a dialog for a Drupal-based plugin.
     *
     * This dynamically loads jQuery UI (if necessary) using the Drupal AJAX
     * framework, then opens a dialog at the specified Drupal path.
     *
     * @param {CKEditor} editor
     *   The CKEditor instance that is opening the dialog.
     * @param {string} url
     *   The URL that contains the contents of the dialog.
     * @param {object} existingValues
     *   Existing values that will be sent via POST to the url for the dialog
     *   contents.
     * @param {function} saveCallback
     *   A function to be called upon saving the dialog.
     * @param {object} dialogSettings
     *   An object containing settings to be passed to the jQuery UI.
     */
    openDialog: function(editor, url, existingValues, saveCallback, dialogSettings) {
      var editormdAjaxDialog = Drupal.ajax({
        dialogType: 'modal',
        url: url,
        progress: { type: 'throbber'},
        submit: {
          editor_object: existingValues
        }
      });

      editormdAjaxDialog.execute();
      Drupal.editormd.saveCallback = saveCallback;
    }
  };

  $(window).on('editor:dialogsave', function(e, values) {
    if (Drupal.editormd.saveCallback) {
      Drupal.editormd.saveCallback(values);
    }
  });

  $(window).on('dialog:afterclose', function(e, dialog, $element) {
    if (Drupal.editormd.saveCallback) {
      Drupal.editormd.saveCallback = null;
    }
  });

})(Drupal, Drupal.debounce, editormd, jQuery);
