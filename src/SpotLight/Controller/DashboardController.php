<?php
/*
 * This file is part of the Spotlight package.
 *
 * (c) Aurimas Niekis <aurimas.niekis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpotLight\Controller;

use Silex\Application;
use SpotLight\Provider\JenkinsProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardController
{
    /**
     * @var Application
     */
    public $app;

    /**
     * @var array
     */
    public $projects = [];

    /**
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->projects = $app['configurator']->getProjects();
    }

    /**
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function index(Request $request, Application $app)
    {
        return $app->render(
            'index.twig',
            ['projects' => $this->projects]
        );
    }

    /**
     * @param Request     $request
     * @param Application $app
     *
     * @return string|Response
     */
    public function project(Request $request, Application $app)
    {
        $project = $request->attributes->get("project");
        if ($this->projectExists($project)) {
            return "Ok";
        } else {
            $app->abort(404, "Project $project does not exist.");
        }
    }

    /**
     * @param Request     $request
     * @param Application $app
     *
     * @return string|Response
     */
    public function ajaxUpdate(Request $request, Application $app)
    {
        $project = $request->attributes->get("project");
        if ($this->projectExists($project)) {
            $provider = $app['configurator']->getProvider($project);
            return $app->json($provider->projects());
        } else {
            $app->abort(404, "Project $project does not exist.");
        }
    }

    /**
     * @param string $project
     *
     * @return bool
     */
    protected function projectExists($project)
    {
        return in_array($project, $this->projects);
    }
}
