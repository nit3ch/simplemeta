<?php

/**
 * @file
 * Contains \Drupal\demo\Form\DemoForm.
 */

namespace Drupal\simplemeta\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Component\Utility\UrlHelper;

class SimplemetaForm extends FormBase {
  
  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'simplemeta_meta_form';
  }
  
  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Let's use _ as prefix to not conflict with other elements.
  $form['_meta'] = array(
    '#type' => 'value',
    '#value' => $meta,
  );

  if (!isset($meta->path)) {
    $form['_path'] = array(
      '#type' => 'textfield',
      '#title' => t('Path'),
      '#description' => t('% may be used as placeholder for system pathes, for example, news/archive/%'),
      '#required' => TRUE,
    );
  }
  else {
    $form['_path'] = array(
      '#type' => 'value',
      '#value' => $meta->path,
    );
  }
  if (\Drupal::config()->get('language', FALSE)) {

    $form['_language'] = array(
      '#type' => 'select',
      '#title' => t('Language'),
      '#options' => _simplemeta_langauge_list(),
      '#default_value' => $meta->language,
    );
    // Do not allow change language for existing meta.
    if (isset($meta->sid)) {
      $form['_language']['#disabled'] = TRUE;
      $form['_language']['#value'] = $meta->language;
    }
  }
  else {
    $form['_language'] = array(
      '#type' => 'value',
      '#value' => $meta->language,
    );
  }

  $form += simplemeta_get_form_elements($meta);

  $form['_buttons'] = array(
    '#type' => 'actions',
  );
  $form['_buttons']['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#attributes' => array('class' => array('button-save')),
  );
  if (!empty($meta->sid)) {
    $form['_buttons']['delete'] = array(
      '#type' => 'link',
      '#title' => t('Delete'),
      '#href' => 'admin/content/simplemeta/' . $meta->sid . '/delete',
    );
  }
  return $form;
  }

/**
 * {@inheritdoc}
 */
public function validateForm(array &$form, FormStateInterface $form_state) {
  $meta = $form_state->getValues()['_meta'];
  $path = $form_state->getValues()['_path'];
  $lang = $form_state->get()['_language'];
  //$system_path = \Drupal::service('path.alias_manager')->getPathByAlias($path_alias, $langcode);
  $normal_path = \Drupal::service('path.alias_manager')->getPathByAlias($path, 'en');
  if ($path != $normal_path) {
    $path = $normal_path;
  }
  if (!UrlHelper::isExternal($path)) {
    $parsed_link = parse_url($path);
    if ($path != $parsed_link['path']) {
      $path = $parsed_link['path'];
    }
    // @todo do we need to check the access?
    // @see menu_edit_item_validate()
    if (!trim($path)) {
      $form_state->setErrorByName('_path', t('Path is invalid'));
    }
    $form_state->set(['values','_path'],$path);
  }
  else {
    $form_state->setErrorByName('_path', t('Path can be only internal'));
  }

  if (isset($meta->sid) && (!simplemeta_meta_load($meta->sid))) {
    $form_state->setErrorByName('_meta', t("Meta #%sid doesn't exist anymore", array('%id' => $meta->sid)));
  }
  elseif ((!isset($meta->sid)) && simplemeta_meta_load_by_path($path, $lang)) {
    $form_state->setErrorByName('_meta', t('Meta for this page in this language already exists'));
  }

  $info = simplemeta_get_info();
  foreach ($info as $key => $definition) {
    if (isset($definition['validate']) && function_exists($definition['validate'])) {
      $function = $definition['validate'];
      $function($form, $form_state);
    }
  }
}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  $values = $form_state->getValues();
  $meta = $values['_meta'];
  $meta->path = $values['_path'];
  $meta->language = $values['_language'];
  $meta->data = array_intersect_key($values, simplemeta_get_form_elements());
  $info = simplemeta_get_info();
  foreach ($info as $key => $definition) {
    if (isset($definition['submit']) && function_exists($definition['submit'])) {
      $function = $definition['submit'];
      // @todo should we pass the $form? Think about
      $function($meta, $form_state);
    }
  }
  simplemeta_meta_save($meta);
 // cache_clear_all('*', 'cache_simplemeta', TRUE);
  drupal_set_message(t('Meta has been saved'));
  $form_state->setRedirect('simplemeta.add');
  //$form_state['redirect'] = 'admin/content/simplemeta/list';
  }
}