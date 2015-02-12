<?php

/**
 * Order_Model_Cart
 *
 * @author Andrzej Wilczyński <and.wilczynski@gmail.com>
 */
class Order_Model_Cart {
    
    protected $session;
    
    public function __construct() {
        $this->session = new Zend_Session_Namespace('CART');
    }
    
    public function getItems($class = null) {
        if(!$items = $this->session->items) {
            $items = array();
            $this->session->items = $items;
        }
        if(null != $class) {
            if(isset($items[$class])) {
                return $items[$class];
            }
        }
        return $items;
    }
    
    public function get($class, $id = null, $index = null) {
        $items = $this->getItems();
        if(isset($items[$class])) {
            if(null !== $id && isset($items[$class][$id])) {
                if(null !== $index && isset($items[$class][$id][$index])) {
                    return $items[$class][$id][$index];
                }
                return $items[$class][$id];
            }
            return $items[$class];
        }
    }
    
    public function add($class, $id, $name, $price, $count, $index, $absolutePrice = false, $options = array()) {
        $items = $this->getItems();
        if(!isset($items[$class])) {
            $items[$class] = array();
        }
        if(!isset($items[$class][$id])) {
            $items[$class][$id] = array();
        }
        $price = $absolutePrice ? $price : $price * $count;
        $items[$class][$id][$index] = array('name' => $name, 'price' => $price, 'count' => $count, 'index' => $index, 'absolute' => $absolutePrice, 'options' => $options);
        $this->session->items = $items;
    }
    
    public function remove($class, $id = null, $index = null) {
        $items = $this->getItems();
        if(isset($items[$class])) {
            if(null != $id) {
                if(null != $index) {
                    unset($items[$class][$id][$index]);
                } else {
                    unset($items[$class][$id]);
                }
            } else {
                unset($items[$class]);
            }
        }
        $this->session->items = $items;
    }
    
    public function count($class = null, $id = null, $index = null) {
        $items = $this->getItems();

        $result = 0;
        
        if(null == $class) {
            foreach($items as $class => $ids) {
                foreach($ids as $id => $indexes) {
                    foreach($indexes as $index => $data) {
                        if(isset($data['count'])) {
                            $result += (int) $data['count'];
                        }
                    }
                }
            }
        } else {
            if(isset($items[$class])) {
                if(null == $id) {
                    if(null != $index) {
                        foreach($items[$class][$id] as $indexes) {
                            foreach($indexes as $index => $data) {
                                $result += (int) $data['count'];
                            }
                        }
                    } else {
                        foreach($items[$class] as $ids) {
                            foreach($ids as $id => $indexes) {
                                foreach($indexes as $index => $data) {
                                    $result += (int) $data['count'];
                                }
                            }
                        }
                    }
                } else {
                    if(isset($items[$class][$id])) {
                        $result += $items[$class][$id]['count'];
                    }
                }
            }
        }
        return $result;
    }
    
    public function getSum() {
        $items = $this->getItems();
        
        $result = 0;
        foreach($items as $class => $ids) {
            foreach($ids as $id => $indexes) {
                foreach($indexes as $index => $data) {
                    if(isset($data['price'])) {
                        if(isset($data['count']) && $data['absolute']) {
                            $result += $data['count'] * $data['price'];
                        } else {
                            $result += $data['price'];
                        }
                    }
                }
            }
        }
        
        return $result;
    }
    
    public function clean() {
        $this->session->items = array();
    }
}

