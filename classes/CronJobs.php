<?php
/**
 * The cron job specific functionality of the plugin.
 */
namespace Ap4l;

include_once AP4L_DIR . 'classes/OrderFunctions.php';
include_once AP4L_DIR . 'classes/QuantityAp4lToWp.php';

use Ap4l\OrderFunctions;
use Ap4l\QuantityAp4lToWp;

if (! class_exists('CronJobs')) {
    class CronJobs extends OrderFunctions
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
         * Add Cron Job schedular
         * ======================
         */
        public function ap4l_add_time_schedule_cron($schedules)
        {
            $schedules['ap4l_1_minute'] = array(
                'interval' => 60,
                'display'  => __('AP4L Every Minute', 'ap4l')
            );

            $schedules['ap4l_2_minutes'] = array(
                'interval' => 60 * 2,
                'display'  => __('AP4L Every 2 Minutes', 'ap4l')
            );

            $schedules['ap4l_5_minutes'] = array(
                'interval' => 60 * 5,
                'display'  => __('AP4L Every 5 Minutes', 'ap4l')
            );

            $schedules['ap4l_once_daily']  = array(
                'interval' => 60 * 60 * 24,
                'display'  => __('AP4L Once Daily', 'ap4l')
            );

            return $schedules;
        }
        /*
         * =================
         * Past Order Fetch
         * =================
         */
        public function ap4l_old_orders_cron()
        {
            global $wpdb;
            $getAccounts = $this->getAccounts(null, 1, 1);
            $this->ap4lLog('===== OLD Orders CRON =====');
            $this->ap4lLog("OLD Orders CRON Creating Time : " . current_time('Y-m-d H:i:s'));
            foreach ($getAccounts as $key => $value) {
                $sellerAccountId   = $value->id;
                $sellerAccountDays = $value->sync_order_days;
                $this->ap4lLog("Seller Account ID : " . $sellerAccountId);
                $cron_status       = get_option('ap4l_sync_order_notice_status_' . $sellerAccountId);
                $cron_from_date    = get_option('ap4l_sync_order_from_date_' . $sellerAccountId);
                $cron_cur_page     = get_option('ap4l_sync_order_cur_page_' . $sellerAccountId);
                $cron_cur_page     = ! empty($cron_cur_page) ? $cron_cur_page : 1;
                $cron_max_page     = get_option('ap4l_sync_order_max_page_' . $sellerAccountId);
                if ($cron_status && ! empty($cron_from_date)) {
                    if ($cron_cur_page <= $cron_max_page) {
                        $api_para_f      = array(
                            'page_no'   => $cron_cur_page,
                            'from_date' => date('Y-m-d', strtotime($cron_from_date)),
                            'to_date'   => date('Y-m-d', strtotime("+1 days")),
                        );
                        $totalOrdersRes  = $this->GetOrdersFromAP4L($sellerAccountId, $api_para_f);
                        $apiRes          = $totalOrdersRes->status;
                        $apiOrders       = $totalOrdersRes->orders;
                        $apiPage         = $totalOrdersRes->paging->page_no;
                        $apiTotalRecords = $totalOrdersRes->paging->total_records;
                        $maxPage         = ceil($apiTotalRecords / AP4L_PER_PAGE_ORDERS);
                        update_option('ap4l_sync_order_max_page_' . $sellerAccountId, $maxPage);
                        update_option('ap4l_sync_order_total_' . $sellerAccountId, $apiTotalRecords);
                        $addNewOrder     = array();
                        if (($apiRes == 'success') && ! empty($apiOrders)) {
                            foreach ($apiOrders as $key => $value) {
                                $sellerOrderId  = $value->seller_order_id;
                                $newOrderStatus = $value->status;
                                $wp_order_id    = $this->getWpOrderIdFrOrderIdDB($sellerOrderId);
                                if (is_null($wp_order_id) || ( $wp_order_id == 0 )) {
                                    $addNewOrder[] = $sellerOrderId;
                                }
                            }
                            if (! empty($addNewOrder)) {
                                $addOrderStr  = implode(',', $addNewOrder);
                                $this->ap4lLog("New Order ID : " . json_encode($addNewOrder));
                                //TODO : Add logs here
                                $addOrdersRes = $this->GetSingleOrderFromAP4L($sellerAccountId, $addOrderStr);
                                foreach ($addOrdersRes as $key => $value) {
//                                    if ( $key == 2 ) {
//                                        exit();
//                                    }
                                    if ($value->status === 'success') {
                                        $seller_id         = $value->seller_order_id;
                                        $sellerOrderStatus = $value->order_details->status;
                                        $increaseStock     = 1;
                                        $singleOrdersRes   = $this->CreateOrderInWooFromAP4L($value, $sellerAccountId, 1, $increaseStock);
                                        $wp_order_id       = $this->getWpOrderIdFrOrderIdDB($seller_id);
                                        if (is_null($wp_order_id)) {
                                            $wpdb->insert(
                                                AP4L_TABLE_PREFIX . 'orders',
                                                array(
                                                        'seller_order_id' => $seller_id,
                                                        'wc_order_id'     => $singleOrdersRes,
                                                        'account_id'      => $sellerAccountId,
                                                        'order_status'    => $sellerOrderStatus,
                                                        'created_at'      => current_time('Y-m-d H:i:s'),
                                                        'updated_at'      => current_time('Y-m-d H:i:s'),
                                                    )
                                            );
                                        } else {
                                            $wpdb->update(
                                                AP4L_TABLE_PREFIX . 'orders',
                                                array(
                                                        'updated_at'  => current_time('Y-m-d H:i:s'),
                                                        'wc_order_id' => $singleOrdersRes,
                                                    ),
                                                array(
                                                        'seller_order_id' => $seller_id,
                                                    )
                                            );
                                        }
                                    }
                                }
                            }
                            $next_page = ($cron_cur_page + 1);
                            update_option('ap4l_sync_order_cur_page_' . $sellerAccountId, $next_page);
                        }
                    } else {
                        //STOP CRON
                        update_option('ap4l_sync_order_notice_status_' . $sellerAccountId, 0);
                        update_option('ap4l_sync_order_from_date_' . $sellerAccountId, '');
                        update_option('ap4l_sync_order_cur_page_' . $sellerAccountId, 0);
                        update_option('ap4l_sync_order_max_page_' . $sellerAccountId, 0);
                        update_option('ap4l_sync_order_total_' . $sellerAccountId, 0);
                    }
                }
            }
        }
        /*
         * ==================
         * Get Order Cron Job
         * ==================
         */
        public function ap4l_get_order_from_api()
        {
            global $wpdb;
            $getAccounts = $this->getAccounts(null, 1, 1);
            global $wpdb;
            $this->ap4lLog('===== New Orders =====');
            $this->ap4lLog("Cron For New Orders Creating Time : " . current_time('Y-m-d H:i:s'));
            foreach ($getAccounts as $key => $value) {
                $sellerAccountId   = $value->id;
                $sellerAccountDays = $value->sync_order_days;
                $this->ap4lLog("Seller Account ID : " . $sellerAccountId);
                $sellerAccountDays = ! empty($sellerAccountDays) ? $sellerAccountDays : 60;
                $ordersFrom        = "-" . $sellerAccountDays . " days";
                global $maxPage;
                $maxPage           = 50;
                for ($page = 1; $page <= $maxPage; $page ++) {
                    $api_para_f              = array(
                        'page_no'   => $page,
                        'from_date' => date('Y-m-d', strtotime($ordersFrom)),
                        'to_date'   => date('Y-m-d', strtotime("+1 days")),
                    );
                    $totalOrdersRes          = $this->GetOrdersFromAP4L($sellerAccountId, $api_para_f);
                    if ($totalOrdersRes) {
                        $apiRes                  = $totalOrdersRes->status;
                        $apiOrders               = $totalOrdersRes->orders;
                        $apiPage                 = $totalOrdersRes->paging->page_no;
                        $apiTotalRecords         = $totalOrdersRes->paging->total_records;
                        $maxPage                 = ceil($apiTotalRecords / AP4L_PER_PAGE_ORDERS);
    //                    $maxPage                 = 1;
                        $sellerOrderMapping      = array();
                        $updateOrder             = array();
                        $addNewOrder             = array();
                        $directOrderStatusUpdate = array( 'shipped' );
                        if (($apiRes == 'success') && ! empty($apiOrders)) {
                            foreach ($apiOrders as $key => $value) {
                                $sellerOrderId  = $value->seller_order_id;
                                $newOrderStatus = $value->status;
                                $wp_order_id    = $this->getWpOrderIdFrOrderIdDB($sellerOrderId);
                                if (is_null($wp_order_id)) {
                                    $addNewOrder[] = $sellerOrderId;
                                } elseif ($wp_order_id != 0) {
                                    $sellerOrderMapping[$sellerOrderId] = $wp_order_id;
                                    update_post_meta($wp_order_id, 'ap4l_order_status', $newOrderStatus);
                                    if ($newOrderStatus === 'shipped') {
                                        $order = new \WC_Order($wp_order_id);
                                        $order->update_status('processing');
                                    }
                                    if ($newOrderStatus === 'delivered') {
                                        $order = new \WC_Order($wp_order_id);
                                        $order->update_status('completed');
                                    }
                                    if (in_array($newOrderStatus, $directOrderStatusUpdate)) {
                                        $updateOrder[] = $sellerOrderId;
                                    }
                                    $wpdb->update(
                                        AP4L_TABLE_PREFIX . 'orders',
                                        array(
                                                'updated_at'   => current_time('Y-m-d H:i:s'),
                                                'order_status' => $newOrderStatus,
                                            ),
                                        array(
                                                'seller_order_id' => $sellerOrderId,
                                            )
                                    );
                                }
                            }
                            if (! empty($updateOrder)) {
                                $updateOrderStr  = implode(',', $updateOrder);
                                $this->ap4lLog("Update Order ID : " . json_encode($updateOrder));
                                //TODO : Add logs here
                                $updateOrdersRes = $this->GetSingleOrderFromAP4L($sellerAccountId, $updateOrderStr);
                                foreach ($updateOrdersRes as $key => $value) {
                                    if ($value->status === 'success') {
                                        update_post_meta($sellerOrderMapping[$value->seller_order_id], 'ap4l_shipping_info', json_encode($value->order_details->shipment_details));
                                    }
                                }
                            }
                            if (! empty($addNewOrder)) {
                                $addOrderStr  = implode(',', $addNewOrder);
                                $this->ap4lLog("New Order ID : " . json_encode($addNewOrder));
                                //TODO : Add logs here
                                $addOrdersRes = $this->GetSingleOrderFromAP4L($sellerAccountId, $addOrderStr);
                                foreach ($addOrdersRes as $key => $value) {
                                    if ($value->status === 'success') {
                                        $seller_id         = $value->seller_order_id;
                                        $sellerOrderStatus = $value->order_details->status;
                                        $increaseStock = 0;
                                        $sellerAccountData = $this->getAccounts($sellerAccountId);
                                        if ($sellerAccountData) {
                                            $syncStatus = $sellerAccountData[0]->sync_quantity;
                                            if ($syncStatus == 0) {
                                                $increaseStock = 1;
                                            }
                                        }
                                        $singleOrdersRes   = $this->CreateOrderInWooFromAP4L($value, $sellerAccountId, 0, $increaseStock);
                                        $wpdb->insert(
                                            AP4L_TABLE_PREFIX . 'orders',
                                            array(
                                                    'seller_order_id' => $seller_id,
                                                    'wc_order_id'     => $singleOrdersRes,
                                                    'account_id'      => $sellerAccountId,
                                                    'order_status'    => $sellerOrderStatus,
                                                    'created_at'      => current_time('Y-m-d H:i:s'),
                                                    'updated_at'      => current_time('Y-m-d H:i:s'),
                                                )
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        /*
         * ==============
         * Product Cron
         * =============
         */
        public function ap4l_product_cron()
        {
            //Add your cron code here as per your need.
            $newProductstoCreate = $this->getProductsForSyncing();
            $ap4lProIDs          = $newProductstoCreate->posts;
            $this->ap4lLog('===== New Product =====');
            $this->ap4lLog("Cron For New Product Creating Time : " . current_time('Y-m-d H:i:s'));
            $this->ap4lLog("New Product IDS: " . json_encode($ap4lProIDs));
            if (! empty($ap4lProIDs)) {
                $createNewProductsInAP4L = $this->createProductsInAP4L($ap4lProIDs, true);
            }
        }
        public function ap4l_product_update_cron()
        {
            //Add your cron code here as per your need.
            $newProductstoCreate = $this->getProductsForUpdating();
            $ap4lProIDs          = $newProductstoCreate->posts;
            $this->ap4lLog('===== Update Product =====');
            $this->ap4lLog("Cron For Update Product Creating Time : " . current_time('Y-m-d H:i:s'));
            $this->ap4lLog("Update Product IDS: " . json_encode($ap4lProIDs));
            if (! empty($ap4lProIDs)) {
                $createNewProductsInAP4L = $this->createProductsInAP4L($ap4lProIDs, true);
            }
        }

        public function ap4l_delete_logs_cron()
        {
            if (!empty(AP4L_LOGS_DAYS)) {
                $this->ap4lLog("===== Remove Logs =====");

                // Listing - logs deletion - start
                $listing_logs_deleted_date = $this->removeListingLogsByDays(AP4L_LOGS_DAYS);
                $this->ap4lLog("Listing - logs deleted till the date: " . $listing_logs_deleted_date);
                // Listing - logs deletion - end

                // Order - logs deletion - start
                $order_logs_deleted_date = $this->removeOrderLogsByDays(AP4L_LOGS_DAYS);
                $this->ap4lLog("Order - logs deleted till the date: " . $order_logs_deleted_date);
                // Order - logs deletion - end

                // Product quantity sync from AP4L to WP - logs deletion - start
                $QuantityAp4lToWp = new QuantityAp4lToWp();
                $deletedDate = $QuantityAp4lToWp->removeLogs(AP4L_LOGS_DAYS);
                $this->ap4lLog("Product quantity sync from AP4L to WP - logs deleted till the date: " . $deletedDate);
                // Product quantity sync from AP4L to WP - logs deletion - end

                // File system - logs deletion - start
                $uploadDir = AP4L_DIR . 'logs';

                if (file_exists($uploadDir) && is_dir($uploadDir)) {
                    $deletedDate = date('Y-m-d', strtotime('-' . AP4L_LOGS_DAYS . ' days'));
                    $logUptoDate = str_replace('-', '', $deletedDate);
                    $files = scandir($uploadDir);

                    foreach ($files as $file) {
                        if ($file == '.' || $file == '..') {
                            continue;
                        }

                        if (str_replace('.log', '', $file) < $logUptoDate) {
                            unlink($uploadDir . '/' . $file);
                        } else {
                            break;
                        }
                    }

                    $this->ap4lLog("File system - logs deleted till the date: " . $deletedDate);
                }
                // File system - logs deletion - end
            }
        }

        // Product quantity sync from AP4L to WP
        public function ap4l_quantity_ap4l_to_wp()
        {
            $QuantityAp4lToWp = new QuantityAp4lToWp();
            $QuantityAp4lToWp->sync();
        }

        public function ap4l_queue_status_check_cron()
        {
            //Add your cron code here as per your need.
            $InQueryProducts = $this->getInQueryProducts();
            $ap4lProIDs      = $InQueryProducts->posts;
            $this->ap4lLog('===== Queue Status Check =====');
            $this->ap4lLog("Cron For Queue status checking : " . current_time('Y-m-d H:i:s'));
            $this->ap4lLog("Queue status Product IDS: " . json_encode($ap4lProIDs));
            if (! empty($ap4lProIDs)) {
                $checkQueueStatus = $this->checkQueueStatus($ap4lProIDs);
            }
        }
    }
}
