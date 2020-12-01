<?php

namespace Drupal\editormd\Plugin\Editor;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\RendererInterface;
use Drupal\editor\Plugin\EditorBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a Editor.md-based text editor for Drupal.
 *
 * @Editor(
 *  id = "editormd",
 *  label = @Translation("editor.md"),
 *  supports_content_filtering = TRUE,
 *  supports_inline_editing = FALSE,
 *  is_xss_safe = FALSE,
 *  supported_element_types = {
 *    "textarea"
 *  }
 * )
 */
class Editormd extends EditorBase implements ContainerFactoryPluginInterface {

  /**
   * The module handler to invoke hooks on.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;


  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke hooks on.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, LanguageManagerInterface $language_manager, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // $this->editormdPluginManager = $editormd_plugin_manager;
    $this->moduleHandler = $module_handler;
    $this->languageManager = $language_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
   public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
     return new static(
       $configuration,
       $plugin_id,
       $plugin_definition,
       $container->get('module_handler'),
       $container->get('language_manager'),
       $container->get('renderer')
     );
   }

   /**
    * {@inheritdoc}
    */
  public function getDefaultSettings() {
    return array(
      'path' => '/'. drupal_get_path('module', 'editormd') . '/editormd/lib/',
      'toolbarIcons' => 'simple',
      'customToolbarIcons' => "",
      'width' => '100%',
      'height' => '300px',
      'default_view' => 'pure_markdown'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    $settings = $editor->getSettings();

    // $editormd_settings_toolbar = array(
    //   '#theme' => 'editormd_settings_toolbar',
    //   '#editor' => $editor,
    //   '#plugins' => $this->editormdPluginManager->getButtons(),
    // );

    $form['toolbarIcons'] = [
      '#type' => 'select',
      '#title' => $this->t('Select toolbarIcons'),
      '#options' => [
        'full' => $this->t('Full'),
        'simple' => $this->t('Simple'),
        'mini' => $this->t('Mini'),
        'custom' => $this->t('Custom')
      ],
      '#default_value' => $settings['toolbarIcons']
    ];

    $form['customToolbarIcons'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom Toolbar JSON'),
      '#description' => $this->t('Example: <code>["h1","h2","h3","h4","h5","h6","|","bold","del","italic","quote","|","list-ul","list-ol","hr","|","watch","preview","fullscreen","|","info"]</code>'),
      '#size' => 60,
      '#maxlength' => 256,
      '#default_value' => $settings['customToolbarIcons'],
      '#state' => array(
        'visible' => array(
          ':input[name="toolbarIcons"]' => array('value' => 'custom'),
        )
      )
    ];

    $form['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#default_value' => $settings['width'],
      '#size' => 60,
      '#maxlength' => 128,
    ];

    $form['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#default_value' => $settings['height'],
      '#size' => 60,
      '#maxlength' => 128,
    ];

    $form['default_view'] = [
      '#type' => 'select',
      '#title' => $this->t('Default View'),
      '#default_value' => $settings['default_view'],
      '#options' => [
        'watch_enabled' => $this->t('Watch Enabled'),
        'pure_markdown' => $this->t('Pure Markdown'),
      ],
    ];

    $form['plugin_settings'] = array(
      '#type' => 'vertical_tabs',
      '#title' => t('Editor.md plugin settings'),
      '#attributes' => array(
        'id' => 'editormd-plugin-settings',
      )
    );

    // simple code for drupal image plugin
    $form['plugins'] = array(
      'drupalimage' => array(
        '#type' => 'details',
        '#title' => t('Enalbe Image upload'),
        '#open' => true,
        '#group' => 'plugin_settings',
        '#attributes' => array(
          'data-editormd-plugin-id' => 'drupalImage',
        )
      )
    );

    $form_state->loadInclude('editor', 'admin.inc');
    $form['plugins']['drupalimage']['image_upload'] = editor_image_upload_settings_form($editor);


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsFormSubmit(array $form, FormStateInterface $form_state) {
    // save image upload settings
    $image_settings = &$form_state->getValue(array('editor', 'settings', 'plugins', 'drupalimage', 'image_upload'));

    if ($image_settings['status'] === 1) {
      $form_state->setValue(array('editor', 'settings', 'imageUpload'), true);
    }

    $form_state->get('editor')->setImageUploadSettings($image_settings);
    $form_state->unsetValue(array('editor', 'settings', 'plugins', 'drupalimage'));

    // Remove the plugin settings' vertical tabs state; no need to save that.
    if ($form_state->hasValue(array('editor', 'settings', 'plugins'))) {
      $form_state->unsetValue(array('editor', 'settings', 'plugin_settings'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getJSSettings(Editor $editor) {
    $settings = $editor->getSettings();

    $settings += array(
      'drupal' => array(
        'format' => $editor->id(),
      )
    );

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    $libraries = array(
      'editormd/editormd',
      'editormd/drupal.editormd',
    );

    return $libraries;
  }


}
