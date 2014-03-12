<?php
/*
 * This file is part of the Spotlight package.
 *
 * (c) Aurimas Niekis <aurimas.niekis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpotLight;

use Silex\Application;
use SpotLight\Exception\InvalidProviderException;
use SpotLight\Provider\JenkinsProvider;

/**
 * Class Configurator
 *
 * @author  Aurimas Niekis <aurimas.niekis@gmail.com>
 *
 * @package SpotLight
 */
class Configurator
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $providers;

    /**
     * @param $file
     */
    public function __construct($file)
    {
        $this->file = $file;
        $this->settings = $this->load();
    }

    /**
     * @throws \InvalidArgumentException
     * @return array|null
     */
    public function load()
    {
        if (!file_exists($this->file)) {
            throw new \InvalidArgumentException("Configuration file is not present");
        }
        $content = file_get_contents($this->file);
        $json = json_decode($content, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $json;
        } else {
            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    $msg = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $msg = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $msg = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $msg = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $msg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $msg = 'Unknown error';
                    break;
            }

            $this->app->error(sprintf("Configurator Error: %s", $msg));
            return null;
        }
    }

    public function getProjects()
    {
        return array_keys($this->settings['projects']);
    }

    public function getProvider($project)
    {
        if (isset($this->settings['projects'][$project])) {
            $settings = $this->settings['projects'][$project];
            switch (strtolower($settings['type'])) {
                case 'jenkins':
                    return new JenkinsProvider($settings);
                    break;

                default:
                    throw new InvalidProviderException("Invalid provider type");
                    break;
            }
        } else {
            throw new \InvalidArgumentException("Not existing project selected");
        }
    }
}
