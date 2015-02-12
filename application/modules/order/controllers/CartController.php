<?php

/**
 * Order_IndexController
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_CartController extends MF_Controller_Action {
 
    public function miniCartAction(){
	$this->_helper->layout->disableLayout();
	
	
        $cartService = $this->_service->getService('Order_Service_Cart');
	
	$cart = $cartService->getCart();
        $cartItems = $cart->get('Product_Model_Doctrine_Product');
        $this->view->assign('cartItems', $cartItems);
        $this->view->assign('cart', $cart);
    }
    
    public function cartAction() {
        $cartService = $this->_service->getService('Order_Service_Cart');
        $productService = $this->_service->getService('Product_Service_Product');
        $couponService = $this->_service->getService('Order_Service_Coupon');
        $affilateProgramService = $this->_service->getService('Affilateprogram_Service_Affilateprogram');
        
        $translator = $this->_service->get('translate');
        
        $form = $cartService->getDiscountCodeForm();
        $form->getElement('discount_code')->setAttrib('placeholder', $translator->translate('Enter a coupon code'));
        
        $cart = $cartService->getCart();
        
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
        
	
	
        
        if (isset($_COOKIE["reference_number"])):
            $partner = $affilateProgramService->getPartner($_COOKIE["reference_number"], 'reference_number');
        endif;

        $count = $cart->count();

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
       
       $noCouponPrice = $totalPrice;
       
       if (isset($_COOKIE["discount_code"])):
            $coupon = $couponService->getCoupon($_COOKIE["discount_code"], 'code');
           if ($coupon->getType() == "percent"):
               $totalPrice = $totalPrice - ($coupon->getAmount()*$totalPrice/100);
               $couponAmount = $coupon->getAmount()."%";
           elseif ($coupon->getType() == "amount"):
               $totalPrice = $totalPrice - $coupon->getAmount();
               $couponAmount = $coupon->getAmount()." zł";
           endif;
       endif; 
       
       if ($partner):
            $discountPartner = ($totalPrice*$partner->getDiscount())/100;
       endif;
       
       $this->view->assign('userDiscount', $userDiscount);
       $this->view->assign('userGroupDiscounts', $userGroupDiscounts);
               
       $this->view->assign('items', $items);
       $this->view->assign('count', $count);
       $this->view->assign('products', $products);
       $this->view->assign('totalPrice', $totalPrice);
       $this->view->assign('noCouponPrice', $noCouponPrice);
       $this->view->assign('coupon', $coupon);
       $this->view->assign('partner', $partner);
       $this->view->assign('discountPartner', $discountPartner);
       $this->view->assign('form', $form);
       $this->view->assign('hideSlider', true);
       $this->view->assign('coupon',$coupon);
        
       $this->_helper->actionStack('layout', 'index', 'default');
        
    }
    
    public function checkoutAction() {
        $cartService = $this->_service->getService('Order_Service_Cart');
        $productService = $this->_service->getService('Product_Service_Product');
        $couponService = $this->_service->getService('Order_Service_Coupon');
        $orderService = $this->_service->getService('Order_Service_Order');
        $deliveryTypeService = $this->_service->getService('Order_Service_DeliveryType');
        $paymentTypeService = $this->_service->getService('Order_Service_PaymentType');
        $userService = $this->_service->getService('User_Service_User');
        $locationService = $this->_service->getService('Default_Service_Location');
        $auth = $this->_service->getService('User_Service_Auth');
        $affilateProgramService = $this->_service->getService('Affilateprogram_Service_Affilateprogram');
        
        $translator = $this->_service->get('translate');

        $loginForm = new User_Form_Login();
        $loginForm->setElementDecorators(User_BootstrapForm::$bootstrapElementDecorators);
        //$loginForm->getElement('remember')->setDecorators(User_BootstrapForm::$bootstrapSubmitDecorators);
        $loginForm->getElement('submit')->setAttrib('class','button_type_5 m_bottom_10 m_right_20 tr_all color_blue transparent fs_medium r_corners');
        $loginForm->getElement('submit')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
        $loginForm->getElement('username')->setAttrib('class', 'r_corners color_grey w_full fw_light');
        $loginForm->getElement('username')->setAttrib('placeholder', 'Email');
        $loginForm->getElement('username')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
        $loginForm->getElement('password')->setAttrib('class', 'r_corners color_grey w_full fw_light');
        $loginForm->getElement('password')->setAttrib('placeholder', $translator->translate('Password'));
        $loginForm->getElement('password')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
        $loginForm->getElement('remember')->setAttrib('class','d_none');
        $loginForm->getElement('remember')->removeDecorator(array('ElementWrapper','Wrapper','Label','DtDdWrapper','Description'));
	
        $orderForm = $userService->getOrderForm();
        //$orderForm->getElement('province')->setMultiOptions($locationService->getProvinceSelectOptions());
        $orderForm->getElement('country')->setMultiOptions($locationService->getCountrySelectOptions());
	$orderForm->getElement('country')->setValue('Poland');
        
        $orderForm->getElement('name')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
        $orderForm->getElement('name')->setAttrib('placeholder', 'np. Jan Kowalski');
	$orderForm->getElement('name')->setAttrib('class', 'r_corners fw_light color_grey w_full');
	
	$orderForm->getElement('country')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
	$orderForm->getElement('country')->setAttrib('class', 'bg_light select_title r_corners fw_light color_grey');
	
        $orderForm->getElement('postal_code')->setAttrib('placeholder', 'np. 56-200');
        $orderForm->getElement('postal_code')->setAttrib('class', 'r_corners fw_light color_grey w_full fe_width_1');
        $orderForm->getElement('postal_code')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
	
        $orderForm->getElement('address')->setAttrib('placeholder', 'np. Śliska 25');
        $orderForm->getElement('address')->setAttrib('class', 'r_corners fw_light color_grey w_full');
        $orderForm->getElement('address')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
	
	$orderForm->getElement('city')->setAttrib('placeholder', 'np. Wrocław');
        $orderForm->getElement('city')->setAttrib('class', 'r_corners fw_light color_grey w_full');
        $orderForm->getElement('city')->removeDecorator(array('ElementWrapper','Wrapper','Label'));

	$orderForm->getElement('contact_email')->setAttrib('placeholder', 'np. pnowak@gmail.com');
        $orderForm->getElement('contact_email')->setAttrib('class', 'r_corners fw_light color_grey fe_width_2 w_xs_full');
        $orderForm->getElement('contact_email')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
	
	$orderForm->getElement('contact_number')->setAttrib('placeholder', 'np. 500300600');
        $orderForm->getElement('contact_number')->setAttrib('class', 'r_corners fw_light color_grey fe_width_2 w_xs_full');
        $orderForm->getElement('contact_number')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
        
        $orderForm->getElement('attention')->setAttrib('class', 'height_2 h_max_100');
        
        $orderForm->getElement('invoice')->setAttrib('class', 'm_right_10 m_top_8 m_xs_top_0 d_none');
        $orderForm->getElement('invoice')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
	
	$orderForm->getElement('invoice_company_name')->setAttrib('placeholder', 'np. Microsoft sp.z o.o.');
        $orderForm->getElement('invoice_company_name')->setAttrib('class', 'r_corners fw_light color_grey w_xs_full');
        $orderForm->getElement('invoice_company_name')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
        
	
	$orderForm->getElement('invoice_city')->setAttrib('placeholder', 'np. Poznań');
        $orderForm->getElement('invoice_city')->setAttrib('class', 'r_corners fw_light color_grey w_xs_full');
        $orderForm->getElement('invoice_city')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
		
		
	$orderForm->getElement('invoice_address')->setAttrib('placeholder', 'np. Krakowska 25');
        $orderForm->getElement('invoice_address')->setAttrib('class', 'r_corners fw_light color_grey w_xs_full');
        $orderForm->getElement('invoice_address')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
		
		
	$orderForm->getElement('invoice_postal_code')->setAttrib('placeholder', 'np. Microsoft sp.z o.o.');
        $orderForm->getElement('invoice_postal_code')->setAttrib('class', 'r_corners fw_light color_grey w_xs_full fe_width_1');
        $orderForm->getElement('invoice_postal_code')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
		
		
	$orderForm->getElement('invoice_nip')->setAttrib('placeholder', 'np. 2998033475');
        $orderForm->getElement('invoice_nip')->setAttrib('class', 'r_corners fw_light color_grey w_xs_full');
        $orderForm->getElement('invoice_nip')->removeDecorator(array('ElementWrapper','Wrapper','Label'));
        
	//$orderForm->getElement('delivery_type_id')->getDeco();
	$orderForm->getElement('delivery_type_id')->setMultiOptions($deliveryTypeService->getTargetDeliveryTypeSelectOptionsSorted());
        $orderForm->getElement('delivery_type_id')->setAttrib('class','d_inline_m m_right_15 m_bottom_3 fw_light');
	$orderForm->getElement('delivery_type_id')->removeDecorator(array('HtmlTag'));
	
	$orderForm->getElement('payment_type_id')->setMultiOptions($paymentTypeService->getTargetPaymentTypeRadioOptions());
        
	
	
        $options = $this->getFrontController()->getParam('bootstrap')->getOptions();
        $contactEmail = $options['reply_email'];
        
        $cart = $cartService->getCart();

        $user = $this->_helper->user();
        if($user):
            $userDiscount = $user['Discount']->toArray();
            $userGroups = $user->get("Groups");
        endif;

        $userGroupDiscounts = array();
        foreach($userGroups as $userGroup):
            $userGroupDiscounts[] = $userGroup['Discount']->toArray();
        endforeach;
        
        if ($user){
            $orderForm->removeElement('contact_email');
        }
        
        if($this->getRequest()->isPost()) {
            $dataPost = $this->getRequest()->getParams();
            if ($dataPost['type'] == 'order-form'){
                if ($dataPost['other_address'] == 1 || $dataPost['other_address'] == NULL):
                    $orderForm->getElement('name')->setRequired();
                    $orderForm->getElement('city')->setRequired();
                    $orderForm->getElement('address')->setRequired();
                    $orderForm->getElement('postal_code')->setRequired();
                    $address = true;
                endif;
                if ($dataPost['delivery_type_id']):
                    $deliveryType = $deliveryTypeService->getDeliveryType($dataPost['delivery_type_id']);
                    if ($deliveryType->getType() == "przelew"){
                        $orderForm->getElement('payment_type_id')->setRequired();
                    }
                endif;
                if ($dataPost['invoice']):
                    $orderForm->getElement('invoice_company_name')->setRequired();
                    $orderForm->getElement('invoice_city')->setRequired();
                    $orderForm->getElement('invoice_address')->setRequired();
                    $orderForm->getElement('invoice_postal_code')->setRequired();
                    $orderForm->getElement('invoice_nip')->setRequired();
                    $address = true;
                endif;
                if($orderForm->isValid($this->getRequest()->getPost()) && $cart->get('Product_Model_Doctrine_Product') != NULL) {
                    try {                                   
                        $this->_service->get('doctrine')->getCurrentConnection()->beginTransaction();
                        
                        $values = $orderForm->getValues();  
                        
                        if ($user){
                            $values['user_id'] = $user->getId();
                        }
                        
                        $items = $cart->get('Product_Model_Doctrine_Product');
                        $productsIds = array();
                        foreach($items as $productId=>$item):
                            $productsIds[] = $productId;
                        endforeach;
       
                        if($productsIds):
                            $products = $productService->getPreSortedProductCart($productsIds);
                        endif;
                        
                        // check availability
                        foreach($products as $product): 
                            foreach($items as $id=>$item):
                                if($product->getId() == $id):
                                    if ($product->getAvailability() < $item[""]['count']):
                                        throw new Order_Model_ProductAvailabilityExistsException($product->Translation[$this->view->language]->name);
                                    endif;
                                endif;
                            endforeach;
                        endforeach;
                        // end check availability
                        
                        //payment 
                        if ($deliveryType->getType() == "przelew" && $values['payment_type_id'] != NULL){
                            $valuesPayment = $values;
                            $valuesPayment['status_id'] = 3;
                            $payment = $orderService->savePaymentFromArray($valuesPayment);
                        }
                        
                        if ($deliveryType->getId() == 9 && $values['payment_type_id'] != NULL){
                            $valuesPayment = $values;
                            $valuesPayment['status_id'] = 8;
                            $payment = $orderService->savePaymentFromArray($valuesPayment);
                        }
                        // endpayment
                        
                        // address
                        if ($address):
                            $deliveryAddress = $orderService->saveDeliveryAddressFromArray($values);
                        endif;
                        // address
                        
                        // delivery
                        $valuesDelivery = $values;
                        $valuesDelivery['status_id'] = "2";
                        if ($deliveryAddress){
                            $valuesDelivery['delivery_address_id'] = $deliveryAddress->getId();
                        }
                        $delivery = $orderService->saveDeliveryFromArray($valuesDelivery);
                        // end delivery
                        
                         // invoice
                        if ($values['invoice'] == 1){
                            $invoice = $orderService->saveInvoiceFromArray($values);
                        }
                        // end invoice
                        
                        // calculate sum
                        if (isset($_COOKIE["discount_code"])):
                            $coupon = $couponService->getCoupon($_COOKIE["discount_code"], 'code');
                        endif; 
                        
                        if (isset($_COOKIE["reference_number"])):
                            $partner = $affilateProgramService->getPartner($_COOKIE["reference_number"], 'reference_number');
                        endif; 
        
                        if($payment):
                            $values['payment_id'] = $payment->getId();
                        endif;
                        if($invoice):
                            $values['invoice_id'] = $invoice->getId();
                        endif;
                        $values['delivery_id'] = $delivery->getId();
                        $values['order_status_id'] = 1;
                        
                        $order = $orderService->saveOrderFromArray($values);
                        
                        $totalPrice = 0;
                        //$relatesProducts = array();
                        foreach($products as $product): 
                            foreach($items as $id=>$item):
                                if($product->getId() == $id):
                                    $arrayDiscounts = array($product['Discount'], $product['Producer']['Discount'], $userDiscount);
                                    foreach($userGroupDiscounts as $userGroupDiscount):
                                        $arrayDiscounts[] = $userGroupDiscount;
                                    endforeach;
                                    $flag = MF_Discount::getPriceWithDiscount($product['promotion_price'], $arrayDiscounts);
                                    $flag = $flag['flag'];
                                    $valuesItem = array();
                                    $valuesItem['order_id'] = $order->getId();
                                    $valuesItem['product_id'] = $product->getId();
                                    $valuesItem['number'] = $item[""]['count'];
                                    if($flag || $product['promotion']):
                                        if($product['promotion']):
                                            if($flag):
                                                $price = MF_Discount::getPriceWithDiscount($product['promotion_price'], $arrayDiscounts); 
                                                $valuesItem['discount_id'] = $price['discount_id'];
                                                $price = $price['price'];
                                                $valuesItem['price'] = $price;
                                                $totalPrice += $price*$item[""]['count'];
                                            else:
                                                $valuesItem['price'] = $product['promotion_price'];
                                                $totalPrice += $product['promotion_price']*$item[""]['count'];
                                            endif;
                                        elseif($flag):
                                            $price = MF_Discount::getPriceWithDiscount($product['price'], $arrayDiscounts);
                                            $valuesItem['discount_id'] = $price['discount_id'];
                                            $price = $price['price'];
                                            $valuesItem['price'] = $price;
                                            $totalPrice += $price*$item[""]['count'];
                                        endif;
                                    else:
                                        $valuesItem['price'] = $product['price'];
                                        $totalPrice += $product['price']*$item[""]['count'];
                                    endif;
                                    $product->setAvailability($product->getAvailability()-$item[""]['count']);
                                    $productService->setSetItemsAvailability($product,$item[""]['count']);
                                    $product->setOrderCounter($product->getOrderCounter()+1);
                                    $product->save();
                                    $orderService->saveItemFromArray($valuesItem);
                                    //$relatesProducts[] = $product;
                                endif;
                            endforeach;
                        endforeach;
                        
                     
                       // save relates 
                        
                        $relatesIds = array();
                        foreach($items as $id=>$item):
                            $relatesIds[$id] = $id;
                        endforeach;
                        $productService->saveRelates($relatesIds);
                        
                        // end saving relates
                        
                        // check partner
                        if ($partner):
                            $totalPrice = $totalPrice-(($totalPrice*$partner->getDiscount())/100);
                            $affilateProgramService->saveOrderForPartner($order->getId(), $partner->getId());
                        endif;
                        // end check partner
                        
                        // check coupon
                        if($coupon):
                            if (!$coupon->isUsed() && MF_Coupon::isValid($coupon)){
                                $valid = true;
                            }
                        endif;
       
                        if ($valid):
                            if ($coupon->getType() == "percent"):
                                $totalPrice = $totalPrice - ($coupon->getAmount()*$totalPrice/100);
                            endif;
                            if ($coupon->getType() == "amount"):
                                $totalPrice = $totalPrice - $coupon->getAmount();
                            endif;
                            $coupon->setOrderId($order->getId());
                            $coupon->setUsed(true);
                            if ($user):
                                $coupon->setUserId($user->getId());
                            endif;
                            $coupon->save();
                            setcookie("discount_code", null, time()-1000, '/');
                        endif;
                        // end check coupon

                        if ($totalPrice < 0):
                            $totalPrice = 0;
                        endif;
                        
                        $deliveryType = $deliveryTypeService->getDeliveryType($values['delivery_type_id']);
                        
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

                        $totalPrice = $totalPrice + $deliveryPrice;
                        
                        // end calculate sum
                      
                        $order->setTotalCost($totalPrice);
                        $order->save(); 
                        
                        $cart->clean();
                        
                        
                        
                        
                        $mail = new Zend_Mail('UTF-8');
                        $mail->setSubject($translator->translate('Thank you for your order.'));
                        if ($user):
                            $mail->addTo($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName());
                        else: 
                            $mail->addTo($values['contact_email'], $values['name']);
                        endif;
                        $mail->setReplyTo($contactEmail, 'System zamówień bodyempire.pl');
                        
                        if($deliveryType->getId() == 11):
                            $orderService->sendConfirmationMailWithPaymentByTransferPayment($mail, $order, $this->view->language, $this->view);
                        endif; 
                        if($payment && $deliveryType->getId() == 10):
                            $orderService->sendConfirmationMailWithPaymentByCourierCollection($mail, $order, $this->view->language, $this->view);
                        endif; 
                        if($payment && $deliveryType->getId() == 1):
                            $orderService->sendConfirmationMailWithPaymentByTransferWithCollection($mail, $order, $this->view->language, $this->view);
                        endif; 
                        if(!$payment):
                            $orderService->sendConfirmationMailWithPaymentByCash($mail, $order, $this->view->language, $this->view);
                        endif;
                        
                        $mail2 = new Zend_Mail('UTF-8');
                        $mail2->setSubject('bodyempire.pl - Order - '.$order->getId());
                        $mail2->addTo($contactEmail);
                        $orderService->sendConfirmationWithOrderToUs($user, $order, $mail2, $this->view);
                        
                        $this->_service->get('doctrine')->getCurrentConnection()->commit();

                        $this->_helper->redirector->gotoRoute(array('payment' => $payment['id'], 'order' => $order->getId()), 'domain-i18n:order-complete');
                    } catch(Order_Model_ProductAvailabilityExistsException $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->view->messages()->add($e->getMessage(), "test");
                    } catch(Exception $e) {
                        $this->_service->get('doctrine')->getCurrentConnection()->rollback();
                        $this->_service->get('log')->log($e->getMessage(), 4);
                    }
                }
		else{
		    var_dump($orderForm->getMessage());exit;
		}
            }
            else{
                if($loginForm->isValid($this->getRequest()->getParams())) {
                    $user = $userService->getUser($loginForm->getValue('username'), 'email');

                    if ($user && !$user->isActive()):
                        $loginForm->addErrorMessage($this->view->translate('User is not active'));
                        $loginForm->getElement('username')->markAsError();
                        $loginForm->getElement('username')->setErrors(array($this->view->translate('User is not active')));
                        $user = NULL;
                    else:
                        $result = $auth->authenticate($loginForm->getValue('username'), $loginForm->getValue('password'));
                        switch($result->getCode()) {
                            case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                                $loginForm->getElement('username')->markAsError();
                                $loginForm->getElement('username')->setErrors(array($this->view->translate('User not found!!!')));
                                $user = NULL;
                            break;
                            case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                                $loginForm->getElement('password')->markAsError();
                                $loginForm->getElement('password')->setErrors(array($this->view->translate('Credential invalid!!!')));
                                $user = NULL;
                            break;
                        }

                    endif;  
                }
            }
	}

        if ($user){
            $orderForm->removeElement('contact_email');
            $profile = $user->get('Profile');
        }
        
        $items = $cart->get('Product_Model_Doctrine_Product');

        if ($items == NULL){
            $this->_helper->redirector->gotoRoute(array(), 'domain-i18n:cart');
        }
        
        $orderForm->getElement('type')->setValue("order-form");
        if(!$user || !$user->getFirstName() || !$user->getLastName() || !$profile->getProvince() || 
                !$profile->getCity() || !$profile->getAddress() || !$profile->getPostalCode()){
            $orderForm->removeElement('other_address');
            if ($user){
                if ($user->getFirstName() && $user->getLastName()){
                    $orderForm->getElement('name')->setValue($user->getFirstName()." ".$user->getLastName());
                }
                if ($profile->getProvince()){
                    $orderForm->getElement('province')->setValue($profile->getProvince());
                }
                if ($profile->getCountry()){
                    $orderForm->getElement('country')->setValue($profile->getCountry());
                }
                if ($profile->getCity()){
                    $orderForm->getElement('city')->setValue($profile->getCity());
                }
                if ($profile->getAddress()){
                    $orderForm->getElement('address')->setValue($profile->getAddress());
                }
                if ($profile->getPostalCode()){
                    $orderForm->getElement('postal_code')->setValue($profile->getPostalCode());
                }

                if ($profile->getPhone()){
                    $orderForm->getElement('contact_number')->setValue($profile->getPhone());
                }
            }
        }

        // invoice data
        if ($user):
            if ($profile->getCompanyName()){
                $orderForm->getElement('invoice_company_name')->setValue($profile->getCompanyName());
            }
            if ($profile->getCity()){
                $orderForm->getElement('invoice_city')->setValue($profile->getCity());
            }
            if ($profile->getAddress()){
                $orderForm->getElement('invoice_address')->setValue($profile->getAddress());
            }
            if ($profile->getPostalCode()){
                $orderForm->getElement('invoice_postal_code')->setValue($profile->getPostalCode());
            }
            if ($profile->getNip()){
                $orderForm->getElement('invoice_nip')->setValue($profile->getNip());
            }
         endif;
        // end invoice data     
 
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
        
        if (isset($_COOKIE["discount_code"])):
            $coupon = $couponService->getCoupon($_COOKIE["discount_code"], 'code');
        endif; 
        
        if (isset($_COOKIE["reference_number"])):
            $partner = $affilateProgramService->getPartner($_COOKIE["reference_number"], 'reference_number');
        endif; 
        
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

        $noCouponPrice = $totalPrice;

       if ($partner):
           $discountPartner = ($totalPrice*$partner->getDiscount())/100;
           $totalPrice = $totalPrice-(($totalPrice*$partner->getDiscount())/100);
       endif;
       
       $valid = false;
       if($coupon):
            if (!$coupon->isUsed() && MF_Coupon::isValid($coupon)){
                $valid = true;
            }
       endif;
       
       if ($valid):
           if ($coupon->getType() == "percent"):
               $couponValue = $coupon->getAmount()." %";
               $totalPrice = $totalPrice - ($totalPrice*$couponValue/100);
           endif;
           if ($coupon->getType() == "amount"):
               $couponValue = $coupon->getAmount()." zł";
               $totalPrice = $totalPrice - $couponValue;
           endif;
       endif;
       
       if ($totalPrice < 0):
           $totalPrice = 0;
       endif;
       
       $this->view->assign('userDiscount', $userDiscount);
       $this->view->assign('userGroupDiscounts', $userGroupDiscounts);
               
       $this->view->assign('items', $items);
       $this->view->assign('products', $products);
       $this->view->assign('totalPrice', $totalPrice);
        $this->view->assign('couponValue', $couponValue);
        $this->view->assign('noCouponPrice', $noCouponPrice);
       $this->view->assign('coupon', $coupon);
       $this->view->assign('orderForm', $orderForm);
       $this->view->assign('loginForm', $loginForm);
       $this->view->assign('user', $user);
       $this->view->assign('profile', $profile);
       $this->view->assign('partner', $partner);
       $this->view->assign('discountPartner', $discountPartner);
        $this->view->assign('hideSlider', true);
        
       $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function orderCompleteAction() {
        $payUService = $this->_service->getService('Order_Service_Payment_PayU');
        $orderService = $this->_service->getService('Order_Service_Order');

        $orderId = (int) $this->getRequest()->getParam('order');
        $order = $orderService->getOrder($orderId);
        
        $paymentId = (int) $this->getRequest()->getParam('payment');
//        if($paymentId):
//             $payment = $payUService->getFullPayment($paymentId);
//        
//             if ($payment->getId() == $order->getPaymentId()):
//                $options = $this->getInvokeArg('bootstrap')->getOptions();
//                if(!array_key_exists('payu', $options)) {
//                   throw new Zend_Controller_Action_Exception('An error occured');
//                }
//
//                $payUForm = new Order_Form_Payu_Payment();
//                $payUForm->setup(array(
//                  'UrlPlatnosci_pl' => $options['payu']['UrlPlatnosci_pl'],
//                  'PosId' => $this->view->cms()->setting('payuPosId'),
//                  'PosAuthKey' => $this->view->cms()->setting('payuPosAuthKey')
//                ));
//                
//                $payUForm->getElement('session_id')->setValue($payment->getId());
//                $payUForm->getElement('amount')->setValue($payment['Order']['total_cost']*100); // wartość w groszach
//                $desc = substr($payment['Order']['Items'][0]['Product']['Translation'][$this->view->language]['name'], 0, 50);
//                $desc = $desc."...";
//                $payUForm->getElement('desc')->setValue($desc);
//                $payUForm->getElement('client_ip')->setValue($_SERVER['REMOTE_ADDR']);
//                $date = new DateTime();
//                if ($payment->getTs() == NULL):
//                    $ts = $date->getTimestamp();
//                    $payment->setTs($ts);
//                    $payment->save();
//                endif;
//                $payUForm->getElement('ts')->setValue($payment->getTs());
//
//                if($payment['Order']['User']):
//                   $payUForm->getElement('first_name')->setValue($payment['Order']['User']['first_name']);
//                   $payUForm->getElement('last_name')->setValue($payment['Order']['User']['last_name']);
//                   $payUForm->getElement('email')->setValue($payment['Order']['User']['email']);
//                   $sig = md5( $this->view->cms()->setting('payuPosId') . 
//                               "" . 
//                               $payment->getId() .
//                               $this->view->cms()->setting('payuPosAuthKey') .
//                               $payment['Order']['total_cost']*100 .
//                               $desc .
//                               "" .
//                               "" .
//                               "" .
//                               $payment['Order']['User']['first_name'] .
//                               $payment['Order']['User']['last_name'] .
//                               "" .
//                               "" .
//                               "" .
//                               "" .
//                               "" .
//                               "" .
//                               $payment['Order']['User']['email'] .
//                               "" .
//                               "" .
//                               $_SERVER['REMOTE_ADDR'] .
//                               $payment->getTs() .
//                               $this->view->cms()->setting('key1')
//                           );
//                    $payUForm->getElement('sig')->setValue($sig);
//                else:
//                   $name = explode(' ', $payment['Order']['Delivery']['DeliveryAddress']['name']);
//                   $first_name = $name[0];
//                   for($i=1;$i<count($name);$i++):
//                       $last_name .= $name[$i]." ";
//                   endfor;
//                   $payUForm->getElement('first_name')->setValue($first_name);
//                   $payUForm->getElement('last_name')->setValue($last_name);
//                   $payUForm->getElement('email')->setValue($payment['Order']['contact_email']);
//                   $sig = md5( $this->view->cms()->setting('payuPosId') . 
//                               "" . 
//                               $payment->getId() .
//                               $this->view->cms()->setting('payuPosAuthKey') .
//                               $payment['Order']['total_cost']*100 .
//                               $desc .
//                               "" .
//                               "" .
//                               "" .
//                               $first_name .
//                               $last_name .
//                               "" .
//                               "" .
//                               "" .
//                               "" .
//                               "" .
//                               "" .
//                               $payment['Order']['contact_email'] .
//                               "" .
//                               "" .
//                               $_SERVER['REMOTE_ADDR'] .
//                               $payment->getTs() .
//                               $this->view->cms()->setting('key1')
//                           );
//                    $payUForm->getElement('sig')->setValue($sig);
//                endif; 
//            endif;
//        endif;
        
        
        $this->view->assign('payUForm', $payUForm);
        $this->view->assign('payment', $payment);
        $this->view->assign('hideSlider', true);
        
        $this->_helper->actionStack('layout', 'index', 'default');
    }
    
    public function orderAbroadCompleteAction() {
        $payUService = $this->_service->getService('Order_Service_Payment_PayU');
        $orderService = $this->_service->getService('Order_Service_Order');

        $orderId = (int) $this->getRequest()->getParam('order');
        $order = $orderService->getOrder($orderId);
        
        $paymentId = (int) $this->getRequest()->getParam('payment');
        if($order['Payment']['id'] == $paymentId && $paymentId):
             $payment = $payUService->getFullPayment($paymentId);
        
             if ($payment->getId() == $order->getPaymentId()):
                $options = $this->getInvokeArg('bootstrap')->getOptions();
                if(!array_key_exists('payu', $options)) {
                   throw new Zend_Controller_Action_Exception('An error occured');
                }

                $payUForm = new Order_Form_Payu_Payment();
                $payUForm->setup(array(
                  'UrlPlatnosci_pl' => $options['payu']['UrlPlatnosci_pl'],
                  'PosId' => $this->view->cms()->setting('payuPosId'),
                  'PosAuthKey' => $this->view->cms()->setting('payuPosAuthKey')
                ));
                
                $payUForm->getElement('session_id')->setValue($payment->getId());
                $payUForm->getElement('amount')->setValue($payment['Order']['total_cost']*100); // wartość w groszach
                $desc = substr($payment['Order']['Items'][0]['Product']['Translation'][$this->view->language]['name'], 0, 50);
                $desc = $desc."...";
                $payUForm->getElement('desc')->setValue($desc);
                $payUForm->getElement('client_ip')->setValue($_SERVER['REMOTE_ADDR']);
                $date = new DateTime();
                if ($payment->getTs() == NULL):
                    $ts = $date->getTimestamp();
                    $payment->setTs($ts);
                    $payment->save();
                endif;
                $payUForm->getElement('ts')->setValue($payment->getTs());

                if($payment['Order']['User']):
                   $payUForm->getElement('first_name')->setValue($payment['Order']['User']['first_name']);
                   $payUForm->getElement('last_name')->setValue($payment['Order']['User']['last_name']);
                   $payUForm->getElement('email')->setValue($payment['Order']['User']['email']);
                   $sig = md5( $this->view->cms()->setting('payuPosId') . 
                               "" . 
                               $payment->getId() .
                               $this->view->cms()->setting('payuPosAuthKey') .
                               $payment['Order']['total_cost']*100 .
                               $desc .
                               "" .
                               "" .
                               "" .
                               $payment['Order']['User']['first_name'] .
                               $payment['Order']['User']['last_name'] .
                               "" .
                               "" .
                               "" .
                               "" .
                               "" .
                               "" .
                               $payment['Order']['User']['email'] .
                               "" .
                               "" .
                               $_SERVER['REMOTE_ADDR'] .
                               $payment->getTs() .
                               $this->view->cms()->setting('key1')
                           );
                    $payUForm->getElement('sig')->setValue($sig);
                else:
                   $name = explode(' ', $payment['Order']['Delivery']['DeliveryAddress']['name']);
                   $first_name = $name[0];
                   for($i=1;$i<count($name);$i++):
                       $last_name .= $name[$i]." ";
                   endfor;
                   $payUForm->getElement('first_name')->setValue($first_name);
                   $payUForm->getElement('last_name')->setValue($last_name);
                   $payUForm->getElement('email')->setValue($payment['Order']['contact_email']);
                   $sig = md5( $this->view->cms()->setting('payuPosId') . 
                               "" . 
                               $payment->getId() .
                               $this->view->cms()->setting('payuPosAuthKey') .
                               $payment['Order']['total_cost']*100 .
                               $desc .
                               "" .
                               "" .
                               "" .
                               $first_name .
                               $last_name .
                               "" .
                               "" .
                               "" .
                               "" .
                               "" .
                               "" .
                               $payment['Order']['contact_email'] .
                               "" .
                               "" .
                               $_SERVER['REMOTE_ADDR'] .
                               $payment->getTs() .
                               $this->view->cms()->setting('key1')
                           );
                    $payUForm->getElement('sig')->setValue($sig);
                endif; 
            endif;
        endif;
        
        
        $this->view->assign('payUForm', $payUForm);
        $this->view->assign('payment', $payment);
        $this->view->assign('order', $order);
        
        $this->_helper->actionStack('layout-shop', 'index', 'default');
    }

}

