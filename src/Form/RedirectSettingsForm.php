<?php

/**
 * @file
 * Contains \Drupal\redirect\Form\RedirectSettingsForm
 */

namespace Drupal\redirect\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class RedirectSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'redirect_settings_form';
  }
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['redirect.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('redirect.settings');
    $form['redirect_auto_redirect'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically create redirects when URL aliases are changed.'),
      '#default_value' => $config->get('auto_redirect'),
      '#disabled' => !\Drupal::moduleHandler()->moduleExists('path'),
    );
    $form['redirect_passthrough_querystring'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Retain query string through redirect.'),
      '#default_value' => $config->get('passthrough_querystring'),
      '#description' => $this->t('For example, given a redirect from %source to %redirect, if a user visits %sourcequery they would be redirected to %redirectquery. The query strings in the redirection will always take precedence over the current query string.', array('%source' => 'source-path', '%redirect' => 'node?a=apples', '%sourcequery' => 'source-path?a=alligators&b=bananas', '%redirectquery' => 'node?a=apples&b=bananas')),
    );
    $form['redirect_warning'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Display a warning message to users when they are redirected.'),
      '#default_value' => $config->get('warning'),
      '#access' => FALSE,
    );
    $form['redirect_default_status_code'] = array(
      '#type' => 'select',
      '#title' => $this->t('Default redirect status'),
      '#description' => $this->t('You can find more information about HTTP redirect status codes at <a href="@status-codes">@status-codes</a>.', array('@status-codes' => 'http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection')),
      '#options' => redirect_status_code_options(),
      '#default_value' => $config->get('default_status_code'),
    );
    $form['globals'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Global redirects'),
      '#description' => $this->t('(formerly Global Redirect features)'),
    );
    $form['globals']['redirect_global_home'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Redirect from paths like index.php and /node to the root directory.'),
      '#default_value' => $config->get('global_home'),
    );
    $form['globals']['redirect_global_clean'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Redirect from non-clean URLs to clean URLs.'),
      '#default_value' => $config->get('global_clean'),
      // @todo - does still apply? See https://drupal.org/node/1659580
      //'#disabled' => !variable_get('clean_url', 0),
    );
    $form['globals']['redirect_global_canonical'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Redirect from non-canonical URLs to the canonical URLs.'),
      '#default_value' => $config->get('global_canonical'),
    );
    $form['globals']['redirect_global_deslash'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Remove trailing slashes from paths.'),
      '#default_value' => $config->get('global_deslash'),
    );
    $form['globals']['redirect_global_admin_paths'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Allow redirections on admin paths.'),
      '#default_value' => $config->get('global_admin_paths'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('redirect.settings');
    foreach ($form_state->getValues() as $key => $value) {
      if (strpos($key, 'redirect_') !== FALSE) {
        $config->set(str_replace('redirect_', '', $key), $value);
      }
    }
    $config->save();
    redirect_page_cache_clear();
    drupal_set_message(t('Configuration was saved.'));
  }

}
