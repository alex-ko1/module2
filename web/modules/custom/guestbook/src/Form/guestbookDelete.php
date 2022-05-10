<?php

namespace Drupal\gestbook\Form;

use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;

class guestbookDelete extends ConfirmFormBase {


  /**
   * @var mixed|null
   */
  private mixed $id;

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
    $query = \Drupal::database();
    $result = $query->select('guestbook', 'g')
      ->fields('g', ['image', 'avatar'])
      ->condition('id', $this->id)
      ->execute()->fetch();
    File::load($result->image)->delete();
    File::load($result->avatar)->delete();
    $query->delete('guestbook')
      ->condition('id', $this->id)
      ->execute();
    \Drupal::messenger()->addStatus('Successfully deleted.');
    $form_state->setRedirect('guestbook.content');
  }

  public function getQuestion() {
    $database = \Drupal::database();
    $result = $database->select('guestbook', 'g')
      ->fields('g', ['id', 'name'])
      ->condition('id', $this->id)
      ->execute()->fetch();
    return $this->t('Delete feedback users- @user_name, with id- %id ?', ['%id' => $result-> id, '@user_name' => $result-> name]);
  }

  public function getCancelUrl(): Url {
    return new Url('guestbook.content');
  }
}
