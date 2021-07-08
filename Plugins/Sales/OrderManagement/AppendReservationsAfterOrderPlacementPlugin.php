<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_LowStockNotification
 * @copyright  Copyright (c) 2021 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\LowStockNotification\Plugins\Sales\OrderManagement;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface;
use Lof\LowStockNotification\Helper\Data as DataHelper;

class AppendReservationsAfterOrderPlacementPlugin
{
    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $_stockByWebsiteIdResolver;

    /**
     * @var GetStockItemDataInterface
     */
    private $_getStockItemData;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $_getStockItemConfiguration;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $_getReservationsQuantity;

    /**
     * AppendReservationsAfterOrderPlacementPlugin constructor.
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param GetStockItemDataInterface $getStockItemData
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     * @param DataHelper $helper
     */
    public function __construct(
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        GetStockItemDataInterface $getStockItemData,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetReservationsQuantityInterface $getReservationsQuantity,
        DataHelper $helper
    ) {
        $this->_stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->_getStockItemData = $getStockItemData;
        $this->_getStockItemConfiguration = $getStockItemConfiguration;
        $this->_getReservationsQuantity = $getReservationsQuantity;
        $this->helper = $helper;
    }

    /**
     * @param OrderManagementInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException
     */
    public function afterPlace(OrderManagementInterface $subject, OrderInterface $order) : OrderInterface
    {
        if ($this->helper->isEnabled()) {
            $websiteId = (int)$order->getStore()->getWebsiteId();
            $stock = $this->_stockByWebsiteIdResolver->execute((int)$websiteId);

            $stockId = (int)$stock->getStockId();
            $stockName = $stock->getName();
            $lowStockItems = [];

            foreach ($order->getAllVisibleItems() as $orderItem) {
                $sku = $orderItem->getSku();
                if ($sku && in_array($orderItem->getProductType(), DataHelper::SUPPORTED_PRODUCT_TYPE)) {
                    $stockItemData = $this->_getStockItemData->execute($sku, $stockId);
                    $stockItemConfiguration = $this->_getStockItemConfiguration->execute($sku, $stockId);

                    $qtyLeftInStock = $stockItemData[GetStockItemDataInterface::QUANTITY]
                                    + $this->_getReservationsQuantity->execute($sku, $stockId);

                    if ($this->helper->getQty() > $qtyLeftInStock) {
                        $orderItem->setData('saleable', $qtyLeftInStock);
                        $orderItem->setData('quantity', (int)$stockItemData[GetStockItemDataInterface::QUANTITY]);
                        $orderItem->setData('stockId', $stockId);
                        $orderItem->setData('stockName', $stockName);
                        $lowStockItems[] = $orderItem;
                    }
                }
            }

            if (!empty($lowStockItems)) {
                $this->helper->notify($lowStockItems);
            }
        }

        return $order;
    }
}
