<?php
/**
 * @file
 * Contains \Drupal\guestbook\Controller\guestbook\feedback.
 */
namespace Drupal\guestbook\Controller;
/*
 * Provides route for custom module.
 */

use Drupal;
use Drupal\file\Entity\File;


class guestbookController {

  /*
 * Display simple page with form for leave review and this reviews.
 */
  public function content(): array {
    $form = Drupal::formBuilder()
      ->getForm('Drupal\guestbook\Form\guestbookForm');
    $query = Drupal::database();
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
    return [
      '#theme' => 'guestbook-theme',
      '#form' => $form,
      '#list' => $result,
    ];
  }
}
