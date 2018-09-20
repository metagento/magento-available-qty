<?php


class Metagento_AvailableQty_Model_Observer
{

    public function sales_quote_item_qty_set_after( $observer )
    {
        /** @var Metagento_AvailableQty_Helper_Data $helper */
        $helper = Mage::helper('availableqty');
        if ( !$helper->isEnabled() ) {
            return $this;
        }
        // Check error
        Mage::getSingleton('cataloginventory/observer')->checkQuoteItemQty($observer);
        $error = false;
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = $observer->getEvent()->getItem();
        $product   = Mage::getModel('catalog/product')->loadByAttribute('sku', $quoteItem->getSku());
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->assignProduct($product);
        if ( !$stockItem->getManageStock() ) {
            return $this;
        }
        if ( count($quoteItem->getErrorInfos()) ) {
            foreach ( $quoteItem->getErrorInfos() as $errorInfo ) {
                if ( $errorInfo['origin'] == 'cataloginventory' ) {
                    $error = true;
                }
            }
        }
        // Message and set QTY
        if ( $error ) {
            if ( !$stockItem->getData('is_qty_decimal') ) {
                $avaiQty = number_format($stockItem->getData('qty'));
            } else {
                $avaiQty = $stockItem->getData('qty');
            }
            if ( $quoteItem->getProductType() == 'simple' ) {
                Mage::getSingleton('core/session')->addWarning($helper->__("Available quantity for %s is %s", $product->getName(), $avaiQty));
            }
            if ( $helper->isAutoAdd() ) {
                Mage::getSingleton('core/session')->addWarning($helper->__("Available quantity for %s is %s", $product->getName(), $avaiQty));
                $quoteItem->getQuote()->setIsSuperMode(true);
                $quoteItem->getQuote()->setHasError(false);
                $quoteItem->setHasError(false);
                Mage::register('quote_item_oldqty', $quoteItem->getData('qty'));
                $quoteItem->setData('qty', $avaiQty);
                Mage::register('quote_item_newqty', $quoteItem->getData('qty'));
            }
        }
        return $this;
    }

}