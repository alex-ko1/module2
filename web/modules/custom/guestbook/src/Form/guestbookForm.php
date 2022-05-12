<?php

namespace Drupal\guestbook\Form;

use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

class guestbookForm extends FormBase
{

  public function getFormId(): string {
    return 'guestbook_form';
  }
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['user_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name:'),
      '#placeholder' => $this->t('Minimal 2 symbols'),
      '#required' => true,
    ];
    $form['email'] = [
      '#title' => 'Your email:',
      '#type' => 'email',
      '#required' => true,
      '#placeholder' => $this->t('A-Z, a-z, -, _.'),
    ];
    $form['phone'] = [
      '#title' => 'Your phone:',
      '#type' => 'tel',
      '#required' => true,
      '#placeholder' => $this->t('+380XXXXXXXXX'),
      '#default_value' => '+380',
    ];
    $form['feedback'] = [
      '#title' => 'Write your feedback:',
      '#type' => 'textarea',
      '#required' => true,
    ];
    $form['avatar'] =[
      '#title' => 'Add avatar:',
      '#type' => 'managed_file',
      '#name' => 'avatar',
      '#description' => $this->t('format: jpg, jpeg, png <br> max-size: 2 MB'),
      '#upload_validators' => [
        'file_validate_is_image' => array(),
        'file_validate_extensions' => array('jpg jpeg png'),
        'file_validate_size' => array(2097152)
      ],
      '#upload_location' => 'public://files',
    ];
    $form['image'] = array(
      '#type' => 'managed_file',
      '#title' => 'Add image:',
      '#name' => 'image',
      '#description' => $this->t('format: jpg, jpeg, png <br> max-size: 5 MB'),
      '#upload_validators' => [
        'file_validate_is_image' => array(),
        'file_validate_extensions' => array('jpg jpeg png'),
        'file_validate_size' => array(5242880)
      ],
      '#upload_location' => 'public://files',
      '#preview' => true,
      '#styles' => true,
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
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

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    //If the image is uploaded, save it in the database
    $database = \Drupal::database();

    $image = $form_state->getValue('image');
    if ($image) {
      $file = File::load($image[0]);
      $file->setPermanent();
      $file->save();
    }
    $avatar = $form_state->getValue('avatar');
    if ($avatar) {
      $file1 = File::load($avatar[0]);
      $file1->setPermanent();
      $file1->save();
    }

    // Database connection
    $database->insert('guestbook')
      ->fields(['name', 'email', 'phone', 'comment', 'image', 'avatar', 'timestamp'])
      ->values([
        'name' => $form_state->getValue('user_name'),
        'email' => $form_state->getValue('email'),
        'phone' => $form_state->getValue('phone'),
        'comment' => $form_state->getValue('feedback'),
        'image' => $image[0],
        'avatar' => $avatar[0],
        'timestamp' => date('m/d/Y H:i:s')
      ])
      ->execute();
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
      $response->addCommand(new MessageCommand( $user_name .', thank\'s for yours feedback! '));
    }
    \Drupal::messenger()->deleteAll();
    return $response;
  }
}

