<?php

namespace Drupal\wechat_mobile\Controller;

use Drupal\wechat_api\Service\WechatApiService;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DrupalKernelInterface;
use Drupal\Core\Url;
use Drupal\Core\Render\Renderer;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Provides route responses for the container info pages.
 */
class WechatMobileController extends ControllerBase implements ContainerInjectionInterface {

    protected $renderer;

    protected $css;
    protected $js;

    protected $wechat_api;

    public function __construct(Renderer $render, WechatApiService $service) {
        global $base_url;

        $this->wechat_api = $service;

        $this->renderer = $render;

        $this->css = array(
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/css/bootstrap.min.css'
        ); 

        $this->js = array(
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/jquery-3.2.1.min.js',
            $base_url . '/' . drupal_get_path('module', 'wechat_mobile') . '/js/bootstrap.min.js',
        );
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('renderer'),
            $container->get('service.wechatapi'),
        );
    }

    /**
     * build simple test theme page
     * path /wechat/mobile/test
     **/
    public function CreateLeafPage(Request $request) {
        $values = $request->query->all();
        \Drupal::logger('GetTestThemePage')->notice( 'request: <pre>@data</pre>', array('@data' => print_r($values, true)) );

        //$twigFilePath = drupal_get_path('module', 'wechat_mobile') . '/templates/wechat-mobile-gen-tpl.html.twig';
        //$template = $this->twig->loadTemplate($twigFilePath);

        $build = array(
            '#theme' => 'wechat_mobile_gen_tpl',
            '#styles' => $this->css,
            '#scripts' => $this->js,
            '#title' => "world",
            '#page' => array('body_id' => 'create-leaf-page')
        );
        $html = $this->renderer->renderRoot($build);
        $response = new Response();
        $response->setContent($html);

        return $response;
    }


    public function GetRender() {
        $list[] = $this->t("First number was @number.", ['@number' => 1]);
        $list[] = $this->t("Second number was @number.", ['@number' => 2]);
        $list[] = $this->t('The total was @number.', ['@number' => 1 + 2]);

        $render_array['page_example_arguments'] = [
            // The theme function to apply to the #items.
            '#theme' => 'item_list',
            // The list itself.
            '#items' => $list,
            '#title' => $this->t('Argument Information'),
        ];

        return $render_array;
    }
}

