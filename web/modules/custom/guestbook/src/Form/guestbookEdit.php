<?php

namespace Drupal\guestbook\Form;

use Drupal;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

class guestbookEdit extends FormBase
{

  protected $id;

  public function getFormId(): string {
    return "Guestbook_Edit";
  }

  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL): array {
    $this->id = $id;
    $query = Drupal::database();
    $data = $query
      ->select('guestbook', 'g')
      ->condition('id', $id)
      ->fields('g', ['name', 'email','phone','comment','avatar', 'image', 'id'])
      ->execute()->fetchAll();
    $image=[];
    $avatar=[];
    if ($data[0]->image){
      $image=[$data[0]->image];
    }
    if ($data[0]->avatar){
      $avatar=[$data[0]->avatar];
    }
    $form['user_name'] = [
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
    $form['phone'] = [
      '#title' => 'Your phone:',
      '#type' => 'tel',
      '#required' => true,
      '#placeholder' => $this->t('+380000000000'),
      '#default_value' => $data[0]->phone,

    ];
    $form['feedback'] = [
      '#title' => 'Write your feedback:',
      '#type' => 'textarea',
      '#required' => true,
      '#default_value' => $data[0]->comment,
    ];
    $form['avatar'] =[
      '#title' => 'Add avatar:',
      '#type' => 'managed_file',
      '#name' => 'avatar',
      '#description' => $this->t('Format: jpg, jpeg, png; Max-size: 2 MB'),
      '#default_value' => $avatar,
      '#upload_validators' => [
        'file_validate_is_image' => array(),
        'file_validate_extensions' => array('jpg jpeg png'),
        'file_validate_size' => array(2097152)
      ],
      '#upload_location' => 'public://files',
    ];
    $form['image'] = [
      '#title' => 'Image:',
      '#type' => 'managed_file',
      '#description' => t('Format: jpg, jpeg, png; Max-size: 5MB'),
      '#default_value' => $image,
      '#upload_location' => 'public://files',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [5242880],
      ],
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Edit review'),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => '::setMessage',
      ],
    ];
    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * Validate the changed username and email.
   */
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
   * Submit form with changed user feedback.
   */
  public function submitForm(array &$form, FormStateInterface $form_state): Url {
    // TODO: Implement submitForm() method.
    $image = $form_state->getValue('image');
    $file = File::load($image[0]);
    $file->setPermanent();
    $file->save();
    $avatar = $form_state->getValue('avatar');
    $file2 = File::load($avatar[0]);
    $file2->setPermanent();
    $file2->save();
    $query = Drupal::database();
    $query->update('guestbook')
      ->condition('id', $this->id)
      ->fields([
        'name' => $form_state->getValue('user_name'),
        'email' => $form_state->getValue('email'),
        'phone' => $form_state->getValue('phone'),
        'comment' => $form_state->getValue('feedback'),
        'avatar' => $avatar[0],
        'image' => $image[0],

      ])
      ->execute();
    $form_state->setRedirect('guestbook.content');
    return new Url('guestbook.content');
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   * Display message with successfully left feedback or display any error when filling out the form
   */
  public function setMessage(array $form, FormStateInterface $form_state): AjaxResponse {
    $user_name = $form_state->getValue('user_name');
    $response = new AjaxResponse();
    if ($form_state->hasAnyErrors()) {
      foreach ($form_state->getErrors() as $errors_array) {
        $response->addCommand(new MessageCommand($errors_array, NULL, ['type'=>'error']));
      }
    }
    else {
      $response->addCommand(new MessageCommand('You edit ' . $user_name .'\'s review ! '));
    }
    Drupal::messenger()->deleteAll();
    return $response;
  }
}
