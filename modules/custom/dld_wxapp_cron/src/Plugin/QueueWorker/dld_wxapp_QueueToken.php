<?php

namespace Drupal\dld_wxapp_cron\Plugin\QueueWorker;

use Drupal\wechat_api\Service\WechatApiService;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A access Token worker.
 *
 * @QueueWorker(
 *   id = "cron_access_Token_queue",
 *   title = @Translation("wechat app access Token queue worker"),
 *   cron = {"time" = 30}
 * )
 */

class dld_wxapp_QueueToken extends QueueWorkerBase implements ContainerFactoryPluginInterface {
//class dld_wxapp_QueueToken extends QueueWorkerBase {
    /**
     * The logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    protected $wechatApi;
    protected $configFactory;

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

//        \Drupal::logger('dld_wxapp_QueueToken')->notice(
//            'configuration configuration: <pre>@configuration</pre>,
//             plugin_id: @plugin_id, plugin_definition: @plugin_definition',
//            array('@configuration' => print_r($configuration, true),
//                '@plugin_id' => $plugin_id,
//                '@plugin_definition' => $plugin_definition
//            )
//        );

        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('logger.factory'),
            $container->get('config.factory'),
            $container->get('service.wechatapi')
        );
    }

    /**
     * dld_wxapp_QueueToken constructor.
     *
     * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
     *   The logger service the instance should use.
     */
    public function __construct(array $configuration,
                                $plugin_id,
                                $plugin_definition,
                                LoggerChannelFactoryInterface $logger,
                                ConfigFactoryInterface $config_factory,
                                WechatApiService $service ) {

        parent::__construct($configuration, $plugin_id, $plugin_definition, $logger, $config_factory);

        $this->logger = $logger;
        $this->configFactory = $config_factory;
        $this->wechatApi = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function processItem($data) {

        if(time() >= $data['lastrun'] + $data['interval']) {

            //$this->logger->get('dld_wxapp_QueueToken')->notice('execute access token');

            if ( $access_token = $this->wechatApi->get_access_token() ) {
                //return token
                $config = $this->configFactory->getEditable('dld.wxapp.config');
                $config->set('access_token', $access_token)->save();
                
                //set token value to node 15
                $entity = \Drupal::entityManager()->getStorage('node')->load(1);
                $entity->field_text1->value = $access_token;
                $entity->save();
            }

            // Set last run execute time.
            $config = $this->configFactory->getEditable('dld_wxapp_cron.settings');
            //$config = \Drupal::service('config.factory')->getEditable('dld_wxapp_cron.settings');
            $config->set('LastRun', time())->save();
        }

    }

}
