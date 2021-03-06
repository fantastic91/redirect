<?php

/**
 * @file
 * Contains Drupal\redirect\Plugin\views\field\FieldRedirectSource.
 */

namespace Drupal\redirect\Plugin\views\field;

use Drupal\Component\Utility\String;
use Drupal\views\Plugin\views\field\FieldPluginBase;

class FieldRedirectSource extends FieldPluginBase {
  function construct() {
    parent::construct();
    $this->additional_fields['source'] = 'source';
    $this->additional_fields['source_options'] = 'source_options';
  }

  function option_definition() {
    $options = parent::option_definition();
    $options['text'] = array('default' => '', 'translatable' => TRUE);
    $options['absolute'] = array('default' => FALSE);
    $options['alter']['contains']['make_link']['default'] = TRUE;
    return $options;
  }

  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    // This field will never be empty
    $form['empty']['#access'] = FALSE;
    $form['empty_zero']['#access'] = FALSE;
    $form['hide_empty']['#access'] = FALSE;

    $form['alter']['make_link']['#description'] = t('If checked, this field will be made into a link.');
    $form['alter']['absolute']['#access'] = FALSE;
    $form['alter']['path']['#access'] = FALSE;

    $form['text'] = array(
      '#type' => 'textfield',
      '#title' => t('Text to display'),
      '#default_value' => $this->options['text'],
    );
    $form['absolute'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use absolute link (begins with "http://")'),
      '#default_value' => $this->options['absolute'],
      '#description' => t('If you want to use this as in "output this field as link" in "link path", you have to enabled this option.'),
    );
  }

  function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  function render($values) {
    $source = $values->{$this->aliases['source']};
    $source_options = unserialize($values->{$this->aliases['source_options']});
    $source_options['absolute'] = !empty($this->options['absolute']);

    $url = redirect_url($source, $source_options);
    $text = !empty($this->options['text']) ? $this->options['text'] : $url;

    if (!empty($this->options['alter']['make_link'])) {
      $this->options['alter']['path'] = $url;
      $this->options['alter']['absolute'] = $source_options['absolute'];
    }
    else {
      $text = String::checkPlain($text);
    }

    return $text;
  }
}
