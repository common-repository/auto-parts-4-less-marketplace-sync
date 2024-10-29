<?php
namespace Ap4l;

include_once AP4L_DIR . 'classes/UserModal.php';

use Ap4l\UserModal;

if (! class_exists('AdminFunctions')) {
    class AdminFunctions extends UserModal
    {
        /**
         * ==================
         * Account Module HTML
         * ==================
         */
        public function accountsPageHtml()
        {
            global $wpdb;
            include_once plugin_dir_path(dirname(__FILE__)) . 'views/account-view.php';
        }
        /**
         * ==================
         * Policies Module HTML
         * ==================
         */
        public function PoliciesPageHtml()
        {
            global $wpdb;
            include_once plugin_dir_path(dirname(__FILE__)) . 'views/policy-view.php';
        }
        /**
         * ==================
         * Category Module HTML
         * ==================
         */
        public function CategoriesPageHtml()
        {
            global $wpdb;
            include_once plugin_dir_path(dirname(__FILE__)) . 'views/category-view.php';
        }
        /**
         * ==================
         * Listing Module HTML
         * ==================
         */
        public function ListingsPageHtml()
        {
            global $wpdb;
            include_once plugin_dir_path(dirname(__FILE__)) . 'views/listing-view.php';
        }
        public function ListingsInnerPageHtml()
        {
            global $wpdb;
            include_once plugin_dir_path(dirname(__FILE__)) . 'views/listing-inner-view.php';
        }
        /**
         * ==================
         * Listing Log Module HTML
         * ==================
         */
        public function ListingLogsPageHtml()
        {
            global $wpdb;
            include_once plugin_dir_path(dirname(__FILE__)) . 'views/listing-log-view.php';
        }
        /**
         * ==================
         * Orders Module HTML
         * ==================
         */
        public function OrdersPageHtml()
        {
            global $wpdb;
            include_once plugin_dir_path(dirname(__FILE__)) . 'views/orders-view.php';
        }
        /**
         * ==================
         * Order Logs Module HTML
         * ==================
         */
        public function OrderLogsPageHtml()
        {
            global $wpdb;
            include_once plugin_dir_path(dirname(__FILE__)) . 'views/orders-log-view.php';
        }
        /**
         * ==================
         * Log Setting Module HTML
         * ==================
         */
        public function LogsSettingPageHtml()
        {
            global $wpdb;
            include_once plugin_dir_path(dirname(__FILE__)) . 'views/logs-view.php';
        }

        /*
         * ==================
         * Get New Access Token
         * ==================
         */
        public function getNewAccessToken($email, $auth_token)
        {
            $args        = array(
                'headers' => array(
                    'Accept' => 'application/json'
                )
            );
            $apiResponse = wp_remote_get(AP4L_API_URL . 'authorize?email=' . $email . '&auth_token=' . $auth_token, $args);
            $body        = json_decode(wp_remote_retrieve_body($apiResponse), true);
            return $body;
        }
        /*
         * ==================
         * Get Access Token
         * ==================
         */
        public function getAccessToken($account_id)
        {
            global $wpdb;
            /*
             * ==================
             * Account Not Exists
             * ==================
             */
            $response       = array(
                'status'  => 'error',
                'message' => 'Error while geting access token.',
            );
            $accountDetails = $wpdb->get_row("SELECT * FROM " . AP4L_TABLE_PREFIX . "accounts WHERE id = '" . $account_id . "'", ARRAY_A);
            if (! empty($accountDetails)) {
                $current_time = strtotime('-5 minutes', time());
                $expires_at   = strtotime($accountDetails['expires_at']);
                if ($current_time < $expires_at) {
                    $response['accToken'] = $accountDetails['access_token'];
                    $response['status']   = 'success';
                    $response['message']  = 'Access Token Taken';
                    return $response;
                } else {
                    $accTok = $this->getNewAccessToken($accountDetails['email'], $accountDetails['auth_token']);
                    if ($accTok['status'] === 'success') {
                        $response['accToken'] = $accTok['access_token'];
                        $response['status']   = 'success';
                        $wpdb->update(
                            AP4L_TABLE_PREFIX . 'accounts',
                            array(
                                    'access_token' => $accTok['access_token'],
                                    'expires_at'   => $accTok['expires_at'],
                                ),
                            array(
                                    'id' => $account_id,
                                )
                        );
                    } else {
                        $response = $accTok;
                    }
                }
            }
            return $response;
        }
        /*
         * ==================
         * Product Sync function when update or save post
         * ==================
         */
        public function SyncProductWithAP4L($post_ID, $post, $update)
        {
            if ($post->post_status != 'publish' && $post->post_type !== 'product') {
                return;
            }
            $product = wc_get_product($post_ID);
            if (! $product) {
                return;
            }
            if ($product->get_type() !== 'simple') {
                return; //Should only work if product type is simple.
            }
            $prodListingID = get_post_meta($post_ID, 'ap4l_pro_listing', true);
            if (empty($prodListingID)) {
                return;
            } //Return;

            $ProductPolicies = $this->getListing($prodListingID, 1);
            if (empty($ProductPolicies)) {
                return;
            } //Return;
            $ProductPolicies       = $ProductPolicies[0];
            $productSyncPolicy     = $this->getPolicy('sync', $ProductPolicies->sync_policy_id, 1);
            if (!empty($productSyncPolicy)) {
                $ProductUpdateAllowed = $productSyncPolicy[0]->auto_update_product;
                if ($ProductUpdateAllowed) {
                    update_post_meta($post_ID, AP4L_PRODUCT_NEEDS_UPDATE, 1);
                }
            }
        }
    }
}
