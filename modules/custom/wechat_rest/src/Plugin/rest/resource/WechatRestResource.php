<?php

namespace Drupal\wechat_rest\Plugin\rest\resource;

use \Drupal\node\Entity\Node;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "wechat_rest_resource",
 *   label = @Translation("Wechat rest resource"),
 *   uri_paths = {
 *     "canonical" = "/mxw/rest",
 *     "https://www.drupal.org/link-relations/create" = "/mxw/rest"
 *   }
 * )
 */
class WechatRestResource extends ResourceBase {

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * Constructs a new WechatRestResource object.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param array $serializer_formats
     *   The available serialization formats.
     * @param \Psr\Log\LoggerInterface $logger
     *   A logger instance.
     * @param \Drupal\Core\Session\AccountProxyInterface $current_user
     *   A current user instance.
     */
    public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        array $serializer_formats,
        LoggerInterface $logger,
        AccountProxyInterface $current_user) {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

        $this->currentUser = $current_user;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->getParameter('serializer.formats'),
            $container->get('logger.factory')->get('wechat_rest'),
            $container->get('current_user')
        );
    }

    /**
     * Responds to GET requests.
     *
     * Returns a list of bundles for specified entity.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function get() {

        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
          throw new AccessDeniedHttpException();
        }

        $request = \Drupal::request();
        $values = $request->query->all();
        //$values = $this->currentRequest->query->get('values');

        //$this->logger->notice( "GET : " );
        $this->logger->notice( "GET : @data", array('@data' => print_r($values, true)) );
        $build = array(
            '#cache' => array(
                'max-age' => 0,
            ),
        );

        $para['data']['type'] = "hello";
        $para['data']['number'] = "10";
        $para['data']['ids'] = $values['ids'];

        
        //return (new ResourceResponse($myResponse))->addCacheableDependency($build);
        
        //return (new ResourceResponse("Implement REST State GET!" . $values['ids']))->addCacheableDependency($build);
        return (new ResourceResponse($para))->addCacheableDependency($build);
    }

    /**
     * Responds to POST requests.
     *
     * Returns a list of bundles for specified entity.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function post(array $data = []) {

        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
          throw new AccessDeniedHttpException();
        }

        $this->logger->notice( "POST : data <pre>@data</pre>", array('@data' => print_r($data, true)) );

        return new ResourceResponse("Implement REST State POST!");
    }

    protected function create_hotel($data) {
        if ($data['entity']['entity_type'] == 'hotel_main_page_add') {
            try {

                //find uid
                
                $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
                $node = \Drupal\node\Entity\Node::create(array(
                          'type' => 'hotel_main_page',
                          'title' => $data['entity']['title'],
                          'langcode' => $language,
                          'uid' => 1,
                          'status' => 1,
                          'body' => array('The body text'),
                          'field_date' => array("2000-01-30"),
                            //'field_fields' => array('Custom values'), // Add your custon field values like this
                    ));

                if ( !empty($data['field_gps_latitude']['value']) ) {
                }
                $node->save();
            }
            catch (Exception $e) {
              // Generic exception handling if something else gets thrown.
              $this->logger->error($e->getMessage());
            }



        }
    }
}
