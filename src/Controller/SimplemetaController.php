<?php
/**
 * @file
 * Contains \Drupal\book\Controller\BookController.
 */

namespace Drupal\simplemeta\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * Controller routines for book routes.
 */
class SimplemetaController {

  /**
   * Returns an administrative overview of all books.
   *
   * @return array
   *   A render array representing the administrative page content.
   */

  public function adminContent() {
  // $query = db_select('simplemeta', 's')
  //   ->extend('PagerDefault')
  //   ->fields('s')
  //   ->orderBy('s.sid', 'DESC')->limit(20);
  // $result = $query->execute();

  // $items = array();
  // while ($meta = $result->fetchObject()) {
  //   $meta->data = unserialize($meta->data);
  //   $items[] = $meta;
  // }

  // return _theme('_simplemeta_meta_list', array('items' => $items)) . theme('pager');
  	 return array(
      '#markup' => t('Hello World!'),
    );
    //return new RESPONSE('hello');
  }
  public function addContent() {

    // @todo Find the alternate of stdClass

    // $meta = new stdClass();
    // $meta->data = array();
    // $meta->language = '';
    $meta = array();
    $form = \Drupal::formBuilder()->getForm('\Drupal\simplemeta\Form\SimplemetaForm',$meta);
    return $form;
    //return new RESPONSE('hello add');
  }

  public function editContent() {
    return new RESPONSE('hello edit');
  }

  public function deleteContent() {
    return new RESPONSE('hello delete');
  }

  public function settingsContent() {
    return new RESPONSE('hello settings');
  }
}
?>