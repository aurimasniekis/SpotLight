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

use SpotLight\Exception\InvalidProviderException;

/**
 * Class AbstractProvider
 *
 * @author  Aurimas Niekis <aurimas.niekis@gmail.com>
 *
 * @package SpotLight\Provider
 */
class AbstractProvider
{
    const STATUS_SUCCESS = 0;
    const STATUS_FAILURE = 1;
    const STATUS_UNKNOWN = -1;

    const ACTIVITY_BUILDING = 1;
    const ACTIVITY_SLEEPING = 0;

    /**
     * @var array
     */
    public $projects = [];

    public function projects()
    {
        throw new InvalidProviderException("The method projects should be populated");
    }
}
