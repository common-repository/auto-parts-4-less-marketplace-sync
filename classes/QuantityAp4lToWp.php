<?php
/**
 * Product Quantity sync from Ap4l to WooCommerce
 */
namespace Ap4l;

include_once AP4L_DIR . 'classes/AdminFunctions.php';

use Ap4l\AdminFunctions;

if (! class_exists('QuantityAp4lToWp')) {
    class QuantityAp4lToWp
    {
        private $AdminFunctions = false;
        private $table = AP4L_TABLE_PREFIX . 'quantity_to_wp';
        private $parentId = 0;
        private $lastPage = 0;
        private $perPage = 30;
        private $totalPages = 0;
        private $totalRecords = 0;
        private $productsProcessed = 0;
        private $listingProducts = array();
        private $listingProductsQty = array();

        public function __construct()
        {
            // global $wpdb;

            // $wpdb->show_errors();
            // $wpdb->hide_errors();

            $this->AdminFunctions = new AdminFunctions();
        }

        public function removeLogs($days)
        {
            global $wpdb;

            $deletedDate = date('Y-m-d', strtotime('-' . AP4L_LOGS_DAYS . ' days'));
            $wpdb->query($wpdb->prepare("delete from " . $this->table . " where date(started_at) < %s", $deletedDate));
            $wpdb->query($wpdb->prepare("delete from " . $this->table . "_products where date(created_at) < %s", $deletedDate));

            return $deletedDate;
        }

        public function sync()
        {
            global $wpdb;

            $this->AdminFunctions->ap4lLog("===== Quantity Ap4l To Wp - Started =====");

            $accounts = $this->AdminFunctions->getAccounts();

            if (empty($accounts)) {
                $this->AdminFunctions->ap4lLog("No account found.");
            }

            foreach ($accounts as $account) {
                $accountId = $account->id;

                $this->AdminFunctions->ap4lLog("Account = " . $accountId);

                if (empty($account->sync_quantity)) {
                    $this->AdminFunctions->ap4lLog("Sync quantity set to false.");
                    continue;
                }

                $listings = $this->AdminFunctions->getListing(null, null, $accountId);

                if (empty($listings)) {
                    $this->AdminFunctions->ap4lLog("No listing found.");
                    continue;
                }

                foreach ($listings as $listing) {
                    $listingProducts = $this->getListingProducts($listing->id, true, true);

                    if (!empty($listingProducts)) {
                        $this->listingProducts = $this->listingProducts + $listingProducts;
                    }
                }

                if (empty($this->listingProducts)) {
                    $this->AdminFunctions->ap4lLog("No listing products found.");
                    continue;
                }

                $inProgress = $this->checkInProgress($accountId);

                if (!empty($inProgress)) {
                    $this->AdminFunctions->ap4lLog("In progress ...");
                    continue;
                }

                $bearer = $this->AdminFunctions->getAccessToken($accountId);

                if (empty($bearer) || empty($bearer['accToken'])) {
                    $this->AdminFunctions->ap4lLog("Bearer is empty.");
                    continue;
                }

                $bearer = $bearer['accToken'];

                $products = $this->getProductsFromApi($bearer);

                if (!empty($products)) {
                    foreach ($products as $sku => $quantity) {
                        $this->productsProcessed++;

                        // $this->AdminFunctions->ap4lLog("=====");
                        // $this->AdminFunctions->ap4lLog("Products Total = " . $this->totalRecords);
                        // $this->AdminFunctions->ap4lLog("Products Processed = " . $this->productsProcessed);
                        // $this->AdminFunctions->ap4lLog("Pages Total = " . $this->totalPages);
                        // $this->AdminFunctions->ap4lLog("Page Current = " . $this->lastPage);

                        $data = array(
                            'last_page' => $this->lastPage,
                            'last_product' => $sku,
                            'products_at_finish' => $this->totalRecords,
                        );

                        // $this->AdminFunctions->ap4lLog("Product counts = " . $this->productsProcessed . " == " . $this->perPage);

                        if ($this->lastPage == 1) {
                            $data['products_at_start'] = $this->totalRecords;
                        }

                        if ($this->lastPage != $this->totalPages) {
                            $data['in_progress'] = (($this->productsProcessed == $this->perPage) ? 0 : 1);

                            // $this->AdminFunctions->ap4lLog("Product counts = " . $this->productsProcessed . " == " . $this->perPage);
                        } else {
                            $data['finished_at'] = date('Y-m-d H:i:s');

                            $currentProducts = $this->perPage - (($this->lastPage * $this->perPage) - $this->totalRecords);
                            $data['in_progress'] = (($this->productsProcessed == $currentProducts) ? 0 : 1);

                            // $this->AdminFunctions->ap4lLog("Product counts = " . $this->productsProcessed . " == " . $currentProducts);
                        }

                        $where = array(
                            'id' => $this->parentId,
                        );

                        $wpdb->update($this->table, $data, $where);

                        if (true === ($productId = $this->updateQuantityBySku($sku, $quantity))) {
                            $this->AdminFunctions->ap4lLog("Not found   => SKU = " . $sku);
                        } elseif (false === $productId) {
                            $this->AdminFunctions->ap4lLog("Not updated => SKU = " . $sku);
                        } else {
                            $this->AdminFunctions->ap4lLog("Updated     => SKU = " . $sku);

                            $data = array(
                                'parent_id' => $this->parentId,
                                'product_id' => $productId,
                                'sku' => $sku,
                                'quantity' => $quantity,
                                'quantity_old' => ((!empty($this->listingProductsQty[$productId]) && !empty($this->listingProductsQty[$productId]['_stock'])) ? $this->listingProductsQty[$productId]['_stock'] : 0),
                                'created_at' => date('Y-m-d H:i:s'),
                            );

                            $wpdb->insert($this->table . '_products', $data);
                        }
                    }
                } else {
                    $data = array(
                        'in_progress' => 0,
                        'last_page' => $this->lastPage,
                        'last_product' => '',
                        'products_at_finish' => $this->totalRecords,
                    );

                    if ($this->lastPage == 1) {
                        $data['products_at_start'] = $this->totalRecords;
                    }

                    if ($this->lastPage == $this->totalPages) {
                        $data['finished_at'] = date('Y-m-d H:i:s');
                    }

                    $where = array(
                        'id' => $this->parentId,
                    );

                    $wpdb->update($this->table, $data, $where);
                }
            }

            $this->AdminFunctions->ap4lLog("===== Quantity Ap4l To Wp - Finished =====");
        }

        public function updateQuantityBySku($sku, $quantity)
        {
            global $wpdb;

            // $this->AdminFunctions->ap4lLog("=====");

            $listingProducts = array_column($this->listingProducts, '_sku', 'product_id');

            if (false === ($productId = array_search($sku, $listingProducts))) {
                return true;
            }

            // $this->AdminFunctions->ap4lLog("in listing = pass");

            $data = array(
                'meta_value' => $quantity,
            );

            $where = array(
                'post_id' => $productId,
                'meta_key' => '_stock',
            );

            // $this->AdminFunctions->ap4lLog("=====");
            // $this->AdminFunctions->ap4lLog($wpdb->prefix . "postmeta");
            // $this->AdminFunctions->ap4lLog($data);
            // $this->AdminFunctions->ap4lLog($where);
            // $this->AdminFunctions->ap4lLog($wpdb->update($wpdb->prefix . "postmeta", $data, $where));

            return (false !== $wpdb->update($wpdb->prefix . "postmeta", $data, $where)) ? $productId : false;
        }

        public function getListingProducts($listingId, $manageStock = false, $ignoreStopped = false)
        {
            global $wpdb;

            $productIds = $wpdb->get_col("select post_id from " . $wpdb->prefix . "postmeta where meta_key = 'ap4l_pro_listing' and meta_value = '" . $listingId . "'");

            $productData = $wpdb->get_results("select post_id as product_id, meta_key, meta_value from " . $wpdb->prefix . "postmeta where post_id in ('" . implode("', '", $productIds) . "')");

            $listingProducts = array();

            foreach ($productIds as $productId) {
                $listingProducts[$productId]['product_id'] = $productId;
            }

            foreach ($productData as $product) {
                $listingProducts[$product->product_id][$product->meta_key] = $product->meta_value;
            }

            if (!empty($manageStock) || !empty($ignoreStopped)) {
                foreach ($listingProducts as $product_id => $product) {
                    if ((
                        !empty($manageStock) &&
                        (
                            empty($product['_manage_stock']) ||
                            $product['_manage_stock'] != 'yes'
                        )
                    ) ||
                    (
                        !empty($ignoreStopped) &&
                        !empty($product['ap4l_product_status']) &&
                        $product['ap4l_product_status'] == 'stop'
                    ) ||
                    !empty($product['ap4l_pro_needs_update']) ||
                    !empty($product['ap4l_pro_queue_id'])
                    ) {
                        unset($listingProducts[$product_id]);
                    }
                }
            }

            return $listingProducts;
        }

        public function getProductsFromApi($bearer)
        {
            $products = array();

            $args = array(
                'method' => 'GET',
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $bearer,
                ),
                'url' => AP4L_API_URL . 'your-products?page_no=' . ( $this->lastPage + 1 )
            );

            $response = $this->AdminFunctions->wpHttpApi($args);
            $httpCode = $response['httpcode'];
            $data = $response['data'];

            if ($httpCode < 400) {
                $productsTemp = json_decode(json_encode($data), true);

                if (!empty($productsTemp)) {
                    if (empty($productsTemp['status']) ||
                        empty($productsTemp['paging']) ||
                        empty($productsTemp['products']) ||
                        $productsTemp['status'] != 'success'
                    ) {
                        $this->AdminFunctions->ap4lLog("Empty response..");
                        return $products;
                    }

                    $this->lastPage = intval($productsTemp['paging']['page_no']);
                    $this->totalRecords = intval($productsTemp['paging']['total_records']);
                    $this->totalPages = ceil(($this->totalRecords > 0 && $this->perPage > 0) ? $this->totalRecords / $this->perPage : 1);

                    $products = array_column($productsTemp['products'], 'quantity', 'vendor_sku');
                } else {
                    $this->AdminFunctions->ap4lLog("Empty response.");
                }
            } else {
                $this->AdminFunctions->ap4lLog("Error in API = " . json_encode($data));
            }

            return $products;
        }

        public function checkInProgress($accountId)
        {
            global $wpdb;

            $inProgress = $wpdb->get_var("select count(*) as cnt from " . $this->table . " where finished_at is null and in_progress = 1 and account_id = " . $accountId);

            if (empty($inProgress)) {
                $row = $wpdb->get_row("select count(*) as cnt, max(id) as row_id from " . $this->table . " where finished_at is null and in_progress = 0 and account_id = " . $accountId);
                $isExists = $row->cnt;

                if (empty($isExists)) {
                    $data = array(
                        'account_id' => $accountId,
                        'in_progress' => 1,
                        'last_page' => 0,
                        'last_product' => '',
                        'products_at_start' => 0,
                        'products_at_finish' => 0,
                        'started_at' => date('Y-m-d H:i:s'),
                    );

                    $wpdb->insert($this->table, $data);
                    $this->parentId = $wpdb->insert_id;
                } else {
                    $this->parentId = $row->row_id;

                    $data = array(
                        'in_progress' => 1,
                    );

                    $where = array(
                        'id' => $this->parentId,
                    );

                    $wpdb->update($this->table, $data, $where);
                }

                $this->lastPage = $wpdb->get_var("select last_page from " . $this->table . " where id = " . $this->parentId);

                return false;
            }

            return true;
        }
    }
}
