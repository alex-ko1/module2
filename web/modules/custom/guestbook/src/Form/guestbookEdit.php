<?php

namespace Drupal\guestbook\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

class guestbookEdit extends FormBase
{

  /**
   * @var mixed|null
   */
  private mixed $id;

  public function getFormId() {
    return "Guestbook_Edit";
  }

  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $this->id = $id;
    $query = \Drupal::database();
    $data = $query
      ->select('guestbook', 'g')
      ->condition('id', $id, '=')
      ->fields('g', ['name', 'email', 'image', 'id'])
      ->execute()->fetchAll();
    $form['cat_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name:'),
      '#default_value' => $data[0]->name,
      '#required' => TRUE,
    ];
    $form['email'] = [
      '#title' => 'Your email:',
      '#type' => 'email',
      '#required' => TRUE,
      '#default_value' => $data[0]->email,
    ];
    $form['image'] = [
      '#title' => 'Image',
      '#type' => 'managed_file',
      '#multiple' => FALSE,
      '#description' => t('format: jpg, jpeg, png <br> max-size: 2MB'),
      '#default_value' => [$data[0]->image],
      '#required' => TRUE,
      '#upload_location' => 'public://images/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
      ],
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Edit Cat'),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => '::setMessage',
      ],
    ];
    return $form;
  }
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    if (strlen($form_state->getValue('user_name')) < 2) {
      $form_state->setErrorByName('user_name', $this->t('Please enter a longer name.'));
    } elseif (strlen($form_state->getValue('user_name')) >100) {
      $form_state->setErrorByName('user_name', $this->t('Please enter a shorter name.'));
    }
    if (strpbrk($form_state->getValue('email'), '0123456789!#$%^&*()+=:;,`~?/<>\'±§[]{}|"')){
      $form_state->setErrorByName('email', $this->t('Please enter a valid email.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): Url {
    // TODO: Implement submitForm() method.
    $image = $form_state->getValue('image');
    $file = File::load($image[0]);
    $file->setPermanent();
    $file->save();
    $query = \Drupal::database();
    $query->update('guestbook')
      ->condition('id', $this->id)
      ->fields([
        'name' => $form_state->getValue('user_name'),
        'email' => $form_state->getValue('email'),
        'image' => $image[0],
      ])
      ->execute();
    $form_state->setRedirect('guestbook.content');
    return new Url('guestbook.content');
  }
  public function setMessage(array $form, FormStateInterface $form_state): AjaxResponse {
    $user_name = $form_state->getValue('user_name');
    $response = new AjaxResponse();
    if ($form_state->hasAnyErrors()) {
      foreach ($form_state->getErrors() as $errors_array) {
        $response->addCommand(new MessageCommand($errors_array, NULL, ['type'=>'error']));
      }
    }
    else {
      $response->addCommand(new MessageCommand('You edit ' . $user_name .'\' review ! '));
    }
    \Drupal::messenger()->deleteAll();
    return $response;
  }
}
