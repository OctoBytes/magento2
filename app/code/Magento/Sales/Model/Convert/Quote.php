<?php
/**
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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Quote data convert model
 */
namespace Magento\Sales\Model\Convert;

class Quote extends \Magento\Object
{
    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Core\Model\Event\Manager
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\AddressFactory
     */
    protected $_orderAddressFactory;

    /**
     * @var \Magento\Sales\Model\Order\PaymentFactory
     */
    protected $_orderPaymentFactory;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_orderItemFactory;

    /**
     * @param \Magento\Core\Model\Event\Manager $eventManager
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Order\AddressFactory $orderAddressFactory
     * @param \Magento\Sales\Model\Order\PaymentFactory $orderPaymentFactory
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Event\Manager $eventManager,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\AddressFactory $orderAddressFactory,
        \Magento\Sales\Model\Order\PaymentFactory $orderPaymentFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        array $data = array()
    ) {
        $this->_eventManager = $eventManager;
        $this->_coreData = $coreData;
        $this->_orderFactory = $orderFactory;
        $this->_orderAddressFactory = $orderAddressFactory;
        $this->_orderPaymentFactory = $orderPaymentFactory;
        $this->_orderItemFactory = $orderItemFactory;
        parent::__construct($data);
    }

    /**
     * Convert quote model to order model
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @param null|\Magento\Sales\Model\Order $order
     * @return \Magento\Sales\Model\Order
     */
    public function toOrder(\Magento\Sales\Model\Quote $quote, $order = null)
    {
        if (!($order instanceof \Magento\Sales\Model\Order)) {
            $order = $this->_orderFactory->create();
        }
        /* @var $order \Magento\Sales\Model\Order */

        $order->setIncrementId($quote->getReservedOrderId())
            ->setStoreId($quote->getStoreId())
            ->setQuoteId($quote->getId())
            ->setQuote($quote)
            ->setCustomer($quote->getCustomer());

        $this->_coreData->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);
        $this->_eventManager->dispatch('sales_convert_quote_to_order', array('order' => $order, 'quote' => $quote));
        return $order;
    }

    /**
     * Convert quote address model to order
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @param null|\Magento\Sales\Model\Order $order
     * @return  \Magento\Sales\Model\Order
     */
    public function addressToOrder(\Magento\Sales\Model\Quote\Address $address, $order = null)
    {
        if (!($order instanceof \Magento\Sales\Model\Order)) {
            $order = $this->toOrder($address->getQuote());
        }

        $this->_coreData->copyFieldsetToTarget(
            'sales_convert_quote_address',
            'to_order',
            $address,
            $order
        );

        $this->_eventManager->dispatch('sales_convert_quote_address_to_order', array(
            'address' => $address,
            'order' => $order
        ));
        return $order;
    }

    /**
     * Convert quote address to order address
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  \Magento\Sales\Model\Order\Address
     */
    public function addressToOrderAddress(\Magento\Sales\Model\Quote\Address $address)
    {
        $orderAddress = $this->_orderAddressFactory->create()
            ->setStoreId($address->getStoreId())
            ->setAddressType($address->getAddressType())
            ->setCustomerId($address->getCustomerId())
            ->setCustomerAddressId($address->getCustomerAddressId());

        $this->_coreData->copyFieldsetToTarget(
            'sales_convert_quote_address',
            'to_order_address',
            $address,
            $orderAddress
        );

        $this->_eventManager->dispatch('sales_convert_quote_address_to_order_address',
            array('address' => $address, 'order_address' => $orderAddress));

        return $orderAddress;
    }

    /**
     * Convert quote payment to order payment
     *
     * @param   \Magento\Sales\Model\Quote\Payment $payment
     * @return  \Magento\Sales\Model\Quote\Payment
     */
    public function paymentToOrderPayment(\Magento\Sales\Model\Quote\Payment $payment)
    {
        $orderPayment = $this->_orderPaymentFactory->create()
            ->setStoreId($payment->getStoreId())
            ->setCustomerPaymentId($payment->getCustomerPaymentId());

        $this->_coreData->copyFieldsetToTarget(
            'sales_convert_quote_payment',
            'to_order_payment',
            $payment,
            $orderPayment
        );

        return $orderPayment;
    }

    /**
     * Convert quote item to order item
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return  \Magento\Sales\Model\Order\Item
     */
    public function itemToOrderItem(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
        $orderItem = $this->_orderItemFactory->create()
            ->setStoreId($item->getStoreId())
            ->setQuoteItemId($item->getId())
            ->setQuoteParentItemId($item->getParentItemId())
            ->setProductId($item->getProductId())
            ->setProductType($item->getProductType())
            ->setQtyBackordered($item->getBackorders())
            ->setProduct($item->getProduct())
            ->setBaseOriginalPrice($item->getBaseOriginalPrice())
        ;

        $options = $item->getProductOrderOptions();
        if (!$options) {
            $options = $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct());
        }
        $orderItem->setProductOptions($options);
        $this->_coreData->copyFieldsetToTarget(
            'sales_convert_quote_item',
            'to_order_item',
            $item,
            $orderItem
        );

        if ($item->getParentItem()) {
            $orderItem->setQtyOrdered($orderItem->getQtyOrdered()*$item->getParentItem()->getQty());
        }

        if (!$item->getNoDiscount()) {
            $this->_coreData->copyFieldsetToTarget(
                'sales_convert_quote_item',
                'to_order_item_discount',
                $item,
                $orderItem
            );
        }
        return $orderItem;
    }
}
