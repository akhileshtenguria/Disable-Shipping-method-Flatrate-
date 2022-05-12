<?php
/**
 * Created By : Rohan Hapani
 */
namespace Zestardtech\DisableFlatRate\Plugin;

class HideShippingPlugin {


    const XML_PATH_FREE_SHIPPING_SUBTOTAL = "carriers/freeshipping/free_shipping_subtotal";

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    const FREESHIPPINGAMOUNT  = 'carriers/freeshipping/free_shipping_subtotal';

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_scopeConfig = $scopeConfig;
    }

    public function aroundCollectCarrierRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Closure $proceed,
        $carrierCode,
        $request
    ) {


          $scopeId = $this->_storeManager->getStore()->getId();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;

        // Get MOA value from system configuration.
        $freeShippingSubTotal = $this->_scopeConfig->getValue(self::XML_PATH_FREE_SHIPPING_SUBTOTAL, $storeScope, $scopeId);

        
        $cartItems = $this->_checkoutSession->getQuote()->getItems();
        $discountedSubtotal = 0;
        foreach($cartItems as $cartItem){
            $discountedSubtotal += $cartItem->getDiscountAmount();
        }
        
        $baseSubTotal = $this->_checkoutSession->getQuote()->getBaseSubtotal();
        $baseSubTotalWithDiscount = $baseSubTotal - $discountedSubtotal;

        //$grandTotal = $this->_checkoutSession->getQuote()->getGrandTotal();

        $freeShippingAmount = $this->_scopeConfig->getValue(self::FREESHIPPINGAMOUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE); // Add in 
        
         if($baseSubTotalWithDiscount >= $freeShippingAmount){
            if($carrierCode == 'flatrate'){ 
                return false;
            }
        }


        $result = $proceed($carrierCode, $request);
        return $result;
    }
}