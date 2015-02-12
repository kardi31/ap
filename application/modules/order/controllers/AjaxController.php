<?php

/**
 * Order_AjaxController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_AjaxController extends MF_Controller_Action {
    
    public function init() {
        $this->_helper->ajaxContext()
                ->addActionContext('remove-product-from-cart', 'json')
                ->addActionContext('add-to-cart', 'json')
                ->addActionContext('check-coupon', 'json')
                ->addActionContext('check-delivery-type', 'json')
                ->addActionContext('change-number-of-product', 'json')
                ->initContext();
        parent::init();
    }
    
    public function addToCartAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $orderService = $this->_service->getService('Order_Service_Order');
        $cartService = $this->_service->getService('Order_Service_Cart');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        
        $this->view->clearVars();
        
        $language = $i18nService->getDefaultLanguage();
        
        $translator = $this->_service->get('translate');
        $cart = $cartService->getCart();
        
        $counter = (int) $this->getRequest()->getParam('counter');
        
	
        if($product = $productService->getFullProduct((int) $this->getRequest()->getParam('product-id'))) {
	
	    /* price calculate */
	    
	    $user = $this->_helper->user();
	    
	    if($user):
		$userDiscount = $user['Discount']->toArray();
		$userGroups = $user->get("Groups");
	    endif;

	    $userGroupDiscounts = array();
	    foreach($userGroups as $userGroup):
		$userGroupDiscounts[] = $userGroup['Discount']->toArray();
	    endforeach;
	    $arrayDiscounts = array($product['Discount'], $product['Producer']['Discount'], $userDiscount);
	    foreach($userGroupDiscounts as $userGroupDiscount):
		$arrayDiscounts[] = $userGroupDiscount;
	    endforeach;
	    
	    $flag = MF_Discount::getPriceWithDiscount($product['promotion_price'], $arrayDiscounts);
	    $flag = $flag['flag'];
	     
	    if($flag || $product['promotion']): 
		 if($product['promotion']): 
		     if($flag): 
							     
			$price = MF_Discount::getPriceWithDiscount($product['promotion_price'], $arrayDiscounts);
			$price = $price['price'];
			    $productPrice = $price; 
		    else: 
			$productPrice = $product['promotion_price'];
		    endif; 
		 else: 

		    $price = MF_Discount::getPriceWithDiscount($product['price'], $arrayDiscounts);
		    $price = $price['price'];
		    $productPrice = $price; 
		 endif; 
	     else: 
		 $productPrice = $product['price']; 
	     endif; 
            $item = $cart->get('Product_Model_Doctrine_Product', $product->getId());
            if ($product->getAvailability() > $item[""]['count']):
                $cart->remove('Product_Model_Doctrine_Product', $product->getId());
                if ($counter && $counter > 0):
                    $cart->add('Product_Model_Doctrine_Product', $product->getId(),
                        $product->Translation[$language->getId()]->name, $productPrice, $item[""]['count']+$counter, null, true,
                        array('photoOffset' => $product['PhotoRoot']['offset'],
                            'photoFilename' => $product['PhotoRoot']['filename'],
                            'categoryName' => $product['Categories'][0]['Translation'][$language->getId()]['name'],
                            'categorySlug' => $product['Categories'][0]['Translation'][$language->getId()]['slug'],
                            'productSlug' => $product['Translation'][$language->getId()]['slug'],

                        ));
                else:
                    $cart->add('Product_Model_Doctrine_Product', $product->getId(),
                        $product->Translation[$language->getId()]->name,
                        $productPrice, $item[""]['count']+1, null, true,
                        array('photoOffset' => $product['PhotoRoot']['offset'],
                            'photoFilename' => $product['PhotoRoot']['filename'],
                            'categoryName' => $product['Categories'][0]['Translation'][$language->getId()]['name'],
                            'categorySlug' => $product['Categories'][0]['Translation'][$language->getId()]['slug'],
                            'productSlug' => $product['Translation'][$language->getId()]['slug'],
                        )
                    );
                endif;
            endif;
        }
        
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
	$cart = $cartService->getCart();
        $cartItems = $cart->get('Product_Model_Doctrine_Product');
        $this->view->assign('cartItems', $cartItems);
        $this->view->assign('cart', $cart);
	
        $cartItemsView = $this->view->partial('cart/mini-cart.phtml', 'order', array('cartItems' => $cartItems,'cart' => $cart));
        
        $this->view->assign('status', "success");
        $this->view->assign('miniCartView', $cartItemsView);  
    }
    
    public function removeProductFromCartAction() {
        $affilateProgramService = $this->_service->getService('Affilateprogram_Service_Affilateprogram');
        $productService = $this->_service->getService('Product_Service_Product');
        $cartService = $this->_service->getService('Order_Service_Cart');
        
        $cart = $cartService->getCart();
        
        $productId = $this->getRequest()->getParam('product-id');
        $cart->remove('Product_Model_Doctrine_Product', $productId);
        
        $counterCart = $cart->count();
        
        $items = $cart->get('Product_Model_Doctrine_Product');
        
        $language = $this->view->language;
        
        $productsIds = array();
        foreach($items as $productId=>$item):
            $productsIds[] = $productId;
        endforeach;
       
        if($productsIds):
            $products = $productService->getPreSortedProductCart($productsIds);
        endif;
        
        $user = $this->_helper->user();
        
        if($user):
            $userDiscount = $user['Discount']->toArray();
            $userGroups = $user->get("Groups");
        endif;

        $userGroupDiscounts = array();
        foreach($userGroups as $userGroup):
            $userGroupDiscounts[] = $userGroup['Discount']->toArray();
        endforeach;
        
        $totalPrice = 0;
        foreach($products as $product): 
            foreach($items as $id=>$item):
                if($product->getId() == $id):
                    $arrayDiscounts = array($product['Discount'], $product['Producer']['Discount'], $userDiscount);
                    foreach($userGroupDiscounts as $userGroupDiscount):
                        $arrayDiscounts[] = $userGroupDiscount;
                    endforeach;
                    $flag = MF_Discount::getPriceWithDiscount($product['promotion_price'], $arrayDiscounts);
                    $flag = $flag['flag'];
                    if($flag || $product['promotion']):
                        if($product['promotion']):
                            if($flag):
                                $price = MF_Discount::getPriceWithDiscount($product['promotion_price'], $arrayDiscounts); 
                                $price = $price['price'];
                                $totalPrice += $price*$item[""]['count'];
                            else:
                                $totalPrice += $product['promotion_price']*$item[""]['count'];
                            endif;
                        elseif($flag):
                            $price = MF_Discount::getPriceWithDiscount($product['price'], $arrayDiscounts); 
                            $price = $price['price'];
                            $totalPrice += $price*$item[""]['count'];
                        endif;
                    else:
                        $totalPrice += $product['price']*$item[""]['count'];
                    endif;
                endif;
              endforeach;
       endforeach;
       
       if (isset($_COOKIE["reference_number"])):
            $partner = $affilateProgramService->getPartner($_COOKIE["reference_number"], 'reference_number');
            if ($partner):
                $discountPartner = ($totalPrice*$partner->getDiscount())/100;
                $discountPartner = $this->view->currency($discountPartner);
            endif;
       endif; 

       $this->view->assign('totalPrice', $totalPrice);

       $this->view->assign('userDiscount', $userDiscount);
       $this->view->assign('userGroupDiscounts', $userGroupDiscounts);
               
       $this->view->assign('items', $items);
       $this->view->assign('products', $products);
        
     
       $miniCartView = $this->view->partial('cart/mini-cart.phtml', 'order', array('cartItems' => $items,'cart' => $cart));

       
       $this->view->assign('body', $cartView);
       $this->_helper->json(array(
            'status' => 'success',
            'body' => $cartView,
            'miniCartView' => $miniCartView,
            'counterCart' => $counterCart,
            'totalPrice' => $this->view->currency($totalPrice),
            'discountPartner' => $discountPartner
       ));      
        
       $this->_helper->layout->disableLayout();
    }
    
    public function checkCouponAction() {
        $couponService = $this->_service->getService('Order_Service_Coupon');

        $this->view->clearVars();
        $cartService = $this->_service->getService('Order_Service_Cart');
        
        $cart = $cartService->getCart();
        
       
        $totalPrice = $cart->getSum();
        
        $translator = $this->_service->get('translate');
        
        if ($this->getRequest()->getParam('code')){
            $coupon = $couponService->getCoupon($this->getRequest()->getParam('code'), 'code');
        }
        
        $valid = false;
        if($coupon):
            if (!$coupon->isUsed() && MF_Coupon::isValid($coupon)){
                $valid = true;
            }
        endif;
        
	
	$noCouponPrice = $totalPrice;
       
       
       if ($valid):
           if ($coupon->getType() == "percent"):
               $totalPrice = $totalPrice - ($coupon->getAmount()*$totalPrice/100);
               $couponAmount = $coupon->getAmount()."%";
           endif;
           if ($coupon->getType() == "amount"):
               $totalPrice = $totalPrice - $coupon->getAmount();
               $couponAmount = $coupon->getAmount()." zł";
           endif;
       endif;
        
       $this->_helper->viewRenderer->setNoRender();
       $this->_helper->layout->disableLayout();
        
       $this->view->assign('noCouponPrice', $noCouponPrice." zł"); 
       $this->view->assign('couponAmount', $couponAmount); 
       $this->view->assign('valid', $valid); 
       $this->view->assign('totalSum', $totalPrice." zł"); 
       $this->view->assign('status', "success");  
    }
    
     public function checkDeliveryTypeAction() {
        $deliveryTypeService = $this->_service->getService('Order_Service_DeliveryType');

        $this->view->clearVars();
        
        $translator = $this->_service->get('translate');
        
        if ($this->getRequest()->getParam('deliveryTypeId')){
            $deliveryType = $deliveryTypeService->getDeliveryType($this->getRequest()->getParam('deliveryTypeId'), 'id');
        }
        $totalPrice = $this->getRequest()->getParam('totalPrice');

        
        if ($deliveryType):
            if ($deliveryType->getPrice() != 0){
                $deliveryPrice = $deliveryType->getPrice();
            }
            else{
                if ($deliveryType['type'] == "przelew"):
                    $deliveryPrice = 0;
                else:
                    $deliveryPrice = $deliveryType->getPrice();
                endif;
            }
        endif;

        if ($deliveryType->getType() == "przelew"){
            $showPayment = true;
        }
        else{
            $showPayment = false;
        }
        
        $priceWithDelivery = $totalPrice + $deliveryPrice;
        
        $priceWithDelivery = $this->view->currency($priceWithDelivery);
        
        $deliveryPrice = $this->view->currency($deliveryPrice);
        
        if ($deliveryType->getId() == 9){
            $deliveryPrice = "wyceniamy indywidualnie";
            $priceWithDelivery .= " <span style='font-size: 10px;'> + koszt dostawy</span>";
        }
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $this->view->assign('deliveryPrice', $deliveryPrice); 
        $this->view->assign('totalPrice', $totalPrice); 
        $this->view->assign('showPayment', $showPayment); 
        $this->view->assign('priceWithDelivery', $priceWithDelivery); 
        $this->view->assign('status', "success");  
    }
    
    public function changeNumberOfProductAction() {
        $productService = $this->_service->getService('Product_Service_Product');
        $cartService = $this->_service->getService('Order_Service_Cart');
        $affilateProgramService = $this->_service->getService('Affilateprogram_Service_Affilateprogram');
        $i18nService = $this->_service->getService('Default_Service_I18n');
        $couponService = $this->_service->getService('Order_Service_Coupon');

        $this->view->clearVars();
        
        $language = $i18nService->getDefaultLanguage();
        
        $translator = $this->_service->get('translate');
        
        $cart = $cartService->getCart();
        
        $counter = (int) $this->getRequest()->getParam('counter');
        $flagAvailability = false;
        
        if($product = $productService->getFullProduct((int) $this->getRequest()->getParam('id'))) {
            $item = $cart->get('Product_Model_Doctrine_Product', $product->getId());
            $oldCounterProduct = $item[""]['count'];
	    $oldOptions = $item[""]['options'];
	    
	    $oldPrice = $item[""]['price'];
	    $totalPrice = $oldCounterProduct * $item[""]['price'];
            if ($product->getAvailability() >= $counter):
                  $cart->remove('Product_Model_Doctrine_Product', $product->getId());
                $cart->add('Product_Model_Doctrine_Product', $product->getId(), $product->Translation[$language->getId()]->name,$oldPrice, $counter, null, TRUE, $oldOptions);
              
		$flagAvailability = true;
		
		$totalPrice = $counter * $item[""]['price'];
            endif;
        }
        $items = $cart->get('Product_Model_Doctrine_Product');
       
        $productsIds = array();
        foreach($items as $productId=>$item):
            $productsIds[] = $productId;
        endforeach;
       
        if($productsIds):
            $products = $productService->getPreSortedProductCart($productsIds);
        endif;
        
        $user = $this->_helper->user();
        
        if($user):
            $userDiscount = $user['Discount']->toArray();
            $userGroups = $user->get("Groups");
        endif;

        $userGroupDiscounts = array();
        foreach($userGroups as $userGroup):
            $userGroupDiscounts[] = $userGroup['Discount']->toArray();
        endforeach;
        
        $productOldId = $product->getId();
        $totalItems = 0;
        
	
        $cart = $cartService->getCart();
       
       if (isset($_COOKIE["reference_number"])):
            $partner = $affilateProgramService->getPartner($_COOKIE["reference_number"], 'reference_number');
            if ($partner):
                $discountPartner = ($totalPrice*$partner->getDiscount())/100;
                $discountPartner = $this->view->currency($discountPartner);
            endif;
       endif; 
       
       $totalSum = $cart->getSum();
       
       $noCouponPrice = $totalSum;
       
       if (isset($_COOKIE["discount_code"])):
            $coupon = $couponService->getCoupon($_COOKIE["discount_code"], 'code');
           if ($coupon->getType() == "percent"):
               $totalSum = $totalSum - ($coupon->getAmount()*$totalSum/100);
               $couponAmount = $coupon->getAmount()."%";
           elseif ($coupon->getType() == "amount"):
               $totalSum = $totalSum - $coupon->getAmount();
               $couponAmount = $coupon->getAmount()." zł";
           endif;
        endif; 
	
	$basketItemCount = $cart->count();
        
      // $cartItemsView = $this->view->partial('cart/mini-cart.phtml', 'order', array('cartItems' => $items));
        
       $this->view->assign('noCouponPrice', $noCouponPrice." zł");
       $this->view->assign('totalPrice', $totalPrice." zł");
       $this->view->assign('totalSum', $totalSum." zł");
       $this->view->assign('productId', $productOldId);
       $this->view->assign('basketItemCount', $basketItemCount);
       $this->view->assign('discountPartner', $discountPartner);
       $this->view->assign('flagAvailability', $flagAvailability);
       $this->view->assign('oldCounterProduct', $oldCounterProduct);
       $this->view->assign('status', "success");

       $this->_helper->viewRenderer->setNoRender();
       $this->_helper->layout->disableLayout();
    }
}

