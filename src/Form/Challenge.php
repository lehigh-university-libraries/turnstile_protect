<?php

namespace Drupal\turnstile_protect\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class Challenge extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'turnstile_protect_challenge_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['turnstile'] = [
      '#type' => 'captcha',
      '#captcha_type' => 'turnstile/Turnstile',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#attributes' => [
        'class' => ['d-none']
      ]
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // 10d expiration
    // TODO - add as config
    setcookie('turnstile_protect_pass', 1, 864000);

    $destination = \Drupal::request()->query->get('destination');
    if (!$destination) {
      $destination = '/';
    }
    $form_state->setRedirectUrl(Url::fromUserInput($destination));
  }
}
