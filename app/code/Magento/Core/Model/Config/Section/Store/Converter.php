<?php
/**
 * DB store configuration data converter. Converts associative array to tree array
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Config\Section\Store;

class Converter extends \Magento\Core\Model\Config\Section\Converter
{
    /**
     * @var \Magento\Core\Model\Config\Section\Processor\Placeholder
     */
    protected $_processor;

    /**
     * @param \Magento\Core\Model\Config\Section\Processor\Placeholder $processor
     */
    public function __construct(\Magento\Core\Model\Config\Section\Processor\Placeholder $processor)
    {
        $this->_processor = $processor;
    }

    /**
     * Convert config data
     *
     * @param array $source
     * @param array $initialConfig
     * @return array
     */
    public function convert($source, $initialConfig = array())
    {
        $storeConfig = array_replace_recursive($initialConfig, parent::convert($source));
        return $this->_processor->process($storeConfig);
    }
}
