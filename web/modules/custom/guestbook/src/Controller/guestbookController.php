<?php
/**
 * @file
 * Contains \Drupal\guestbook\Controller\guestbook\feedback.
 */
namespace Drupal\guestbook\Controller;
/*
 * Provides route for custom module.
 */
use Drupal\file\Entity\File;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

class guestbookController {

  /*
 * Display simple page.
 */
  public function content() {
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    $admin = "administrator";
    $form = \Drupal::formBuilder()
      ->getForm('Drupal\guestbook\Form\guestbookForm');
    $query = \Drupal::database();
    $result = $query->select('guestbook', 'g')
      ->fields('g', ['name', 'phone', 'email', 'comment', 'image', 'avatar', 'timestamp', 'id'])
      ->orderBy('id', 'DESC')
      ->execute()->fetchAll();
    foreach ($result as $row) {
      $avatar = NULL;
      $image = NULL;
      if ($row->avatar != $avatar) {
        $avatar = File::load($row->avatar)->createFileUrl(FALSE);
      }
      if ($row->image != $image) {
        $image = File::load($row->image)->createFileUrl(FALSE);
      }
      $review[] = [
        'id' => $row->id,
        'name' => $row->name,
        'email' => $row->email,
        'phone' => $row->phone,
        'comment' => $row->comment,
        'avatar' => $avatar,
        'image' => $image,
        'timestamp' => $row->timestamp,
      ];
      $result=$review;
    }
    $data = [];
    if (in_array($admin, $roles)) {
      $url = Url::fromRoute('delete.content', ['id' => $row->id]);
      $url2 = Url::fromRoute('edit.content', ['id' => $row->id]);
      $delete_link = [
        '#title' => 'Delete',
        '#type' => 'link',
        '#url' => $url,
        '#attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
        ],
        '#attached' => [
          'library' => ['core/drupal.dialog.ajax'],
        ],
      ];
      $edit_link = [
        '#title' => 'Edit',
        '#type' => 'link',
        '#url' => $url2,
        '#attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
        ],
        '#attached' => [
          'library' => ['core/drupal.dialog.ajax'],
        ],
      ];
      $links['link'] = [
        'data' => [
          "#theme" => 'operations',
          'delete' => $delete_link,
          'edit' => $edit_link,
        ],
      ];
      $data[] = $links;
    }
    /* $data = [];
    foreach ($result as $row) {
      $file = File::load($row->image);
      $uri = $file->getFileUri();
      $userImage = [
        '#theme' => 'image',
        '#uri' => $uri,
        '#alt' => 'Cat',
        '#width' => 125,
        '#attributes' => [
          'target' => ['_blank']
        ],
      ];
      $file2 = File::load($row->avatar);
      $uri2 = $file2->getFileUri();
      $userAvatar = [
        '#theme' => 'image',
        '#uri' => $uri2,
        '#alt' => 'Cat',
        '#width' => 50,
        '#attributes' => [
          'target' => ['_blank']
        ],
      ];
      $data[] = [
        'avatar' => [
          'data' => $userAvatar,
        ],
        'name' => $row->name,
        'timestamp' => $row->timestamp,
        'comment' => $row->comment,
        'image' => [
          'data' => $userImage,
        ],
        'email' => $row->email,
        'phone' => $row->phone,
      ];
    }
    $build[] = [
      '#type' => 'table',
      '#rows' => $data,
    ];*/
    return [
      '#theme' => 'guestbook-theme',
      '#form' => $form,
      '#list' => $result,
      '#links' => $data,
    ];
  }
}
