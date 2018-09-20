<?php

class Metagento_AvailableQty_Helper_Data extends
    Mage_Core_Helper_Abstract
{
    public function isEnabled( $store = null )
    {
        return Mage::getStoreConfig('availableqty/general/enabled', $store);
    }

    public function isAutoAdd( $store = null )
    {
        return Mage::getStoreConfig('availableqty/general/auto_add', $store);
    }
}