<?php
/**
 * This class defines all functions which are user redundantly.
 */
namespace Ap4l;

if (! class_exists('UserModal')) {
    class UserModal
    {
        private $categoryList = false;

        public function __construct()
        {
            // NA
        }
        public function sanitizeValue($text)
        {
            $text = trim(str_replace(" ", "-", strtolower($text)));
            return $text;
        }
        public function getTimeZone($time)
        {
            return date('T', strtotime($time));
        }
        public function getTimeFormated($time)
        {
            return date('M d, Y, H:i:s A T', strtotime($time));
        }

        public function getCategoryList()
        {
            global $wpdb;

            if (empty($this->categoryList)) {
                $categoryListQuery = "SELECT * FROM " . AP4L_TABLE_PREFIX . "cat_mapping_attributes order by category_name";
                $this->categoryList = $wpdb->get_results($categoryListQuery);
            }

            return $this->categoryList;
        }

        public function getCategorySelectBox($termId, $selectedCat = '')
        {
            $categoryList = $this->getCategoryList();
            ?>

            <select class="catMapBoxSelect" name="<?php echo esc_attr('mapping' . $termId); ?>">
                <option value="">Select AP4L Category</option>

                <?php foreach ($categoryList as $key => $value) : ?>
                <option value="<?php echo esc_attr($value->category_id); ?>" <?php selected($selectedCat, $value->category_id); ?>><?php echo esc_html($value->category_name); ?></option>
                <?php endforeach; ?>
            </select>

            <?php
        }

        public function createWhereCondition($whereClause)
        {
            $wheretext = '';
            if (count($whereClause) > 0) {
                $whereClauseText = array_map(function ($key, $val) {
                    return $key . " = " . $val;
                }, array_keys($whereClause), $whereClause);
                $wheretext = " WHERE " . implode(" AND ", $whereClauseText);
            }
            return $wheretext;
        }
        public function getWpOrderIdFrOrderId($SellerOrderId)
        {
            global $wpdb;
            $mainQuery = "SELECT post_id FROM " . $wpdb->postmeta . " WHERE meta_key='ap4l_order_id' AND meta_value='" . $SellerOrderId . "' LIMIT 1";
            $wpOrderId = $wpdb->get_var($mainQuery);
            return $wpOrderId;
        }
        public function getWpOrderIdFrOrderIdDB($SellerOrderId)
        {
            global $wpdb;
            $mainQuery = "SELECT wc_order_id FROM " . AP4L_TABLE_PREFIX . "orders WHERE seller_order_id='" . $SellerOrderId . "' LIMIT 1";
            $wpOrderId = $wpdb->get_var($mainQuery);
            return $wpOrderId;
        }
        public function getAllOrderLogs($logId = '', $accountId = '', $apiEnd = '', $apiRes = '', $searchword = '')
        {
            global $wpdb;

            $mainQuery = "SELECT table1.* FROM " . AP4L_TABLE_PREFIX . "orders_logs AS table1 ";
            $mainQuery .= "LEFT JOIN " . AP4L_TABLE_PREFIX . "accounts AS table2 ";
            $mainQuery .= "ON table1.seller_id = table2.id ";
            $whereClause = [];

            if (!empty($logId)) {
                $whereClause['table1.id'] = $logId;
            }

            if (!empty($accountId)) {
                $whereClause['table1.seller_id'] = $accountId;
            }

            if (!empty($apiEnd)) {
                $whereClause['table1.api_endpoint'] = "'" . $apiEnd . "'";
            }

            if (!empty($apiRes)) {
                $whereClause['table1.resposne_code'] = $apiRes;
            }

            $mainQuery .= $this->createWhereCondition($whereClause);

            if ($searchword) {
                if (empty($whereClause)) {
                    $mainQuery .= ' WHERE ';
                } else {
                    $mainQuery .= ' AND ';
                }

                $mainQuery .= "(table2.title LIKE '%{$searchword}%' OR table1.request_at LIKE '%{$searchword}%' OR table1.api_response LIKE '%{$searchword}%' OR table1.api_endpoint LIKE '%{$searchword}%' OR table1.resposne_code LIKE '%{$searchword}%' )";
            }

            $mainQuery .= " ORDER BY table1.id DESC";
            $wpOrderId  = $wpdb->get_results($mainQuery);

            return $wpOrderId;
        }
        public function getAllListingLogs($logId = '', $listingId = '', $proId = '', $apiEnd = '', $apiRes = '', $searchword = '')
        {
            global $wpdb;
            $mainQuery   = "SELECT table1.* FROM " . AP4L_TABLE_PREFIX . "listing_logs AS table1 ";
            $mainQuery .= "LEFT JOIN " . AP4L_TABLE_PREFIX . "listings AS table2 ";
            $mainQuery .= "ON table1.listing_id = table2.id ";
            $mainQuery .= "LEFT JOIN " . $wpdb->prefix . "posts AS table3 ";
            $mainQuery .= "ON table1.product_id = table3.ID ";
            $whereClause = [];

            if (!empty($logId)) {
                $whereClause['table1.id'] = $logId;
            }

            if (!empty($listingId)) {
                $whereClause['table1.listing_id'] = $listingId;
            }

            if (!empty($proId)) {
                $whereClause['table1.product_id'] = $proId;
            }

            if (!empty($apiEnd)) {
                $whereClause['table1.api_endpoint'] = "'" . $apiEnd . "'";
            }

            if (!empty($apiRes)) {
                $whereClause['table1.resposne_code'] = $apiRes;
            }

            $mainQuery .= $this->createWhereCondition($whereClause);

            if ($searchword) {
                if (empty($whereClause)) {
                    $mainQuery .= ' WHERE ';
                } else {
                    $mainQuery .= ' AND ';
                }

                $mainQuery .= "(table3.post_title LIKE '%{$searchword}%' OR table2.listing_name LIKE '%{$searchword}%' OR table1.request_at LIKE '%{$searchword}%' OR table1.listing_id LIKE '%{$searchword}%' OR table1.api_endpoint LIKE '%{$searchword}%' OR table1.resposne_code LIKE '%{$searchword}%' )";
            }

            $mainQuery .= " order by table1.id DESC";
            $wpOrderId  = $wpdb->get_results($mainQuery);

            return $wpOrderId;
        }

        public function removeAccountAll($accID)
        {
            global $wpdb;
            $accounts = 0;
            if (! empty($accID)) {
                $mainQuery                        = "SELECT * FROM " . AP4L_TABLE_PREFIX . "listings";
                $whereClause                      = [];
                $whereClause['seller_account_id'] = $accID;
                $mainQuery                        .= $this->createWhereCondition($whereClause);
                $allListing                       = $wpdb->get_results($mainQuery);
                if (! empty($allListing)) {
                    foreach ($allListing as $key => $value) {
                        $polDltRes = $this->removeListingAll($value->id);
                    }
                }
                $accounts = $wpdb->delete(
                    AP4L_TABLE_PREFIX . 'accounts',
                    array( 'id' => $accID, ),
                );
                $accounts = $wpdb->delete(
                    AP4L_TABLE_PREFIX . 'orders_logs',
                    array( 'seller_id' => $accID, ),
                );
            }
            return $accounts;
        }
        public function removeListingAll($LisID)
        {
            global $wpdb;
            $found_posts = $this->getListingProducts($LisID);
            if (! empty($found_posts)) {
                foreach ($found_posts as $key => $proId) {
                    $polDltRes1 = $wpdb->delete(
                        $wpdb->prefix . 'postmeta',
                        array( 'post_id' => $proId, 'meta_key' => 'ap4l_pro_listing' ),
                    );
                    $polDltRes2 = $wpdb->delete(
                        $wpdb->prefix . 'postmeta',
                        array( 'post_id' => $proId, 'meta_key' => 'ap4l_pro_needs_update' ),
                    );
                    $polDltRes3 = $wpdb->delete(
                        $wpdb->prefix . 'postmeta',
                        array( 'post_id' => $proId, 'meta_key' => AP4L_PRODUCT_SYNCED ),
                    );
                    $polDltRes4 = $wpdb->delete(
                        $wpdb->prefix . 'postmeta',
                        array( 'post_id' => $proId, 'meta_key' => 'ap4l_pro_listing_date' ),
                    );
                    $polDltRes4 = $wpdb->delete(
                        $wpdb->prefix . 'postmeta',
                        array( 'post_id' => $proId, 'meta_key' => 'ap4l_pro_exists_ap4l' ),
                    );
                    $polDltRes4 = $wpdb->delete(
                        $wpdb->prefix . 'postmeta',
                        array( 'post_id' => $proId, 'meta_key' => 'ap4l_pro_queue_id' ),
                    );
                    $polDltRes4 = $wpdb->delete(
                        $wpdb->prefix . 'postmeta',
                        array( 'post_id' => $proId, 'meta_key' => 'ap4l_product_status' ),
                    );
                }
            }
            $polDltRes  = $wpdb->delete(
                AP4L_TABLE_PREFIX . 'listings',
                array( 'id' => $LisID, ),
            );
            $polDltLogs = $wpdb->delete(
                AP4L_TABLE_PREFIX . 'listing_logs',
                array( 'listing_id' => $LisID, ),
            );
            return $polDltRes;
        }
        public function removeOrderLogs($ids)
        {
            global $wpdb;

            $logRemoveQuery = "DELETE FROM " . AP4L_TABLE_PREFIX . "orders_logs WHERE id IN (" . $ids . ")";
            $delete_result = $wpdb->get_results($logRemoveQuery);
            return $delete_result;
        }
        public function removeListingLogs($ids)
        {
            global $wpdb;

            $logRemoveQuery = "DELETE FROM " . AP4L_TABLE_PREFIX . "listing_logs WHERE id IN (" . $ids . ")";
            $delete_result = $wpdb->get_results($logRemoveQuery);
            return $delete_result;
        }

        public function removeListingLogsByDays($days)
        {
            global $wpdb;
            $listing_logs_deleted_date = '';

            $listingLogsCountQuery = "SELECT COUNT(*) AS cnt, MAX(DATE(request_at)) AS listing_logs_deleted_date FROM " . AP4L_TABLE_PREFIX . "listing_logs WHERE DATE(request_at) <= DATE_SUB(CURDATE(), INTERVAL " . $days . " DAY)";
            $listingLogsCountRow = $wpdb->get_row($listingLogsCountQuery);
            $listingLogsCount = $listingLogsCountRow->cnt;

            if (!empty($listingLogsCount) && !empty($listingLogsCountRow->listing_logs_deleted_date)) {
                $listing_logs_deleted_date = $listingLogsCountRow->listing_logs_deleted_date;

                $listingLogsRemoveQuery = "DELETE FROM " . AP4L_TABLE_PREFIX . "listing_logs WHERE DATE(request_at) <= DATE_SUB(CURDATE(), INTERVAL " . $days . " DAY)";
                $listingLogsRemovedCount = $wpdb->query($listingLogsRemoveQuery);

                if ($listingLogsCount != $listingLogsRemovedCount) {
                    $this->ap4lLog("Listing logs deleted count defer: " . $listingLogsCount . ' != ' . $listingLogsRemovedCount);
                }
            }

            return $listing_logs_deleted_date;
        }

        public function removeOrderLogsByDays($days)
        {
            global $wpdb;
            $order_logs_deleted_date = '';

            $orderLogsCountQuery = "SELECT COUNT(*) AS cnt, MAX(DATE(request_at)) AS order_logs_deleted_date FROM " . AP4L_TABLE_PREFIX . "orders_logs WHERE DATE(request_at) <= DATE_SUB(CURDATE(), INTERVAL " . $days . " DAY)";
            $orderLogsCountRow = $wpdb->get_row($orderLogsCountQuery);
            $orderLogsCount = $orderLogsCountRow->cnt;

            if (!empty($orderLogsCount) && !empty($orderLogsCountRow->order_logs_deleted_date)) {
                $order_logs_deleted_date = $orderLogsCountRow->order_logs_deleted_date;

                $orderLogsRemoveQuery = "DELETE FROM " . AP4L_TABLE_PREFIX . "orders_logs WHERE DATE(request_at) <= DATE_SUB(CURDATE(), INTERVAL " . $days . " DAY)";
                $orderLogsRemovedCount = $wpdb->query($orderLogsRemoveQuery);

                if ($orderLogsCount != $orderLogsRemovedCount) {
                    $this->ap4lLog("Order logs deleted count defer: " . $orderLogsCount . ' != ' . $orderLogsRemovedCount);
                }
            }

            return $order_logs_deleted_date;
        }

        public function getAccounts($id = null, $status = null, $syncOrder = null)
        {
            global $wpdb;
            $mainQuery   = "SELECT * FROM " . AP4L_TABLE_PREFIX . "accounts";
            $whereClause = [];
            if ($id) {
                $whereClause['id'] = $id;
            }
            if ($status !== null) {
                $whereClause['is_active'] = $status;
            }
            if ($syncOrder !== null) {
                $whereClause['sync_orders'] = $syncOrder;
            }
            $mainQuery .= $this->createWhereCondition($whereClause);
            $mainQuery .= " order by title";
            $accounts  = $wpdb->get_results($mainQuery);
            return $accounts;
        }
        public function getAP4LOrders($orderStatusFilter = '', $accountId = '', $searchword = '')
        {
            global $wpdb;
            $mainQuery   = "SELECT * FROM " . AP4L_TABLE_PREFIX . "orders AS table1 ";
            $mainQuery .= "JOIN " . AP4L_TABLE_PREFIX . "accounts AS table2 ";
            $mainQuery .= "ON table1.account_id = table2.id ";
            $mainQuery .= "JOIN " . AP4L_TABLE_PREFIX . "accounts AS table3 ";
            $mainQuery .= "ON table1.account_id = table3.id ";
            $mainQuery .= "JOIN " . $wpdb->prefix . "wc_order_stats AS table4 ";
            $mainQuery .= "ON table1.wc_order_id = table4.order_id ";
            $mainQuery .= "JOIN " . $wpdb->prefix . "wc_customer_lookup AS table5 ";
            $mainQuery .= "ON table4.customer_id = table5.customer_id ";
            $mainQuery .= "WHERE";
            $whereClause = [];
            if (! empty($orderStatusFilter)) {
                $mainQuery .= " order_status = '" . $orderStatusFilter . "' AND";
            }
            if (! empty($accountId)) {
                $mainQuery .= " account_id = '" . $accountId . "' AND";
            }
            if (! empty($searchword)) {
                $mainQuery .= " (table5.first_name LIKE '%{$searchword}%' OR table5.last_name LIKE '%{$searchword}%' OR net_total LIKE '%{$searchword}%' OR table2.title LIKE '%{$searchword}%' OR seller_order_id LIKE '%{$searchword}%' OR wc_order_id LIKE '%{$searchword}%' OR table1.created_at LIKE '%{$searchword}%' OR order_status LIKE '%{$searchword}%' ) AND";
            }
            $mainQuery .= " wc_order_id != 0 order by table1.id";
            $accounts  = $wpdb->get_results($mainQuery);
            return $accounts;
        }
        public function get_orders_ids_by_product_id($product_id)
        {
            global $wpdb;
            // Define HERE the orders status to include in  <==  <==  <==  <==  <==  <==  <==
            $orders_statuses = "'wc-completed', 'wc-processing', 'wc-on-hold'";
            # Get All defined statuses Orders IDs for a defined product ID (or variation ID)
            return $wpdb->get_col("
                SELECT DISTINCT woi.order_id
                FROM {$wpdb->prefix}woocommerce_order_itemmeta as woim,
                     {$wpdb->prefix}woocommerce_order_items as woi,
                     {$wpdb->prefix}posts as p
                WHERE  woi.order_item_id = woim.order_item_id
                AND woi.order_id = p.ID
                AND p.post_status IN ( $orders_statuses )
                AND woim.meta_key IN ( '_product_id', '_variation_id' )
                AND woim.meta_value LIKE '$product_id'
                ORDER BY woi.order_item_id DESC");
        }
        public function getProductUPC($proID)
        {
            global $wpdb;
            $upcValue = '';
            $listing_id = get_post_meta($proID, 'ap4l_pro_listing', true);
            if ($listing_id) {
                $ProductPolicies = $this->getListing($listing_id, 1);
                $ProductPolicies   = $ProductPolicies[0];
                $sellingPolicyID = $ProductPolicies->seller_policy_id;
                $mainQuery   = "SELECT * FROM " . AP4L_TABLE_PREFIX . "selling_policy";
                $whereClause = [];
                $whereClause['main_policy_id'] = $sellingPolicyID;
                $whereClause['product_attribute_id'] = 1;
                $mainQuery .= $this->createWhereCondition($whereClause);
                $upcDetail  = $wpdb->get_results($mainQuery);
                if ($upcDetail) {
                    if ($upcDetail[0]->static_value) {
                        $upcValue = $upcDetail[0]->static_value;
                    } else {
                        $upcValue = get_post_meta($proID, $upcDetail[0]->woocommerce_attribute, true);
                    }
                }
            }
            return $upcValue;
        }
        public function getListingProducts($listingId, $ap4lProOnly = false, $searchword = '')
        {
            global $wpdb;
            $searchProRes = array();
            $args = array(
                'post_type'      => AP4L_PRODUCT_POSTTYPE,
                'fields'         => 'ids',
                'posts_per_page' => '-1',
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'ap4l_pro_listing',
                        'value'   => $listingId,
                        'compare' => '=',
                    ),
                ),
            );
            if ($ap4lProOnly) {
                $args['meta_query'][] = array(
                   'key'     => 'ap4l_product_status',
                    'value'   => 'stop',
                    'compare' => '!=',
                );
            }
            if ($searchword) {
                $args['meta_query'][1]['relation'] = 'OR';
                $args['meta_query'][1][] = array(
                   'key'     => '_sku',
                    'value'   => $searchword,
                    'compare' => 'LIKE',
                );
                $args['meta_query'][1][] = array(
                   'key'     => '_stock',
                    'value'   => $searchword,
                    'type'    => 'numeric',
                    'compare' => '=',
                );
                $args['meta_query'][1][] = array(
                   'key'     => '_price',
                    'value'   => $searchword,
                    'type'    => 'numeric',
                    'compare' => '=',
                );
                $args['meta_query'][1][] = array(
                   'key'     => 'ap4l_pro_listing_date',
                    'value'   => $searchword,
                    'compare' => 'LIKE',
                );
                $args1 = array(
                    'post_type'      => AP4L_PRODUCT_POSTTYPE,
                    'fields'         => 'ids',
                    'posts_per_page' => '-1',
                    's'              => $searchword,
                    'meta_query'     => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'ap4l_pro_listing',
                            'value'   => $listingId,
                            'compare' => '=',
                        ),
                    ),
                );
                $SearchPro = new \WP_Query($args1);
                $searchProRes = $SearchPro->posts;
            }
            $ListingProductResult = new \WP_Query($args);
            $ListingProductRes = $ListingProductResult->posts;
            $allPro = array_merge($ListingProductRes, $searchProRes);
            $allPro = array_unique($allPro);
            $allPro = array_values($allPro);
            return $allPro;
        }
        public function getInactiveProducts($allPro)
        {
            if (empty($allPro)) {
                return array();
            }
            $args = array(
                'post_type'      => AP4L_PRODUCT_POSTTYPE,
                'fields'         => 'ids',
                'posts_per_page' => '-1',
                'post__in'       => $allPro,
                'post_status'    => 'draft'
            );
            $draftProducts = new \WP_Query($args);
            $draftProducts = $draftProducts->posts;

            $args = array(
                'post_type'      => AP4L_PRODUCT_POSTTYPE,
                'fields'         => 'ids',
                'posts_per_page' => '-1',
                'post__in'       => $allPro,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_visibility',
                        'field'    => 'slug',
                        'terms'   => array('outofstock'),
                        'compare' => 'NOT IN',
                    )
                )
            );
            $outofstockProducts = new \WP_Query($args);
            $outofstockProducts = $outofstockProducts->posts;
            $args = array(
                'post_type'      => AP4L_PRODUCT_POSTTYPE,
                'fields'         => 'ids',
                'posts_per_page' => '-1',
                'post__in'       => $allPro,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'ap4l_product_status',
                        'value'   => 'stop',
                        'compare' => '=',
                    )
                )
            );
            $stoppedProducts = new \WP_Query($args);
            $stoppedProducts = $stoppedProducts->posts;
            $ListingProductResult = array_unique(array_merge($stoppedProducts, $outofstockProducts, $draftProducts));

            return $ListingProductResult;
        }
        public function getPolicy($type, $main_ID = null, $status = null)
        {
            global $wpdb;
            $mainQuery = "SELECT * FROM " . AP4L_TABLE_PREFIX . "parent_policies AS table1 ";
            $mainQuery .= "RIGHT JOIN " . AP4L_TABLE_PREFIX . $type . "_policy AS table2 ";
            $mainQuery .= "ON table1.id = table2.main_policy_id";
            if ($status !== null || $main_ID) {
                $mainQuery .= " WHERE ";
                if ($main_ID) {
                    $mainQuery .= " table1.id = " . $main_ID . " ";
                }
                if ($status !== null && $main_ID) {
                    $mainQuery .= "AND ";
                }
                if ($status !== null) {
                    $mainQuery .= " table1.status = " . $status . " ";
                }
            }
            $mainQuery    .= " order by table1.id";
            $all_policies = $wpdb->get_results($mainQuery);
            return $all_policies;
        }
        public function getPolicies($status = null, $policyTypeFilter = '')
        {
            global $wpdb;
            $mainQuery   = "SELECT * FROM " . AP4L_TABLE_PREFIX . "parent_policies";
            $whereClause = [];
            if ($status !== null) {
                $whereClause['status'] = $status;
            }
            if (! empty($policyTypeFilter)) {
                $whereClause['policy_type'] = "'" . $policyTypeFilter . "'";
            }
            $mainQuery    .= $this->createWhereCondition($whereClause);
            $mainQuery    .= " order by id";
            $all_policies = $wpdb->get_results($mainQuery);
            return $all_policies;
        }
        public function getSyncPolicies($main_ID = null, $status = null)
        {
            global $wpdb;
            $mainQuery   = "SELECT * FROM " . AP4L_TABLE_PREFIX . "sync_policy";
            $whereClause = [];
            if ($main_ID) {
                $whereClause['main_policy_id'] = $main_ID;
            }
            if ($status !== null) {
                $whereClause['status'] = $status;
            }
            $mainQuery    .= $this->createWhereCondition($whereClause);
            $mainQuery    .= " order by id";
            $all_policies = $wpdb->get_results($mainQuery);
            return $all_policies;
        }
        public function getShippingPolicies($main_ID = null, $status = null)
        {
            global $wpdb;
            $mainQuery   = "SELECT * FROM " . AP4L_TABLE_PREFIX . "shipping_policy";
            $whereClause = [];
            if ($main_ID) {
                $whereClause['main_policy_id'] = $main_ID;
            }
            if ($status !== null) {
                $whereClause['status'] = $status;
            }
            $mainQuery    .= $this->createWhereCondition($whereClause);
            $mainQuery    .= " order by id";
            $all_policies = $wpdb->get_results($mainQuery);
            return $all_policies;
        }
        public function getSellingPolicies($main_ID = null, $status = null)
        {
            global $wpdb;
            $mainQuery   = "SELECT * FROM " . AP4L_TABLE_PREFIX . "selling_policy";
            $whereClause = [];
            if ($main_ID) {
                $whereClause['main_policy_id'] = $main_ID;
            }
            if ($status !== null) {
                $whereClause['status'] = $status;
            }
            $mainQuery    .= $this->createWhereCondition($whereClause);
            $mainQuery    .= " order by id";
            $all_policies = $wpdb->get_results($mainQuery);
        }
        public function getListing($main_ID = null, $status = null, $seller = null, $shipPolicy = null, $sellPolicy = null, $syncPolicy = null)
        {
            global $wpdb;
            $mainQuery   = "SELECT * FROM " . AP4L_TABLE_PREFIX . "listings";
            $whereClause = [];
            if ($main_ID) {
                $whereClause['id'] = $main_ID;
            }
            if ($seller) {
                $whereClause['seller_account_id'] = $seller;
            }
            if ($sellPolicy) {
                $whereClause['seller_policy_id'] = $sellPolicy;
            }
            if ($shipPolicy) {
                $whereClause['shipping_policy_id'] = $shipPolicy;
            }
            if ($syncPolicy) {
                $whereClause['sync_policy_id'] = $syncPolicy;
            }
            if ($status !== null) {
                $whereClause['status'] = $status;
            }
            $mainQuery    .= $this->createWhereCondition($whereClause);
            $mainQuery    .= " order by id";
            $all_policies = $wpdb->get_results($mainQuery);
            return $all_policies;
        }
        public function removeEntryFromDB($tablename, $id)
        {
            global $wpdb;

            $mainQuery = "DELETE FROM " . AP4L_TABLE_PREFIX . $tablename . " WHERE ID IN (" . $id . ")";

            if ($tablename == 'parent_policies') {
                $childtableQuery = "DELETE FROM " . AP4L_TABLE_PREFIX . "selling_policy WHERE main_policy_id IN (" . $id . ")";
                $delete_result   = $wpdb->get_results($childtableQuery);
                $childtableQuery = "DELETE FROM " . AP4L_TABLE_PREFIX . "sync_policy WHERE main_policy_id IN (" . $id . ")";
                $delete_result   = $wpdb->get_results($childtableQuery);
                $childtableQuery = "DELETE FROM " . AP4L_TABLE_PREFIX . "shipping_policy WHERE main_policy_id IN (" . $id . ")";
                $delete_result   = $wpdb->get_results($childtableQuery);
            }

            $main_query_result = $wpdb->get_results($mainQuery);

            return $main_query_result;
        }
        public function getProductAttributes()
        {
            global $wpdb;
            $all_attributes = $wpdb->get_results("SELECT * FROM " . AP4L_TABLE_PREFIX . "product_attributes order by id");
            return $all_attributes;
        }
        public function getWCMappingAttributes()
        {
            global $wpdb;
//            $all_attributes = $wpdb->get_results("SELECT * FROM " . AP4L_TABLE_PREFIX . "wc_mapping_attributes order by id");
            $all_attributes = array();
            return $all_attributes;
        }

        public function ap4lLog($entry)
        {
            if (defined('WP_DEBUG') && !empty(WP_DEBUG)) {
                // If the entry is array or object, json_encode.
                if (is_array($entry) || is_object($entry)) {
                    $entry = json_encode($entry);
                }

                error_log($entry);
            }
        }

        public function getProductsForSyncing($syncstatus = 0)
        {
            global $wpdb;

            $args               = array(
                'post_type'  => AP4L_PRODUCT_POSTTYPE,
                'fields'     => 'ids',
                'posts_per_page' => '30',
                'meta_query' => array(
                    array(
                        'key'     => AP4L_PRODUCT_SYNCED,
                        'value'   => $syncstatus,
                        'compare' => '=',
                    ),
                ),
            );
            $ProductsForSyncing = new \WP_Query($args);
            $productIdsTemp = $ProductsForSyncing->posts;

            $this->ap4lLog('getProductsForSyncing - started');
            $this->ap4lLog('productIdsTemp: ' . implode(', ', $productIdsTemp));

            // $productIds = array();

            // if (!empty($productIdsTemp)) {
            //     foreach ($productIdsTemp as $productId) {
            //         $productIds[] = $productId;
            //     }
            // }

            // $this->ap4lLog('productIds: ' . implode(', ', $productIds));

            // if (!empty($productIds)) {
            //     $query = "UPDATE " . $wpdb->prefix . "postmeta SET meta_value = '1' WHERE meta_key = '" . AP4L_PRODUCT_SYNCED . "' AND post_id IN ('" . implode("', '", $productIds) . "')";
            //     $wpdb->query($query);

            //     $this->ap4lLog($query);

                return $ProductsForSyncing;
            // }

            // return false;
        }
        public function getProductsForUpdating($syncstatus = 1)
        {
            $args               = array(
                'post_type'  => AP4L_PRODUCT_POSTTYPE,
                'fields'     => 'ids',
                'posts_per_page' => '30',
                'meta_query' => array(
                    array(
                        'key'     => AP4L_PRODUCT_NEEDS_UPDATE,
                        'value'   => $syncstatus,
                        'compare' => '=',
                    ),
                ),
            );
            $ProductsForSyncing = new \WP_Query($args);
            $productIdsTemp = $ProductsForSyncing->posts;

            $this->ap4lLog('getProductsForUpdating - started');
            $this->ap4lLog('productIdsTemp: ' . implode(', ', $productIdsTemp));

            // $productIds = array();

            // if (!empty($productIdsTemp)) {
            //     foreach ($productIdsTemp as $productId) {
            //         $productIds[] = $productId;
            //     }
            // }

            // $this->ap4lLog('productIds: ' . implode(', ', $productIds));

            // if (!empty($productIds)) {
            //     $query = "UPDATE " . $wpdb->prefix . "postmeta SET meta_value = '0' WHERE meta_key = '" . AP4L_PRODUCT_NEEDS_UPDATE . "' AND post_id IN ('" . implode("', '", $productIds) . "')";
            //     $wpdb->query($query);

            //     $this->ap4lLog($query);

                return $ProductsForSyncing;
            // }

            // return false;
        }
        public function getInQueryProducts($syncstatus = 1)
        {
            $args               = array(
                'post_type'  => AP4L_PRODUCT_POSTTYPE,
                'fields'     => 'ids',
                // 'posts_per_page' => '30',
                'meta_query' => array(
                    array(
                        'key'     => AP4L_PRODUCT_QUEUE_ID,
                        'value'   => '',
                        'compare' => '!=',
                    )
                ),
            );
            $ProductsForSyncing = new \WP_Query($args);
            return $ProductsForSyncing;
        }
        public function checkQueueStatus($wcproductids)
        {
            if (empty($wcproductids)) {
                return;
            }
            foreach ($wcproductids as $key => $productID) {
                $queueId = get_post_meta($productID, AP4L_PRODUCT_QUEUE_ID, true);
                $this->ap4lLog("Queue ID : " . $queueId);
                if (!empty($queueId)) {
                    $prodListingID = get_post_meta($productID, 'ap4l_pro_listing', true);
                    if (empty($prodListingID)) {
                        return;
                    }
                    $ProductPolicies = $this->getListing($prodListingID, 1);
                    if (empty($ProductPolicies)) {
                        return;
                    }
                    $ProductPolicies   = $ProductPolicies[ 0 ];
                    $sellerAccountData = $this->getAccounts($ProductPolicies->seller_account_id, 1);
                    $bearer            = $this->getAccessToken($ProductPolicies->seller_account_id);
                    if ($bearer) {
                        $bearer = $bearer[ 'accToken' ];
                    } else {
                        return;
                    }
                    $current_status = $this->getQueueStatusAP4L($queueId, $bearer);
                    if ($current_status) {
                        $responseData = $current_status['data'];
                        $responseData = ( array )$responseData;
                        $responseData = isset($responseData[0]) ? ( array )$responseData[0] : $responseData;
                        global $wpdb;
                        if ($responseData['status'] == 'success') {
                            $queue_type = get_post_meta($productID, AP4L_QUEUE_TYPE, true);
                            update_post_meta($productID, AP4L_PRODUCT_EXIST_IN_AP4L, 1);
                            update_post_meta($productID, AP4L_PRODUCT_QUEUE_ID, '');
                            $new_log_message = 'Success in Queue';
                            if ($queue_type == 'stop') {
                                update_post_meta($productID, 'ap4l_product_status', 'stop');
                                $new_log_message = 'Success in Queue - Stop';
                            } elseif ($queue_type == 'relist') {
                                update_post_meta($productID, 'ap4l_product_status', 'listed');
                                $new_log_message = 'Success in Queue - Relist';
                            } else {
                                update_post_meta($productID, 'ap4l_product_status', 'listed');
                            }

                            $queue_log_id = get_post_meta($productID, AP4L_QUEUE_LOG_ID, true);
                            if (! empty($queue_log_id)) {
                                $wpdb->update(
                                    AP4L_TABLE_PREFIX . 'listing_logs',
                                    array(
                                            'message' => $new_log_message,
                                        ),
                                    array( 'id' => $queue_log_id )
                                );
                                update_post_meta($productID, AP4L_QUEUE_LOG_ID, '');
                                update_post_meta($productID, AP4L_QUEUE_TYPE, '');
                            }
 //                           update_post_meta($productID, 'ap4l_product_status',  'Queue request success for '.$current_status->type);
                        } elseif ($responseData['status'] !== 'in_queue') {
                            $queue_type = get_post_meta($productID, AP4L_QUEUE_TYPE, true);
                            update_post_meta($productID, AP4L_PRODUCT_QUEUE_ID, '');
                            $new_log_message = 'Failed in Queue';
                            if ($queue_type == 'stop') {
                                update_post_meta($productID, 'ap4l_product_status', 'Failed in queue - Stop');
                                $new_log_message = 'Failed in Queue - Stop';
                            } elseif ($queue_type == 'relist') {
                                update_post_meta($productID, 'ap4l_product_status', 'Failed in queue - '.$responseData['type']);
                                $new_log_message = 'Failed in Queue - Relist';
                            } else {
                                update_post_meta($productID, 'ap4l_product_status', 'Failed in queue - '.$responseData['type']);
                            }
                            $queue_log_id = get_post_meta($productID, AP4L_QUEUE_LOG_ID, true);
                            if (! empty($queue_log_id)) {
                                $wpdb->update(
                                    AP4L_TABLE_PREFIX . 'listing_logs',
                                    array(
                                            'message' => $new_log_message,
                                        ),
                                    array( 'id' => $queue_log_id )
                                );
                                update_post_meta($productID, AP4L_QUEUE_LOG_ID, '');
                                update_post_meta($productID, AP4L_QUEUE_TYPE, '');
                            }
                        }
                    }
                    $this->ap4lLog("Queue status Paylod : " . $queueId);
                    $this->ap4lLog("Queue status bearer : " . $bearer);
                    $this->ap4lLog("Queue status response : " . json_encode($current_status));
                }
            }
        }
        public function createProductsInAP4L($wcproductids, $cron = false)
        {
            if (empty($wcproductids)) {
                return;
            }
            foreach ($wcproductids as $key => $productID) {
                $product     = wc_get_product($productID);
                $log_message = '';
                //Should only work if product type is simple.
                if ($product->get_type() !== 'simple') {
                    return;
                }
                if (get_post_meta($productID, '_manage_stock', true) !== 'yes') {
                    return;
                }
                if (get_post_meta($productID, 'ap4l_product_status', true) == 'stop') {
                    return;
                }
                $prodListingID = get_post_meta($productID, 'ap4l_pro_listing', true);
                if (empty($prodListingID)) {
                    return;
                }
                $ProductPolicies = $this->getListing($prodListingID, 1);
                if (empty($ProductPolicies)) {
                    return;
                }
                $ProductPolicies   = $ProductPolicies[0];
                $productSyncPolicy = $this->getPolicy('sync', $ProductPolicies->sync_policy_id, 1);
                if (empty($productSyncPolicy)) {
                    return;
                }
                $productShippingPolicy = $this->getPolicy('shipping', $ProductPolicies->shipping_policy_id, 1);
                if (empty($productShippingPolicy)) {
                    return;
                }
                $productSellingPolicy = $this->getPolicy('selling', $ProductPolicies->seller_policy_id, 1);
                if (empty($productSellingPolicy)) {
                    return;
                }
                $sellerAccountData = $this->getAccounts($ProductPolicies->seller_account_id, 1);
                if (empty($sellerAccountData)) {
                    return;
                }
                $adminFunctionsOBJ = new AdminFunctions();
                $bearer            = $adminFunctionsOBJ->getAccessToken($ProductPolicies->seller_account_id);
                if (key_exists('accToken', $bearer)) {
                    $bearer = $bearer['accToken'];
                } else {
                    return;
                }
                $ap4lproductsynced  = get_post_meta($productID, AP4L_PRODUCT_SYNCED, true);
                $ap4lupdateproducts = get_post_meta($productID, AP4L_PRODUCT_NEEDS_UPDATE, true);
//                $upc                = get_post_meta($productID, 'ap4l-upc', true);
                $upc                = $this->getProductUPC($productID);
                $productExistinap4l = false;

                $productExistsinAP4L = get_post_meta($productID, AP4L_PRODUCT_EXIST_IN_AP4L, true);
                if ($productExistsinAP4L) {
                    $ap4lproductsynced = true;
                    $productExistinap4l = true;
                } else {
                    if (! empty($upc)) {
                        $doesProductAlreadyExist = $this->fetchProductAP4L($upc, $bearer);
                        if (isset($doesProductAlreadyExist)) {
                            $responseData = $doesProductAlreadyExist['data'];
                            if ($responseData) {
                                $responseData = ( array )$responseData;
                                $responseData = isset($responseData[0]) ? ( array )$responseData[0] : $responseData;
                                if ($responseData['status'] == 'success') {
                                    $ap4lproductsynced = true;
                                    $productExistinap4l = true;
                                    update_post_meta($productID, AP4L_PRODUCT_EXIST_IN_AP4L, 1);
                                }
                            }
                        }
                    }
                }
                $apiendpoint = '';
                $responseInQueue = false;
                if (! $ap4lproductsynced) {
                    if ($cron) {
                        $ProductSyncedAllowed = $productSyncPolicy[0]->auto_sync_product;
                        if (! $ProductSyncedAllowed) {
                            return;
                        }
                    }
                    $requestAt   = current_time('Y-m-d H:i:s');
                    $apiendpoint = 'products/create';
                    $apiPostData = $this->makePostDataObject('productCreate', $productID, $productSellingPolicy, $productShippingPolicy, $sellerAccountData);
                    $apiData     = $this->elsaApiRequest($apiendpoint, [], true, $apiPostData, 'json', $bearer);
                    if (isset($apiData)) {
                        $responseData = $apiData['data'];
                        if ($responseData) {
                            $responseData = ( array )$responseData;
                            $responseData = isset($responseData[0]) ? ( array )$responseData[0] : $responseData;
                            if ($responseData['status'] == 'success') {
                                update_post_meta($productID, AP4L_PRODUCT_NEEDS_UPDATE, 0);
                                update_post_meta($productID, AP4L_PRODUCT_SYNCED, 1);
                                update_post_meta($productID, AP4L_PRODUCT_EXIST_IN_AP4L, 1);
                                if (isset($responseData['queue_id'])) {
                                    update_post_meta($productID, AP4L_PRODUCT_QUEUE_ID, $responseData['queue_id']);
                                    $responseInQueue = true;
                                    update_post_meta($productID, 'ap4l_product_status', 'Added to queue - Product Create');
                                }
                                $this->ap4lLog("Create Product ID : " . $productID . " Product Successfully Created in AP4L");
//                                $log_message = "Product Successfully Created in AP4L";
                                $log_message = "Added to queue - Product Create";
                            } else {
                                update_post_meta($productID, AP4L_PRODUCT_SYNCED, 0);
                                if ($apiData['httpcode'] == '401') {
                                    $responseData['errors'][] = 'Authentication Error! Access token might be expired.';
                                }
                                $this->ap4lLog("Create Product ID : " . $productID . " AP4L API Error : " . implode(',', ( array )$responseData['errors']));
                                $log_message = "AP4L API Error : " . implode(',', ( array )$responseData['errors']);
                            }
                        } else {
                            if ($apiData['httpcode'] == '401') {
                                $responseData['errors'][] = 'Authentication Error! Access token might be expired.';
                            }
                            $this->ap4lLog("Create Product ID : " . $productID . " AP4L API Error : " . $apiData['httpcode']);
                            $log_message = "AP4L API Error : " . $apiData['httpcode'];
                        }
                    }
                } elseif ($ap4lupdateproducts || $productExistinap4l) {
                    if ($cron) {
                        $ProductUpdateAllowed = $productSyncPolicy[0]->auto_update_product;
                        if (!$ProductUpdateAllowed) {
                            return;
                        }
                    }
                    $requestAt   = current_time('Y-m-d H:i:s');
//                    $apiendpoint = 'your-products/update';
//                    $apiPostData = $this->makePostDataObject('productUpdate', $productID, $productSellingPolicy, $productShippingPolicy, $sellerAccountData);
                    $apiendpoint           = 'products/sell-an-existing';
                    $apiPostData           = $this->makePostDataObject('productUpdate', $productID, $productSellingPolicy, $productShippingPolicy, $sellerAccountData);
                    $NewapiPostData[0]        = $apiPostData[ 0 ][ 'seller_specifics' ];
                    $NewapiPostData[0][ 'upc' ] = $apiPostData[ 0 ][ 'general_info' ][ 'upc' ];
                    $apiPostData = $NewapiPostData;
                    $this->ap4lLog("Update Product Payload : " . json_encode($apiPostData));
                    $apiData     = $this->elsaApiRequest($apiendpoint, [], true, $apiPostData, 'json', $bearer);
                    if (isset($apiData)) {
                        $responseData = $apiData['data'];
                        if ($responseData) {
                            $responseData = ( array )$responseData;
                            $responseData = isset($responseData[0]) ? ( array )$responseData[0] : $responseData;
                            if ($responseData['status'] == 'success') {
                                //TODO : yet to be decided what should be updated here for UPC since vendor sku will never be change
                                update_post_meta($productID, AP4L_PRODUCT_NEEDS_UPDATE, 0);
                                update_post_meta($productID, AP4L_PRODUCT_SYNCED, 1);
                                if (isset($responseData['queue_id'])) {
                                    update_post_meta($productID, AP4L_PRODUCT_QUEUE_ID, $responseData['queue_id']);
                                    update_post_meta($productID, 'ap4l_product_status', 'Added to queue - Product Update');
                                    $responseInQueue = true;
                                }
                                $this->ap4lLog("Update Product ID : " . $productID . " Product Successfully update in AP4L");
//                                $log_message = "Product Successfully update in AP4L";
                                $log_message = "Added to queue - Product Update";
                            } else {
                                update_post_meta($productID, AP4L_PRODUCT_NEEDS_UPDATE, 1);
                                if ($apiData['httpcode'] == '401') {
                                    $responseData['errors'][] = 'Authentication Error! Access token might be expired.';
                                }
                                //TODO : Error callback, yet to be decided what should happen
                                $this->ap4lLog("Update Product ID : " . $productID . " AP4L API Error : " . implode(',', ( array )$responseData['errors']));
                                $log_message = "AP4L API Error : " . implode(',', ( array )$responseData['errors']);
                                if (isset($responseData['errors']->vendor_sku)) { //If vendor SKU Exist
                                    if ($responseData['errors']->vendor_sku == "'vendor_sku' already exist in the system.") {
                                        $apiendpoint = 'your-products/update';
                                        $apiPostData = $this->makePostDataObject('productUpdate', $productID, $productSellingPolicy, $productShippingPolicy, $sellerAccountData);
                                        $apiData     = $this->elsaApiRequest($apiendpoint, [], true, $apiPostData, 'json', $bearer);
                                        if (isset($apiData)) {
                                            $responseData = $apiData['data'];
                                            if ($responseData) {
                                                $responseData = ( array )$responseData;
                                                $responseData = isset($responseData[0]) ? ( array )$responseData[0] : $responseData;
                                                if ($responseData['status'] == 'success') {
                                                    //TODO : yet to be decided what should be updated here for UPC since vendor sku will never be change
                                                    update_post_meta($productID, AP4L_PRODUCT_NEEDS_UPDATE, 0);
                                                    update_post_meta($productID, AP4L_PRODUCT_SYNCED, 1);
                                                    if (isset($responseData['queue_id'])) {
                                                        update_post_meta($productID, AP4L_PRODUCT_QUEUE_ID, $responseData['queue_id']);
                                                        update_post_meta($productID, 'ap4l_product_status', 'Added to queue - Product Update');
                                                        $responseInQueue = true;
                                                    }
                                                    $this->ap4lLog("Update Product ID : " . $productID . " Product Successfully update in AP4L");
//                                                    $log_message = "Product Successfully update in AP4L";
                                                    $log_message = "Added to queue - Product Update";
                                                } else {
                                                    update_post_meta($productID, AP4L_PRODUCT_NEEDS_UPDATE, 1);
                                                    //TODO : Error callback, yet to be decided what should happen
                                                    if ($apiData['httpcode'] == '401') {
                                                        $responseData['errors'][] = 'Authentication Error! Access token might be expired.';
                                                    }
                                                    $this->ap4lLog("Update Product ID : " . $productID . " AP4L API Error : " . implode(',', ( array )$responseData['errors']));
                                                    $log_message = "AP4L API Error : " . implode(',', ( array )$responseData['errors']);
                                                }
                                            } else {
                                                if ($apiData['httpcode'] == '401') {
                                                    $responseData['errors'][] = 'Authentication Error! Access token might be expired.';
                                                }
                                                $this->ap4lLog("Update Product ID : " . $productID . " AP4L API Error : " . $apiData['httpcode']);
                                                $log_message = "AP4L API Error : " . $apiData['httpcode'];
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($apiData['httpcode'] == '401') {
                                $responseData['errors'][] = 'Authentication Error! Access token might be expired.';
                            }
                            $this->ap4lLog("Update Product ID : " . $productID . " AP4L API Error : " . $apiData['httpcode']);
                            $log_message = "AP4L API Error : " . $apiData['httpcode'];
                        }
                    }
                }
                if (AP4L_LOG_STATUS && ! empty($apiendpoint)) {
                    global $wpdb;
                    $requestAt = empty($requestAt) ? current_time('Y-m-d H:i:s') : $requestAt;
                    $wpdb->insert(
                        AP4L_TABLE_PREFIX . 'listing_logs',
                        array(
                                'api_endpoint'  => isset($apiendpoint) ? $apiendpoint : '-',
                                'listing_id'    => $prodListingID,
                                'product_id'    => $productID,
                                'api_payload'   => isset($apiPostData) ? json_encode($apiPostData) : '-',
                                'request_at'    => $requestAt,
                                'api_response'  => isset($apiData) ? json_encode($apiData) : '-',
                                'response_at'   => current_time('Y-m-d H:i:s'),
                                'resposne_code' => isset($apiData) ? $apiData['httpcode'] : '-',
                                'message'       => $log_message,
                                'cron'          => $cron,
                            )
                    );
                    if ($responseInQueue) {
                        update_post_meta($productID, AP4L_QUEUE_LOG_ID, $wpdb->insert_id);
                    }
                }
            }
        }
        public function ap4lProductBulkFunction($wcproductids, $type, $listing_id)
        {
            if (empty($wcproductids)) {
                return;
            }
            foreach ($wcproductids as $key => $productID) {
                $product = wc_get_product($productID);
                //Should only work if product type is simple.
                if ($product->get_type() !== 'simple') {
                    return;
                }
                if (get_post_meta($productID, '_manage_stock', true) !== 'yes') {
                    return;
                }
                $prodListingID = get_post_meta($productID, 'ap4l_pro_listing', true);
                if (empty($prodListingID)) {
                    return;
                }
                $productExistsinAP4L = get_post_meta($productID, AP4L_PRODUCT_EXIST_IN_AP4L, true);
                if (!$productExistsinAP4L) {
                    return;
                }
                $ProductPolicies = $this->getListing($prodListingID, 1);
                if (empty($ProductPolicies)) {
                    return;
                }
                $ProductPolicies   = $ProductPolicies[0];
                $productSyncPolicy = $this->getPolicy('sync', $ProductPolicies->sync_policy_id, 1);
                if (empty($productSyncPolicy)) {
                    return;
                }
//                $ProductSyncedAllowed = $productSyncPolicy[0]->auto_sync_product;
//                if ( ! $ProductSyncedAllowed ) {
//                    return;
//                }
                $productShippingPolicy = $this->getPolicy('shipping', $ProductPolicies->shipping_policy_id, 1);
                if (empty($productShippingPolicy)) {
                    return;
                }
                $productSellingPolicy = $this->getPolicy('selling', $ProductPolicies->seller_policy_id, 1);
                if (empty($productSellingPolicy)) {
                    return;
                }
                $sellerAccountData = $this->getAccounts($ProductPolicies->seller_account_id, 1);
                if (empty($sellerAccountData)) {
                    return;
                }
                $adminFunctionsOBJ = new AdminFunctions();
                $bearer            = $adminFunctionsOBJ->getAccessToken($ProductPolicies->seller_account_id);
                if (key_exists('accToken', $bearer)) {
                    $bearer = $bearer['accToken'];
                } else {
                    return;
                }
                $ap4lproductsynced  = get_post_meta($productID, AP4L_PRODUCT_SYNCED, true);
                $ap4lupdateproducts = get_post_meta($productID, AP4L_PRODUCT_NEEDS_UPDATE, true);
//                $upc                = get_post_meta($productID, 'ap4l-upc', true);
                $upc                = $this->getProductUPC($productID);
                $log_message        = '';
                $responseInQueue = false;
                if (! empty($upc)) {
//                    $doesProductAlreadyExist = $this->fetchProductAP4L($upc, $bearer);
//                    if (isset($doesProductAlreadyExist)) {
                    if ($productExistsinAP4L) {
//                        $responseData = $doesProductAlreadyExist['data'];
//                        if ($responseData) {
//                            $responseData = ( array )$responseData;
//                            $responseData = isset($responseData[0]) ? ( array )$responseData[0] : $responseData;
//                            if ($responseData['status'] == 'success') {
                                $ap4lproductsynced = true;
                        if ($type == 'stop') {
                            //TPDO: Remove all fields except vendor SKU
                            $apiPostData                                      = [];
                            $sku                                              = get_post_meta($productID, '_sku', true);
                            $apiPostData[0]['seller_specifics']['vendor_sku'] = $sku;
                            $apiPostData[0]['seller_specifics']['quantity']   = 0;
                        } else {
                            $apiPostData = $this->makePostDataObject('productUpdate', $productID, $productSellingPolicy, $productShippingPolicy, $sellerAccountData);
                        }
                                $this->ap4lLog("Pro Update Request : " . json_encode($apiPostData));
                                $requestAt = current_time('Y-m-d H:i:s');
                                $apiData   = $this->elsaApiRequest('your-products/update', [], true, $apiPostData, 'json', $bearer);
                                $this->ap4lLog("Pro Update Responce : " . json_encode($apiData));
                        if (isset($apiData)) {
                            $responseData = $apiData['data'];
                            if ($responseData) {
                                $responseData = ( array )$responseData;
                                $responseData = isset($responseData[0]) ? ( array )$responseData[0] : $responseData;
                                if ($responseData['status'] == 'success') {
                                    //TODO : yet to be decided what should be updated here for UPC since vendor sku will never be change
                                    update_post_meta($productID, AP4L_PRODUCT_SYNCED, 1);
                                    update_post_meta($productID, AP4L_PRODUCT_NEEDS_UPDATE, 0);
                                    if (isset($responseData['queue_id'])) {
                                        update_post_meta($productID, AP4L_PRODUCT_QUEUE_ID, $responseData['queue_id']);
                                    }
                                    $this->add_ap4l_function_notices(__("Product Successfully Updated in AP4L"), "success", true);
                                    if ($type == 'stop') {
                                        update_post_meta($productID, 'ap4l_product_status', 'Added to queue - Product Stop');
                                        update_post_meta($productID, AP4L_QUEUE_TYPE, 'stop');
                                        $log_message = "Added to queue - Product Stop";
                                        $responseInQueue = true;
                                    } elseif ($type == 'relist') {
                                        update_post_meta($productID, 'ap4l_product_status', 'Added to queue - Product Relist');
                                        update_post_meta($productID, AP4L_QUEUE_TYPE, 'relist');
                                        $log_message = "Added to queue - Product Relist";
                                        $responseInQueue = true;
                                    }
                                } else {
                                    update_post_meta($productID, AP4L_PRODUCT_NEEDS_UPDATE, 1);
                                    if ($apiData['httpcode'] == '401') {
                                        $responseData['errors'][] = 'Authentication Error! Access token might be expired.';
                                    }
                                    //TODO : Error callback, yet to be decided what should happen
                                    $this->add_ap4l_function_notices(__("AP4L API Error : " . implode(',', ( array )$responseData['errors'])), "error", true);
                                    $log_message = "AP4L API Error : " . implode(',', ( array )$responseData['errors']);
                                }
                            } else {
                                $this->add_ap4l_function_notices(__("AP4L API Error HTTP Code: " . $apiData['httpcode']), "error", true);
                                $log_message = "AP4L API Error HTTP Code: " . $apiData['httpcode'];
                            }
                        }
//                            } else {
//                                $this->add_ap4l_function_notices(__("Product is not there in AP4L So first sync product with id : " . $productID), "error", true);
//                                $log_message = "Product is not there in AP4L So first sync product";
//                            }
//                        }
                    } else {
                        $this->add_ap4l_function_notices(__("Product is not there in AP4L So first sync product with id : " . $productID), "error", true);
                        $log_message = "Product is not there in AP4L So first sync product";
//                        $this->add_ap4l_function_notices(__("Some API Error with http code" . $doesProductAlreadyExist['httpcode']), "error", true);
//                        $log_message = "Some API Error with http code" . $doesProductAlreadyExist['httpcode'];
                    }
                } else {
                    $this->add_ap4l_function_notices(__("UPC not found for product  id : " . $productID), "error", true);
                    $log_message = "UPC not found in WC product";
                }
                if (! $ap4lproductsynced) {
                    $this->add_ap4l_function_notices(__("Product is not there in AP4L So first sync product with id : " . $productID), "error", true);
                    $log_message = "Product is not there in AP4L So first sync product";
                }
                if (AP4L_LOG_STATUS) {
                    global $wpdb;
                    $requestAt = empty($requestAt) ? current_time('Y-m-d H:i:s') : $requestAt;
                    $wpdb->insert(
                        AP4L_TABLE_PREFIX . 'listing_logs',
                        array(
                                'api_endpoint'  => 'your-products/update',
                                'listing_id'    => $listing_id,
                                'product_id'    => $productID,
                                'api_payload'   => isset($apiPostData) ? json_encode($apiPostData) : '-',
                                'request_at'    => $requestAt,
                                'api_response'  => isset($apiData) ? json_encode($apiData) : '-',
                                'response_at'   => current_time('Y-m-d H:i:s'),
                                'resposne_code' => isset($apiData) ? $apiData['httpcode'] : '-',
                                'message'       => $log_message,
                            )
                    );
                    if ($responseInQueue) {
                        update_post_meta($productID, AP4L_QUEUE_LOG_ID, $wpdb->insert_id);
                    }
                }
            }
        }
        /*
         * =============================
         * Past Order Sync Orders Status
         * =============================
         */
        public function ap4l_past_order_status_html($accID = null)
        {
            $allAcc = $this->getAccounts($accID, 1, 1);
            foreach ($allAcc as $key => $value) {
                $accID    = $value->id;
                $accTitle = $value->title;
                if (get_option('ap4l_sync_order_notice_status_' . $accID)) {
                    $cur_page      = get_option('ap4l_sync_order_cur_page_' . $accID);
                    $max_page      = get_option('ap4l_sync_order_max_page_' . $accID);
                    $noticeMessage = '"' . $accTitle . '" Account Past Order Progress : ';
                    if ($cur_page == '1') {
                        $noticeMessage .= '0%';
                    } elseif ($cur_page > $max_page) {
                        $noticeMessage = '';
                        update_option('ap4l_sync_order_notice_status_' . $accID, 0);
                        update_option('ap4l_sync_order_from_date_' . $accID, '');
                        update_option('ap4l_sync_order_cur_page_' . $accID, 0);
                        update_option('ap4l_sync_order_max_page_' . $accID, 0);
                        update_option('ap4l_sync_order_total_' . $accID, 0);
                    } else {
                        $totalOrders   = get_option('ap4l_sync_order_total_' . $accID);
                        $orderAdded    = ($cur_page - 1 ) * AP4L_PER_PAGE_ORDERS;
                        $progress = number_format(($orderAdded * 100)/$totalOrders, 0);
                        $noticeMessage .=  $progress.'%';
                    }
                    if (! empty($noticeMessage)) {
                        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $noticeMessage);
                    }
                }
            }
        }
        public function display_ap4l_function_notices()
        {
            $this->ap4l_past_order_status_html();
            $notices = get_option("ap4l_api_notices", array());
            /*
             * =========================================================
             * Iterate through our notices to be displayed and print them.
             * =========================================================
             */
            foreach ($notices as $notice) {
                printf(
                    '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
                    $notice['type'],
                    $notice['dismissible'],
                    $notice['notice']
                );
            }

            // Now we reset our options to prevent notices being displayed forever.
            if (! empty($notices)) {
                delete_option("ap4l_api_notices", array());
            }
        }
        public function add_ap4l_function_notices($notice = "", $type = "warning", $dismissible = true)
        {
            //warning, info, error, success.
            // Here we return the notices saved on our option, if there are not notices, then an empty array is returned
            $notices          = get_option("ap4l_api_notices", array());
            $dismissible_text = ( $dismissible ) ? "is-dismissible" : "";
            // We add our new notice.
            array_push($notices, array(
                "notice"      => $notice,
                "type"        => $type,
                "dismissible" => $dismissible_text
            ));
            // Then we update the option with our notices array
            update_option("ap4l_api_notices", $notices);
        }
        //Has no use currently but still not removed for future purpose
        public function getWoocommerceAttributesOLD($item = '')
        {
            // Get index for special column names.
            $index = $item;
            if (preg_match('/\d+/', $item, $matches)) {
                $index = $matches[0];
            }
            // Properly format for meta field.
            $meta           = str_replace('meta:', '', $item);
            // Available options.
            $weight_unit    = get_option('woocommerce_weight_unit');
            $dimension_unit = get_option('woocommerce_dimension_unit');
            $options        = array(
                'id'                 => __('ID', 'woocommerce'),
                'type'               => __('Type', 'woocommerce'),
                'sku'                => __('SKU', 'woocommerce'),
                'name'               => __('Name', 'woocommerce'),
                'published'          => __('Published', 'woocommerce'),
                'featured'           => __('Is featured?', 'woocommerce'),
                'catalog_visibility' => __('Visibility in catalog', 'woocommerce'),
                'short_description'  => __('Short description', 'woocommerce'),
                'description'        => __('Description', 'woocommerce'),
                'price'              => array(
                    'name'    => __('Price', 'woocommerce'),
                    'options' => array(
                        'regular_price'     => __('Regular price', 'woocommerce'),
                        'sale_price'        => __('Sale price', 'woocommerce'),
                        'date_on_sale_from' => __('Date sale price starts', 'woocommerce'),
                        'date_on_sale_to'   => __('Date sale price ends', 'woocommerce'),
                    ),
                ),
                'tax_status'         => __('Tax status', 'woocommerce'),
                'tax_class'          => __('Tax class', 'woocommerce'),
                'stock_status'       => __('In stock?', 'woocommerce'),
                'stock_quantity'     => _x('Stock', 'Quantity in stock', 'woocommerce'),
                'backorders'         => __('Backorders allowed?', 'woocommerce'),
                'low_stock_amount'   => __('Low stock amount', 'woocommerce'),
                'sold_individually'  => __('Sold individually?', 'woocommerce'),
                /* translators: %s: weight unit */
                'weight'             => sprintf(__('Weight (%s)', 'woocommerce'), $weight_unit),
                'dimensions'         => array(
                    'name'    => __('Dimensions', 'woocommerce'),
                    'options' => array(
                        /* translators: %s: dimension unit */
                        'length' => sprintf(__('Length (%s)', 'woocommerce'), $dimension_unit),
                        /* translators: %s: dimension unit */
                        'width'  => sprintf(__('Width (%s)', 'woocommerce'), $dimension_unit),
                        /* translators: %s: dimension unit */
                        'height' => sprintf(__('Height (%s)', 'woocommerce'), $dimension_unit),
                    ),
                ),
                'category_ids'       => __('Categories', 'woocommerce'),
                'tag_ids'            => __('Tags (comma separated)', 'woocommerce'),
                'tag_ids_spaces'     => __('Tags (space separated)', 'woocommerce'),
                'shipping_class_id'  => __('Shipping class', 'woocommerce'),
                'images'             => __('Images', 'woocommerce'),
                'parent_id'          => __('Parent', 'woocommerce'),
                'upsell_ids'         => __('Upsells', 'woocommerce'),
                'cross_sell_ids'     => __('Cross-sells', 'woocommerce'),
                'grouped_products'   => __('Grouped products', 'woocommerce'),
                'external'           => array(
                    'name'    => __('External product', 'woocommerce'),
                    'options' => array(
                        'product_url' => __('External URL', 'woocommerce'),
                        'button_text' => __('Button text', 'woocommerce'),
                    ),
                ),
                'downloads'          => array(
                    'name'    => __('Downloads', 'woocommerce'),
                    'options' => array(
                        'downloads:id' . $index   => __('Download ID', 'woocommerce'),
                        'downloads:name' . $index => __('Download name', 'woocommerce'),
                        'downloads:url' . $index  => __('Download URL', 'woocommerce'),
                        'download_limit'          => __('Download limit', 'woocommerce'),
                        'download_expiry'         => __('Download expiry days', 'woocommerce'),
                    ),
                ),
                'attributes'         => array(
                    'name'    => __('Attributes', 'woocommerce'),
                    'options' => array(
                        'attributes:name' . $index     => __('Attribute name', 'woocommerce'),
                        'attributes:value' . $index    => __('Attribute value(s)', 'woocommerce'),
                        'attributes:taxonomy' . $index => __('Is a global attribute?', 'woocommerce'),
                        'attributes:visible' . $index  => __('Attribute visibility', 'woocommerce'),
                        'attributes:default' . $index  => __('Default attribute', 'woocommerce'),
                    ),
                ),
                'reviews_allowed'    => __('Allow customer reviews?', 'woocommerce'),
                'purchase_note'      => __('Purchase note', 'woocommerce'),
                'meta:' . $meta      => __('Import as meta data', 'woocommerce'),
                'menu_order'         => __('Position', 'woocommerce'),
            );
            return apply_filters('woocommerce_csv_product_import_mapping_options', $options, $item);
        }
        private function get_wc_product_meta_keys()
        {
            global $wpdb;
            $post_type = 'product';
            $query     = "
                SELECT DISTINCT($wpdb->postmeta.meta_key)
                FROM $wpdb->posts
                LEFT JOIN $wpdb->postmeta
                ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                WHERE $wpdb->posts.post_type = '%s'
                AND $wpdb->postmeta.meta_key != ''
                AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)'
                AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
            ";
            $meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));
            return $meta_keys;
        }
        public function getWoocommerceAttributes($item = '')
        {
            $wcMappingAttrs                     = $this->getWCMappingAttributes();
            $options                            = [];
            $options['post_title']              = 'Name';
            $options['post_content']            = 'Description';
            $options['_weight']                 = 'Weight';
            $options['_sku']                    = 'SKU';
            $options['_regular_price']          = 'Regular price';
            $options['_sale_price']             = 'Sale price';
            $options['_sale_price_dates_from']  = 'Sale price dates from';
            $options['_sale_price_dates_to']    = 'Sale price dates to';
            $options['_stock']                  = 'Stock quantity';
            $options['_low_stock_amount']       = 'Low stock threshold';
            // $options['_tax_status']             = 'Tax status';

            if (! empty($wcMappingAttrs)) {
                foreach ($wcMappingAttrs as $map_key => $map_val) {
                    $map_slug           = 'ap4l-' . sanitize_title($map_val->attribute_name);
                    $options[$map_slug] = $map_val->attribute_name;
                }
            }
            $allMetaFields = $this->get_wc_product_meta_keys();
            foreach ($allMetaFields as $meta_key => $meta_val) {
                if (! array_key_exists($meta_val, $options)) {
                    $options[$meta_val] = ucwords(trim(str_replace('_', ' ', $meta_val)));
                }
            }

            natsort($options);
            return $options;
        }
        public function getProductMetaValuesObj($WCProductID)
        {
            $valuesOBJ      = [];
            $wcMappingAttrs = $this->getWCMappingAttributes();
            if (! empty($wcMappingAttrs)) {
                foreach ($wcMappingAttrs as $map_key => $map_val) {
                    $map_slug             = 'ap4l-' . sanitize_title($map_val->attribute_name);
                    $meta_value           = get_post_meta($WCProductID, $map_slug, true);
                    $valuesOBJ[$map_slug] = $meta_value;
                }
            }
            $product                   = wc_get_product($WCProductID);
            $weight_unit               = get_option('woocommerce_weight_unit');
            $valuesOBJ['post_title']   = $product->get_name();
            $valuesOBJ['post_content'] = $product->get_description();
            $valuesOBJ['_weight']      = $product->get_weight();
            $valuesOBJ['_sku']         = $product->get_sku();
            $valuesOBJ['_price']       = $product->get_regular_price();
            $valuesOBJ['_sale_price']  = $product->get_sale_price();
            $valuesOBJ['_stock']       = $product->get_stock_quantity();
            $attachment_ids            = $product->get_gallery_image_ids();
            $feature_image             = get_the_post_thumbnail_url($WCProductID);

            if (! empty($feature_image)) {
                $valuesOBJ['product_images']['image_1'] = $feature_image;
            }

            $imageKey = ((!empty($valuesOBJ['product_images'])) ? count($valuesOBJ['product_images']) : 0);

            if (!empty($attachment_ids)) {
                foreach ($attachment_ids as $attachment_id) {
                    if (!empty($attachment_id)) {
                        $valuesOBJ['product_images']['image_' . (++$imageKey)] = wp_get_attachment_url($attachment_id);
                    }
                }
            }

            return $valuesOBJ;
        }

        public function makePostDataObject($action, $WCProductID, $productSellingPolicy, $productShippingPolicy, $sellerAccountData)
        {
            // $this->ap4lLog('makePostDataObject - temp - start');

            $productAttributes = $this->getProductAttributes($WCProductID);
            $returnObj         = [];

            // $this->ap4lLog('productAttributes:');
            // $this->ap4lLog($productAttributes);

            foreach ($productAttributes as $attrkey => $attrval) {
                $main_key = $attrval->attribute_type == 'General' ? 'general_info' : 'seller_specifics';
                $wc_attr  = array_filter($productSellingPolicy, function ($item) use ($attrval) {
                    return $attrval->id == $item->product_attribute_id;
                });
                $meta_value = '';

                if (! empty($wc_attr)) {
                    $wc_attribute = reset($wc_attr)->woocommerce_attribute;

                    if ($wc_attribute == 'post_title') {
                        $meta_value = get_the_title($WCProductID);
                    } elseif ($wc_attribute == 'post_content') {
                        $meta_value = get_the_content($WCProductID);
                    } elseif ($wc_attribute == '' && reset($wc_attr)->static_value !== '') {
                        $meta_value = reset($wc_attr)->static_value;
                    } else {
                        $meta_value = get_post_meta($WCProductID, $wc_attribute, true);

                        if ($attrval->ap4l_key == 'instruction_files' ||
                            $attrval->ap4l_key == 'video_urls'
                        ) {
                            if (!empty($meta_value)) {
                                $meta_value = explode(',', str_replace(' ', '', $meta_value));
                            } else {
                                $meta_value = array();
                            }
                        } elseif ($attrval->ap4l_key == 'eligible_for_return' ||
                            $attrval->ap4l_key == 'allow_free_return' ||
                            $attrval->ap4l_key == 'california_proposition65_warn'
                        ) {
                            $meta_value = ((strtolower(trim($meta_value)) == 'yes') ? 1 : 0);
                        }
                    }
                }

                // $this->ap4lLog('$wc_attribute: ' . ($wc_attribute ?? ''));
                // $this->ap4lLog('$attrval->ap4l_key: ' . $attrval->ap4l_key);
                // $this->ap4lLog('$meta_value: ');
                // $this->ap4lLog($meta_value);

                $returnObj[$main_key][$attrval->ap4l_key] = $meta_value;
            }

            $attachment_ids = get_post_meta($WCProductID, '_product_image_gallery', true);
            $attachment_ids = explode(',', $attachment_ids);
            $feature_image  = get_the_post_thumbnail_url($WCProductID);

            if (! empty($feature_image)) {
                $returnObj['product_images']['image_1'] = $feature_image;
            }

            $imageKey = ((!empty($returnObj['product_images'])) ? count($returnObj['product_images']) : 0);

            if (! empty($attachment_ids)) {
                foreach ($attachment_ids as $attachment_id) {
                    if (! empty($attachment_id)) {
                        $returnObj['product_images']['image_' . (++$imageKey)] = wp_get_attachment_url($attachment_id);
                    }
                }
            }

            for ($i=$imageKey; $i < 10; $i++) {
                $returnObj['product_images']['image_' . ($i + 1)] = '';
            }

            $sync_quantity = $sellerAccountData[0]->sync_quantity;

            if ($action == 'productUpdate' && empty($sync_quantity)) {
                unset($returnObj['seller_specifics']['quantity']);
            }

            $returnObj['seller_specifics']['sale_price'] = ((!empty($returnObj['seller_specifics']['sale_price'])) ? $returnObj['seller_specifics']['sale_price'] : 0);

            //Some Additional Fields which are not directly available
            $product_IDS                                     = wp_get_post_terms($WCProductID, 'product_cat', array( "fields" => "ids" ))[0];

            if (! empty($product_IDS)) {
                $returnObj['general_info']['category_id'] = get_term_meta($product_IDS, AP4L_CATEGORY_KEY, true);
            }

            $shipping_policy = get_post_meta($WCProductID, 'ap4l_shipping_policyid', true);

            if (empty($shipping_policy)) {
                $shipping_policy = ! empty($productShippingPolicy) ? $productShippingPolicy[0]->ap4l_shipping_policy_id : '';
            }

            $returnObj['seller_specifics']['shipping_policy_id'] = $shipping_policy;

            if (isset($returnObj['seller_specifics']['quantity'])) {
                $returnObj['seller_specifics']['quantity'] = intval($returnObj['seller_specifics']['quantity']);
            }

            $mainResponseObject[]                                = $returnObj;

            // $this->ap4lLog($mainResponseObject);
            // $this->ap4lLog('makePostDataObject - temp - end');

            return $mainResponseObject;
        }
        public function fetchProductAP4L($upc, $bearer)
        {
            $queryParams['upc'] = $upc;
            $apiData            = $this->elsaApiRequest('products/exists', $queryParams, false, false, false, $bearer);
            return $apiData;
        }

        public function getQueueStatusAP4L($queue, $bearer)
        {
            $queryParams['queue_id'] = $queue;
            $apiData            = $this->elsaApiRequest('products/queue-status', $queryParams, false, false, false, $bearer);
            return $apiData;
        }

        public function wpHttpApi($args)
        {
            // $args = array(
            //     'method' => 'GET',
            //     'timeout' => '5', // How long to wait before giving up
            //     'redirection' => '5', // How many times to follow redirections.
            //     'httpversion' => '1.0',
            //     'blocking' => true, // Should the rest of the page wait to finish loading until this operation is complete?
            //     'headers' => array(),
            //     'body' => null,
            //     'cookies' => array(),
            // );

            if (empty($args['method'])) {
                $args['method'] = 'GET';
            } else {
                $args['method'] = strtoupper(str_replace(' ', '', $args['method']));
            }

            $url = $args['url'];
            unset($args['url']);

            // $this->ap4lLog('CURL - URL: ' . $url);
            // $this->ap4lLog('Args: ');
            // $this->ap4lLog($args);

            if ($args['method'] == 'GET') {
                $response = wp_remote_get($url, $args);
            } elseif ($args['method'] == 'POST') {
                unset($args['method']);
                $response = wp_remote_post($url, $args);
            } else {
                $response = wp_remote_request($url, $args);
            }

            // if ($response === false) {
            //     trigger_error('Error: "' . curl_error($request) . '" - Code: ' . curl_errno($request));
            // }

            $httpCode   = wp_remote_retrieve_response_code($response);
            $body       = wp_remote_retrieve_body($response);
            $body       = json_decode($body);

            $response = array(
                'httpcode' => $httpCode,
                'data' => $body,
            );

            // $this->ap4lLog('response: ');
            // $this->ap4lLog($response);

            return $response;
        }

        public function elsaApiRequest($endpoint, $queryParams = [], $optPost = false, $postFields = false, $httpHeaderType = false, $bearer = false, $requestType = false)
        {
            $args = array();

            // setup url - start
            $requestUrl = AP4L_API_URL . $endpoint;

            if (! empty($queryParams)) {
                $query = '';

                foreach ($queryParams as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $vKey => $vValue) {
                            $query .= '&' . $vKey . '=' . urlencode($vValue);
                        }
                    } else {
                        $query .= '&' . $key . '=' . urlencode($value);
                    }
                }

                $requestUrl = $requestUrl . '?' . trim($query, '&');
            }

            $args['url'] = $requestUrl;
            // setup url - end

            // setup headers and http post fields - start
            $httpHeaders   = array();
            $httpHeaders['Accept'] = 'application/json';

            if ($httpHeaderType == 'json' && !empty($postFields)) {
                $postFields    = json_encode($postFields);
                $httpHeaders['Content-Type'] = 'application/json';
            } elseif ($httpHeaderType == 'x-form' && !empty($postFields)) {
                $postFields    = http_build_query($postFields);
                $httpHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
            }

            if (!empty($bearer)) {
                $httpHeaders['Authorization'] = 'Bearer ' . $bearer;
            }

            $args['headers'] = $httpHeaders;

            if (!empty($postFields)) {
                $args['body'] = $postFields;
            }
            // setup headers and http post fields - end

            // setup method - start
            if (!empty($requestType)) {
                $args['method'] = $requestType;
            } elseif (!empty($optPost)) {
                $args['method'] = 'POST';
            } else {
                $args['method'] = 'GET';
            }
            // setup method - end

            return $this->wpHttpApi($args);
        }
    }
}
