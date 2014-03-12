<?php
/*
 * This file is part of the Spotlight package.
 *
 * (c) Aurimas Niekis <aurimas.niekis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpotLight\Application;

use Silex\Application;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use SpotLight\Configurator;
use SpotLight\Controller\DashboardController;

class SpotLightApplication extends Application
{
    use Application\TwigTrait;
    use Application\MonologTrait;

    public function __construct($env = "prod", array $values = array())
    {
        parent::__construct($values);
        $app = $this;

        $this->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__.'/../Resources/views/',
        ));

        if ($env == 'prod') {
            $this->register(new MonologServiceProvider(), array(
                    'monolog.logfile' => __DIR__.'/../../../logs/production.log',
                    'monolog.level' => 'WARNING',
                    'monolog.name' => 'SpotLight'
                ));
        } else {
            $this->register(new MonologServiceProvider(), array(
                    'monolog.logfile' => __DIR__.'/../../../logs/development.log',
                    'monolog.level' => 'DEBUG',
                    'monolog.name' => 'SpotLight'
                ));
        }

        if (php_sapi_name() === 'cli-server') {
            //$this['debug'] = true;
        }

        $this['configurator'] = $this->share(function () use ($app) {
            return new Configurator(__DIR__.'/../../../config.json');
        });

        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new ServiceControllerServiceProvider());
        $this['dashboard.controller'] = $this->share(function () use ($app) {
            return new DashboardController($app);
        });

        $this->get("/", 'dashboard.controller:index')->bind("projects");
        $this->get("/project/{project}", 'dashboard.controller:project')->value("project", "default")->bind("project");
        $this->get("/project/{project}/json", 'dashboard.controller:ajaxUpdate')
            ->assert('project', '\w+')
            ->bind("project_json");
    }

}
