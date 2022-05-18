<?php

namespace Drupal\guestbook\Form;

use Drupal;
use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

class guestbookDelete extends ConfirmFormBase {


  protected $id;

  public function getFormId(): string {
    return 'delete_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL): array {
    $this->id = $id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /**
     * Delete image and avatar field from the database.
     */
    $query = Drupal::database();
    $result = $query->select('guestbook', 'g')
      ->fields('g', ['image', 'avatar', 'id' ])
      ->condition('id', $this->id)
      ->execute()->fetch();
    if ($result->image){
    File::load($result->image)->delete();
    }
    if ($result->avatar){
    File::load($result->avatar)->delete();
    }
    $query->delete('guestbook')
      ->condition('id', $this->id)
      ->execute();
    Drupal::messenger()->addStatus('Successfully deleted.');
    $form_state->setRedirect('guestbook.content');
  }

  /**
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   * Question about confirming delete this feedback.
   */
  public function getQuestion(): \Drupal\Core\StringTranslation\TranslatableMarkup {
    $database = Drupal::database();
    $result = $database->select('guestbook', 'g')
      ->fields('g', ['id', 'name'])
      ->condition('id', $this->id)
      ->execute()->fetch();
    return $this->t('Delete feedback users - @user_name, with id - %id?', ['%id' => $result-> id, '@user_name' => $result-> name]);
  }

  public function getCancelUrl(): Url {
    return new Url('guestbook.content');
  }
}
