<?php
/*
 * This file is part of the Spotlight package.
 *
 * (c) Aurimas Niekis <aurimas.niekis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpotLight\Provider;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Silex\Application;

/**
 * Class JenkinsProvider
 *
 * @author  Aurimas Niekis <aurimas.niekis@gmail.com>
 *
 * @package SpotLight\Provider
 */
class JenkinsProvider extends AbstractProvider
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->httpClient = new Client($settings['server']);

        $this->loadXML();
    }

    public function loadXML()
    {
        $file = isset($this->settings['file']) ? $this->settings['file'] : "/cc.xml";
        $request = $this->httpClient->get($file);
        $request->setAuth($this->settings['username'], $this->settings['password']);

        $response = null;

        try {
            $response = $request->send();
        } catch (BadResponseException $e) {
            $this->app->error(sprintf("Provider Jenkins: HTTP Error: %s" . $e->getMessage()));
        }

        $this->xml = $response->xml();
    }

    public function parseData()
    {
        /** @var $project \SimpleXMLElement */
        foreach ($this->xml->Project as $projectNode) {
            $project = [];
            $project['name'] = (string)$projectNode['name'];
            $project['url'] = (string)$projectNode['webUrl'];
            $project['build'] = (string)$projectNode['lastBuildLabel'];
            $project['build_time'] = strtotime((string)$projectNode['lastBuildTime']) . "000";
            $project['build_status'] = $this->mapStatus((string)$projectNode['lastBuildStatus']);
            $project['activity'] = $this->mapActivity((string)$projectNode['activity']);

            $this->projects[] = $project;
        }
    }

    /**
     * @param string $status
     *
     * @return int
     */
    public function mapStatus($status)
    {
        switch($status) {
            case "Failure":
                return self::STATUS_FAILURE;
                break;

            case "Success":
                return self::STATUS_SUCCESS;
                break;

            default:
                return self::STATUS_UNKNOWN;
                break;
        }
    }

    /**
     * @param string $status
     *
     * @return int
     */
    public function mapActivity($status)
    {
        switch($status) {
            case "Sleeping":
                return self::ACTIVITY_SLEEPING;
                break;

            case "Building":
                return self::ACTIVITY_BUILDING;
                break;

            default:
                return self::STATUS_UNKNOWN;
                break;
        }
    }

    public function projects()
    {
        $this->parseData();
        return $this->projects;
    }
}
