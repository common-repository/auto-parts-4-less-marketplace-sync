<?php
/**
 * The Order specific functionality of the plugin.
 */
namespace Ap4l;

include_once AP4L_DIR . 'classes/AdminFunctions.php';

use Ap4l\AdminFunctions;

if (! class_exists('OrderFunctions')) {
    class OrderFunctions extends AdminFunctions
    {
        /*
         * ==========================================
         * Initialize the class and set its properties.
         * ==========================================
         */
        public function __construct()
        {
            // NA
        }
        /*
         * ======================
         * Get AP4L ORders
         * ======================
         */
        public function GetAP4LWooOrders($orderStatusFilter = '')
        {
            $args       = array(
                'orderby'           => 'date',
                'order'             => 'DESC',
                'return'            => 'ids',
                'numberposts'       => '-1',
                'onlyAP4LOrders'    => '1',
                'orderStatusFilter' => $orderStatusFilter,
            );
            $ap4lOrders = wc_get_orders($args);
            return $ap4lOrders;
        }
        /*
         * Remove It
         * ========================
         * Get Current Page & count
         * ========================
         */
        public function GetCurrentPagePointer($sellerId)
        {
            $current_date     = current_time('Y-m-d');
            $lastInsertedDate = get_option('ap4l_last_inserted_order_date_' . $sellerId);
            $lastInsertedId   = get_option('ap4l_last_inserted_order_' . $sellerId);
//            $lastInsertedId   = 4;
            $perPageOrder     = 30;
            if ($lastInsertedDate == $current_date) {
                if (empty($lastInsertedId) || ($lastInsertedId == 0)) {
                    $currentPage = 1;
                } else {
                    $currentPage = ceil($lastInsertedId / $perPageOrder);
                }
            } else {
                $currentPage    = 1;
                $lastInsertedId = 0;
                update_option('ap4l_last_inserted_order_date_' . $sellerId, $current_date);
                update_option('ap4l_last_inserted_order_' . $sellerId, 0);
            }
            $responce = array(
                'page'    => $currentPage,
                'last'    => $lastInsertedId,
                'pointer' => $lastInsertedId - (($currentPage - 1) * $perPageOrder),
            );
            return $responce;
        }
        /*
         * ===================
         * Get Order From AP4L
         * ===================
         */
        public function GetOrdersFromAP4L($sellerAccountId, $api_para_f = [])
        {
            global $wpdb;
            $bearer = $this->getAccessToken($sellerAccountId);
            if (key_exists('accToken', $bearer)) {
                $bearer = $bearer['accToken'];
            } else {
                return;
            }
            $requestAt      = current_time('Y-m-d H:i:s');
            $totalOrdersRes = $this->elsaApiRequest('orders', $api_para_f, false, false, 'json', $bearer);
            /*
             * =======================
             * Insert Log into database
             * =======================
             */
            if (AP4L_LOG_STATUS) {
                $wpdb->insert(
                    AP4L_TABLE_PREFIX . 'orders_logs',
                    array(
                            'api_endpoint'  => 'orders',
                            'seller_id'     => $sellerAccountId,
                            'api_payload'   => json_encode($api_para_f),
                            'request_at'    => $requestAt,
                            'api_response'  => json_encode($totalOrdersRes),
                            'response_at'   => current_time('Y-m-d H:i:s'),
                            'resposne_code' => $totalOrdersRes['httpcode'],
                            'message'       => '',
                        )
                );
            }
            return $totalOrdersRes['data'];
        }
        /*
         * ==========================
         * Get Single Order From AP4L
         * ==========================
         */
        public function GetSingleOrderFromAP4L($sellerAccountId, $sellerOrderID)
        {
            global $wpdb;
            $bearer = $this->getAccessToken($sellerAccountId);
            if (key_exists('accToken', $bearer)) {
                $bearer = $bearer['accToken'];
            } else {
                return;
            }
            $api_para_s      = array(
                'seller_order_ids' => $sellerOrderID
            );
            $requestAt       = current_time('Y-m-d H:i:s');
            $singleOrdersRes = $this->elsaApiRequest('orders/full-details', $api_para_s, false, false, 'json', $bearer);
            /*
             * =======================
             * Insert Log into database
             * =======================
             */
            if (AP4L_LOG_STATUS) {
                $wpdb->insert(
                    AP4L_TABLE_PREFIX . 'orders_logs',
                    array(
                            'api_endpoint'  => 'orders/full-details',
                            'seller_id'     => $sellerAccountId,
                            'api_payload'   => json_encode($api_para_s),
                            'request_at'    => $requestAt,
                            'api_response'  => json_encode($singleOrdersRes),
                            'response_at'   => current_time('Y-m-d H:i:s'),
                            'resposne_code' => $singleOrdersRes['httpcode'],
                            'message'       => '',
                        )
                );
            }
            return $singleOrdersRes['data'];
        }
        /*
         * ====================================
         * Create Order In WooCommere From AP4L
         * ====================================
         */
        public function CreateOrderInWooFromAP4L($apiRes, $sellerAccountId, $forceProduct = 0, $increaseStock = 0)
        {
            global $wpdb;

            $ap4lOrderItems = $apiRes->order_details->ordered_items;
            $productList    = array();
            if (! empty($ap4lOrderItems)) {
                foreach ($ap4lOrderItems as $key => $value) {
                    $venderSKU  = $value->vendor_sku;
                    $product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $venderSKU));
                    if ($product_id || $forceProduct) {
                        $productList[] = array(
                            'pro_id'    => $product_id,
                            'pro_name'  => $value->name,
                            'pro_price' => $value->price,
                            'pro_qty'   => $value->quantity,
                            'tax'       => $value->tax_amount,
                            'shipping'  => $value->shipping_and_handling_charges,
                        );
                    }
                }
                if (! empty($productList)) {
                    $ap4lOrderAdd      = $apiRes->order_details->shipping_address;
                    $ap4lOrderName     = $apiRes->order_details->customer_name;
                    $ap4lOrderEmail    = $apiRes->order_details->customer_email;
                    $ap4lOrderDate     = $apiRes->order_details->purchase_date;
                    $ap4lOrderSOid     = $apiRes->order_details->seller_order_id;
                    $ap4lOrderOid      = $apiRes->order_details->order_id;
                    $ap4lOrderStatus   = $apiRes->order_details->status;
                    $ap4lOrderShipping = $apiRes->order_details->shipment_details;
                    $wooOrderAddBill   = array(
                        'first_name' => $ap4lOrderName,
                        'last_name'  => '',
                        'company'    => '',
                        'email'      => $ap4lOrderEmail,
                        'phone'      => '',
                        'address_1'  => '',
                        'address_2'  => '',
                        'city'       => '',
                        'state'      => '',
                        'postcode'   => '',
                        'country'    => ''
                    );
                    $wooOrderAddShip   = array(
                        'first_name' => $ap4lOrderAdd->first_name,
                        'last_name'  => $ap4lOrderAdd->last_name,
                        'company'    => '',
                        'email'      => '',
                        'phone'      => '',
                        'address_1'  => $ap4lOrderAdd->address_line1,
                        'address_2'  => $ap4lOrderAdd->address_line2,
                        'city'       => $ap4lOrderAdd->city,
                        'state'      => $ap4lOrderAdd->region,
                        'postcode'   => $ap4lOrderAdd->zip,
                        'country'    => $ap4lOrderAdd->country
                    );
                    $order             = wc_create_order();
                    $order->set_address($wooOrderAddBill, 'billing');
                    $order->set_address($wooOrderAddShip, 'shipping');
                    $wcOrderStatus     = 'wc-processing';

                    if ($ap4lOrderStatus === 'shipped') {
                        $wcOrderStatus = 'wc-processing';
                    }

                    if ($ap4lOrderStatus === 'delivered') {
                        $wcOrderStatus = 'wc-completed';
                    }

                    $order->set_status($wcOrderStatus);

                    foreach ($productList as $key => $value) {
                        $addProArgs = array(
                            'name'     => $value['pro_name'],
                            'subtotal' => $value['pro_price'],
                            'total'    => ($value['pro_qty'] * $value['pro_price']),
                            'quantity' => $value['pro_qty'],
                        );
                        $product    = array();
                        if (! empty($value['pro_id'])) {
                            $product = wc_get_product($value['pro_id']);
                        }
                        $order->add_product($product, $value['pro_qty'], $addProArgs);
                        $taxAmount   = $value['tax'];
                        $shippAmount = $value['shipping'];
                        if ($taxAmount > 0) {
                            $taxfee = new \WC_Order_Item_Fee();
                            $taxfee->set_name('AP4L Tax');
                            $taxfee->set_amount($taxAmount);
                            $taxfee->set_total($taxAmount);
                            $order->add_item($taxfee);
                        }
                        if ($shippAmount > 0) {
                            $shippfee = new \WC_Order_Item_Shipping();
                            $shippfee->set_method_title("AP4L Shipping");
                            $shippfee->set_total($shippAmount);
                            $order->add_item($shippfee);
                        }
                    }
                    $payment_gateways = WC()->payment_gateways->payment_gateways();
                    $order->set_payment_method($payment_gateways['ap4l_payment']);
                    $AP4Lnote         = 'Order From AP4L. Order ID: ' . $ap4lOrderSOid;
                    $order->add_order_note($AP4Lnote);
                    $order->update_meta_data('ap4l_order', 1);
                    if (! empty($ap4lOrderShipping)) {
                        $order->update_meta_data('ap4l_shipping_info', json_encode($ap4lOrderShipping));
                    }
                    $order->update_meta_data('ap4l_order_id', $ap4lOrderSOid);
                    $order->update_meta_data('ap4l_order_status', $ap4lOrderStatus);
                    $order->update_meta_data('ap4l_seller', $sellerAccountId);
                    $order->calculate_totals();

                    $ap4lOrderDate = str_replace('T', ' ', substr($ap4lOrderDate, 0, strpos($ap4lOrderDate, '.')));
                    $order->set_date_created($ap4lOrderDate);

                    $order->save();
                    $newOrderID = $order->get_id();
                    /*
                     * Add Stock QTY Back
                     * Old Orders + Sync qty False
                     */
                    if ($increaseStock === 1) {
                        wc_increase_stock_levels($newOrderID);
                    }
//                    $this->after_ap4l_order_fetched($newOrderID);
                    return $newOrderID;
                } else {
                    return 0;
                }
            }
        }
        /*
         * =======================================
         * UPdate Qty + If seller has no sync QTY
         * =======================================
         */
        public function after_ap4l_order_fetched($order_id)
        {
            if (! $order_id) {
                return;
            }
            if (! get_post_meta($order_id, '_thankyou_action_done', true)) {
                // Get an instance of the WC_Order object
                $order       = wc_get_order($order_id);
                $ap4l_order  = $order->get_meta('ap4l_order');
                $ap4l_seller = $order->get_meta('ap4l_seller');
                foreach ($order->get_items() as $item_id => $item) {
                    // Get the product object
                    $product_id    = $item->get_product_id();
                    $prodListingID = get_post_meta($product_id, 'ap4l_pro_listing', true);
                    if ($prodListingID) {
                        update_post_meta($product_id, AP4L_PRODUCT_NEEDS_UPDATE, 1);
                    }
                }
                if (! $ap4l_order || ! $ap4l_seller) {
                    return;
                }
                $sellerAccountData = $this->getAccounts($ap4l_seller);
                if (! $sellerAccountData) {
                    return;
                }

                $syncStatus = $sellerAccountData[0]->sync_quantity;

                if ($syncStatus == 0) {
                    // Loop through order items
                    foreach ($order->get_items() as $item_id => $item) {
                        // Get the product object
                        $item_quantity = $item->get_quantity();
                        $product_id    = $item->get_product_id();
                        $product       = $item->get_product();

                        $product_qty   = $product->get_stock_quantity();

                        $new_qty       = $product_qty + $item_quantity;
                        update_post_meta($product_id, '_stock', $new_qty);
                        $AP4Lnote      = 'Product quantity reverted back to :' . $new_qty . " for product ID : " . $product_id;
                        $order->add_order_note($AP4Lnote);
                    }
                }
                $order->update_meta_data('_thankyou_action_done', true);
                $order->save();
            }
        }
    }
}
