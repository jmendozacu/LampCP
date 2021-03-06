<?php
/**
 * LampCP
 * https://github.com/jeboehm/LampCP
 *
 * Licensed under the GPL Version 2 license
 * http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */

namespace Jeboehm\Lampcp\ConfigBundle\Service;

use Jeboehm\Lampcp\ConfigBundle\Service\AbstractConfigProvider;

/**
 * Class ConfigProviderCollector
 *
 * Collects the config providers. Used by
 * ConfigProviderCompilerPass.
 *
 * @package Jeboehm\Lampcp\ConfigBundle\Service
 * @author  Jeffrey Böhm <post@jeffrey-boehm.de>
 */
class ConfigProviderCollector {
    /** @var array */
    protected $_provider;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->_provider = array();
    }

    /**
     * Add provider.
     *
     * @param AbstractConfigProvider $provider
     */
    public function addProvider(AbstractConfigProvider $provider) {
        $this->_provider[] = $provider;
    }

    /**
     * Return registered providers.
     *
     * @return AbstractConfigProvider[]
     */
    public function getProviders() {
        return $this->_provider;
    }
}
