<?php

namespace Drupal\wechat_service\Form;


use Drupal\wechat_api\Service\WechatApiService;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the SimpleForm form controller.
 *
 * This example demonstrates a simple form with a singe text input element. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class GetMenuForm extends FormBase {

    protected $wechat_api;

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('service.wechatapi')
        );
    }

    public function __construct(WechatApiService $service) {
        $this->wechat_api = $service;
    }

  /**
   * Build the simple form.
   *
   * A build form method constructs an array that defines how markup and
   * other form elements are included in an HTML form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
    public function buildForm(array $form, FormStateInterface $form_state) {

        //\Drupal::logger('buildForm')->notice( 'menu: <pre>@data</pre>', array('@data' => print_r($this->wechat_api->get_custom_menu(), true)) );
        $form['result'] = array(
            '#markup' => '<p>' . $this->wechat_api->get_custom_menu() . '</p>'
        );

        return $form;
    }

    public function getFormId() {
        return 'wechat_service_get_menu';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
    }

}

