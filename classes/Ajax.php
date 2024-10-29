<?php
/**
 * The admin-specific functionality of the plugin.
 */
namespace Ap4l;

include_once AP4L_DIR . 'classes/AdminFunctions.php';

use Ap4l\AdminFunctions;

if (! class_exists('Ajax')) {
    class Ajax extends AdminFunctions
    {
        /**
         * ===========================================
         * Initialize the class and set its properties.
         * ===========================================
         */
        public function __construct()
        {
            // NA
        }
        /*
         * =========================
         * Category Mapping Function
         * =========================
         */
        public function AddCategoryMapping()
        {
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $response        = array(
                'status'  => 'error',
                'message' => 'You can not change value.',
            );

            $attributeValues = (!empty($_REQUEST['attributeValues'])) ? ap4l_sanitize_array($_REQUEST['attributeValues']) : array();

            if (! empty($attributeValues)) {
                foreach ($attributeValues as $key => $value) {
                    update_term_meta($value[ 'wooCat' ], AP4L_CATEGORY_KEY, $value[ 'ap4l' ]);
                }

                $response = array(
                    'status'  => 'success',
                    'message' => 'Categories mapped successfully.',
                );
            }

            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * =========================
         * Update Log Settings
         * =========================
         */
        public function UpdateLogsSetting()
        {
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');

            $response = array(
                'status'  => 'error',
                'message' => 'You can not change value.',
            );

            $formData = (!empty($_REQUEST['formData'])) ? ap4l_sanitize_array($_REQUEST['formData']) : array();

            if (! empty($formData)) {
                foreach ($formData as $key => $value) {
                    $res = update_option($value[ 'name' ], $value[ 'value' ]);
                }

                $response = array(
                    'status'  => 'success',
                    'message' => 'Log Settings saved successfully.',
                );
            }

            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * =====================
         * Account Change Module
         * =====================
         */
        public function accountsChnageAction()
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $accID            = sanitize_text_field($_REQUEST[ 'accID' ]);
            $changeMethod     = sanitize_text_field($_REQUEST[ 'changeMethod' ]);
            $CheckBoxValue    = sanitize_text_field($_REQUEST[ 'CheckBoxValue' ]);
            $successmsg       = sanitize_text_field($_REQUEST[ 'successmsg' ]);
            $response         = array(
                'status'  => 'error',
                'message' => 'You can not change value.',
            );
            $access_token_res = $this->getAccessToken($accID);
            if ($access_token_res[ 'status' ] === 'success') {
                $updateRes = $wpdb->update(
                    AP4L_TABLE_PREFIX . 'accounts',
                    array(
                            $changeMethod => $CheckBoxValue,
                            'updated_at'  => date('Y-m-d H:i:s')
                        ),
                    array( 'id' => $accID )
                );
                if ($updateRes === 1) {
                    $status_rep            = ($CheckBoxValue == 1) ? 'Activated' : 'Deactivated';
                    $response[ 'status' ]  = 'success';
                    $response[ 'message' ] = str_replace('%status%', $status_rep, $successmsg);
                }
            } else {
                $response = $access_token_res;
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * =====================
         * Account Delete Module
         * =====================
         */
        public function accountsDeleteAction()
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $accID    = sanitize_text_field($_REQUEST[ 'accID' ]);
            $response = array(
                'status'  => 'error',
                'message' => 'Account couldn\'t delete.',
            );
            $accounts = $this->removeAccountAll($accID);
            if (! empty($accounts)) {
                $response[ 'status' ]  = 'success';
                $response[ 'message' ] = 'Account successfully deleted.';
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * =====================
         * Account Create Module
         * =====================
         */
        public function accountsCreateAction()
        {
            global $wpdb;
            $response = array(
                'status'  => 'error',
                'message' => 'Something went wrong!!',
            );
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $formData = ap4l_sanitize_array($_REQUEST[ 'formData' ]);
            $accData  = array();
            foreach ($formData as $key => $value) {
                $accData[ $value[ 'name' ] ] = $value[ 'value' ];
            }
            $accName       = $accData[ 'accoutName' ];
            $emailID       = $accData[ 'emailID' ];
            $auth_token    = $accData[ 'authToken' ];
            $editAccID     = $accData[ 'formEditId' ];
            $syncOrders    = (!empty($accData[ 'syncOrders' ])) ? $accData[ 'syncOrders' ] : 0;
            $syncQty       = (!empty($accData[ 'syncOrders' ]) && !empty($accData[ 'syncQty' ])) ? $accData[ 'syncQty' ] : 0;
            $syncOrderDays = (!empty($accData[ 'syncOrderDays' ])) ? $accData[ 'syncOrderDays' ] : 60;
            $syncOrderFrom = (!empty($accData[ 'syncOrderFrom' ])) ? $accData[ 'syncOrderFrom' ] : '';
            $accStatus     = (!empty($accData[ 'accStatus' ])) ? $accData[ 'accStatus' ] : 1;
            $account       = $wpdb->get_row("SELECT * FROM " . AP4L_TABLE_PREFIX . "accounts WHERE email = '" . $emailID . "'", ARRAY_A);
            $accTok        = $this->getNewAccessToken($emailID, $auth_token);

            if (! empty($accData) && ( $accTok[ 'status' ] == 'success' ) && ( $accData[ 'formAction' ] == 'edit')) {
                $wpdb->update(
                    AP4L_TABLE_PREFIX . 'accounts',
                    array(
                            'title'           => $accName,
                            'email'           => $emailID,
                            'auth_token'      => $auth_token,
                            'sync_orders'     => $syncOrders,
                            'sync_quantity'   => $syncQty,
                            'sync_order_days' => $syncOrderDays,
                            'sync_order_date' => $syncOrderFrom,
                            'is_active'       => $accStatus,
                            'access_token'    => $accTok[ 'access_token' ],
                            'expires_at'      => $accTok[ 'expires_at' ],
                            'updated_at'      => date('Y-m-d H:i:s'),
                        ),
                    array(
                            'id' => $editAccID,
                        )
                );
                $response = array(
                    'status'  => 'success',
                    'message' => 'Account Updated Successfully',
                );
                $syncOrderFromOld = get_option('ap4l_sync_order_from_date_'.$editAccID);
                if (! empty($syncOrderFrom) && ! empty($editAccID) && ($syncOrderFrom != $syncOrderFromOld)) {
                    $update_array = array(
                        'ap4l_sync_order_notice_status_' => 1,
                        'ap4l_sync_order_from_date_'     => $syncOrderFrom,
                        'ap4l_sync_order_cur_page_'      => 1,
                        'ap4l_sync_order_max_page_'      => 4,
                    );
                    foreach ($update_array as $key => $value) {
                        $update_key = $key . '' . $editAccID;
                        update_option($update_key, $value);
                    }
                }
            } elseif (! empty($accData) && ( $accData[ 'formAction' ] == 'add')) {
                if ($account) {
                    $response[ 'message' ] = 'Account already exists!';
                } elseif ($accTok[ 'status' ] == 'success') {
                    $wpdb->insert(
                        AP4L_TABLE_PREFIX . 'accounts',
                        array(
                                'title'           => $accData[ 'accoutName' ],
                                'email'           => $emailID,
                                'auth_token'      => $auth_token,
                                'sync_orders'     => $syncOrders,
                                'sync_quantity'   => $syncQty,
                                'sync_order_days' => $syncOrderDays,
                                'sync_order_date' => $syncOrderFrom,
                                'is_active'       => 1,
                                'access_token'    => $accTok[ 'access_token' ],
                                'expires_at'      => $accTok[ 'expires_at' ],
                                'created_at'      => date('Y-m-d H:i:s'),
                                'updated_at'      => date('Y-m-d H:i:s'),
                            )
                    );
                    $account_id = $wpdb->insert_id;
                    $response   = array(
                        'status'  => 'success',
                        'message' => 'Account Created Successfully. Account Id: ' . $account_id,
                    );
                    if (! empty($syncOrderFrom) && ! empty($account_id)) {
                        $update_array = array(
                            'ap4l_sync_order_notice_status_' => 1,
                            'ap4l_sync_order_from_date_'     => $syncOrderFrom,
                            'ap4l_sync_order_cur_page_'      => 1,
                            'ap4l_sync_order_max_page_'      => 4,
                        );
                        foreach ($update_array as $key => $value) {
                            $update_key = $key . '' . $account_id;
                            update_option($update_key, $value);
                        }
                    }
                } else {
                    $response = $accTok;
                }
            } else {
                $response = $accTok;
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * ==================
         * All Policy Change
         * ==================
         */
        public function AllPolicyChange()
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $PolicyID      = sanitize_text_field($_REQUEST[ 'PolicyID' ]);
            $CheckBoxValue = sanitize_text_field($_REQUEST[ 'CheckBoxValue' ]);
            $successmsg    = sanitize_text_field($_REQUEST[ 'successmsg' ]);
            $response      = array(
                'status'  => 'error',
                'message' => 'You can not change value.',
            );
            if (! empty($PolicyID)) {
                $updateRes = $wpdb->update(
                    AP4L_TABLE_PREFIX . 'parent_policies',
                    array(
                            'status'     => $CheckBoxValue,
                            'updated_at' => current_time('Y-m-d H:i:s')
                        ),
                    array( 'id' => $PolicyID )
                );
                if ($updateRes === 1) {
                    $status_rep            = ($CheckBoxValue == 1) ? 'Activated' : 'Deactivated';
                    $response[ 'status' ]  = 'success';
                    $response[ 'message' ] = str_replace('%status%', $status_rep, $successmsg);
                }
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * =================
         * All Policy Delete
         * =================
         */
        public function AllPolicyDelete()
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $response = array(
                'status'  => 'error',
                'message' => 'error while deleting policy!!',
            );
            $polID    = sanitize_text_field($_REQUEST[ 'polID' ]);
            $polType  = sanitize_text_field($_REQUEST[ 'polType' ]);
            if (! empty($polID)) {
                $polDltRes = $wpdb->delete(
                    AP4L_TABLE_PREFIX . 'parent_policies',
                    array( 'id' => $polID, ),
                );
            }
            if ($polID && ($polType == 'synchronization')) {
                $polDltRes = $wpdb->delete(
                    AP4L_TABLE_PREFIX . 'sync_policy',
                    array( 'main_policy_id' => $polID, ),
                );
                if ($polDltRes) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'Policy Deleted Successfully.',
                    );
                }
            }
            if ($polID && ($polType == 'shipping')) {
                $polDltRes = $wpdb->delete(
                    AP4L_TABLE_PREFIX . 'shipping_policy',
                    array( 'main_policy_id' => $polID, ),
                );
                if ($polDltRes) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'Policy Deleted Successfully.',
                    );
                }
            }
            if ($polID && ($polType == 'selling')) {
                $polDltRes = $wpdb->delete(
                    AP4L_TABLE_PREFIX . 'selling_policy',
                    array( 'main_policy_id' => $polID, ),
                );
                if ($polDltRes) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'Policy Deleted Successfully.',
                    );
                }
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * ============================
         * Sync Policies Ajax Functions
         * ============================
         */
        public function SyncPolicyCreate()
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $response   = array(
                'status'  => 'error',
                'message' => 'Something went wrong!!!',
            );
            $formData   = ap4l_sanitize_array($_REQUEST[ 'formData' ]);
            $policyData = array();
            foreach ($formData as $key => $value) {
                $policyData[ $value[ 'name' ] ] = $value[ 'value' ];
            }
            if (! empty($policyData) && ( $policyData[ 'formAction' ] == 'edit')) {
                $main_update  = $wpdb->update(
                    AP4L_TABLE_PREFIX . 'parent_policies',
                    array(
                            'policy_name' => $policyData[ 'policyName' ],
                            'policy_type' => $policyData[ 'formType' ],
                            'status'      => isset($policyData[ 'policyStatus' ]) ? $policyData[ 'policyStatus' ] : 1,
                            'updated_at'  => current_time('Y-m-d H:i:s'),
                        ),
                    array(
                            'id' => $policyData[ 'formEditId' ]
                        )
                );
                $mainError    = $wpdb->last_error;
                $child_update = $wpdb->update(
                    AP4L_TABLE_PREFIX . 'sync_policy',
                    array(
                            'auto_sync_product'   => isset($policyData[ 'autoSyncProducts' ]) ? $policyData[ 'autoSyncProducts' ] : 0,
                            'auto_update_product' => isset($policyData[ 'autoUpdateProducts' ]) ? $policyData[ 'autoUpdateProducts' ] : 0,
                        ),
                    array(
                            'main_policy_id' => $policyData[ 'formEditId' ]
                        )
                );
                $childError   = $wpdb->last_error;
                if (($main_update || ! $mainError) && ($child_update || ! $childError)) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'Synchronization policy successfully edited.',
                    );
                }
            } elseif (! empty($policyData) && ( $policyData[ 'formAction' ] == 'add')) {
                $wpdb->insert(
                    AP4L_TABLE_PREFIX . 'parent_policies',
                    array(
                            'policy_name' => $policyData[ 'policyName' ],
                            'policy_type' => $policyData[ 'formType' ],
                            'status'      => isset($policyData[ 'policyStatus' ]) ? $policyData[ 'policyStatus' ] : 1,
                            'created_at'  => current_time('Y-m-d H:i:s'),
                            'updated_at'  => current_time('Y-m-d H:i:s'),
                        )
                );
                $policy_id       = $wpdb->insert_id;
                $wpdb->insert(
                    AP4L_TABLE_PREFIX . 'sync_policy',
                    array(
                            'main_policy_id'      => $policy_id,
                            'auto_sync_product'   => isset($policyData[ 'autoSyncProducts' ]) ? $policyData[ 'autoSyncProducts' ] : 0,
                            'auto_update_product' => isset($policyData[ 'autoUpdateProducts' ]) ? $policyData[ 'autoUpdateProducts' ] : 0,
                        )
                );
                $inner_policy_id = $wpdb->insert_id;
                if ($policy_id && $inner_policy_id) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'Synchronization policy successfully created.',
                    );
                }
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * ============================
         * Shipping Policies Ajax Functions
         * ============================
         */
        public function ShippingPolicyCreate()
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $response   = array(
                'status'  => 'error',
                'message' => 'Something went wrong!!!',
            );
            $formData   = ap4l_sanitize_array($_REQUEST[ 'formData' ]);
            $policyData = array();
            foreach ($formData as $key => $value) {
                $policyData[ $value[ 'name' ] ] = $value[ 'value' ];
            }
            if (! empty($policyData) && ( $policyData[ 'formAction' ] == 'edit')) {
                $main_update  = $wpdb->update(
                    AP4L_TABLE_PREFIX . 'parent_policies',
                    array(
                            'policy_name' => $policyData[ 'policyName' ],
                            'policy_type' => $policyData[ 'formType' ],
                            'status'      => isset($policyData[ 'policyStatus' ]) ? $policyData[ 'policyStatus' ] : 1,
                            'updated_at'  => current_time('Y-m-d H:i:s'),
                        ),
                    array(
                            'id' => $policyData[ 'formEditId' ]
                        )
                );
                $mainError    = $wpdb->last_error;
                $child_update = $wpdb->update(
                    AP4L_TABLE_PREFIX . 'shipping_policy',
                    array(
                            'ap4l_shipping_policy_id' => $policyData[ 'policyID' ],
                        ),
                    array(
                            'main_policy_id' => $policyData[ 'formEditId' ]
                        )
                );
                $childError   = $wpdb->last_error;
                if (($main_update || ! $mainError) && ($child_update || ! $childError)) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'Shipping policy successfully edited.',
                    );
                }
            } elseif (! empty($policyData) && ( $policyData[ 'formAction' ] == 'add')) {
                $wpdb->insert(
                    AP4L_TABLE_PREFIX . 'parent_policies',
                    array(
                            'policy_name' => $policyData[ 'policyName' ],
                            'policy_type' => $policyData[ 'formType' ],
                            'status'      => isset($policyData[ 'policyStatus' ]) ? $policyData[ 'policyStatus' ] : 1,
                            'created_at'  => current_time('Y-m-d H:i:s'),
                            'updated_at'  => current_time('Y-m-d H:i:s'),
                        )
                );
                $policy_id       = $wpdb->insert_id;
                $wpdb->insert(
                    AP4L_TABLE_PREFIX . 'shipping_policy',
                    array(
                            'main_policy_id'          => $policy_id,
                            'ap4l_shipping_policy_id' => $policyData[ 'policyID' ],
                        )
                );
                $inner_policy_id = $wpdb->insert_id;
                if ($policy_id && $inner_policy_id) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'Shipping policy successfully created.',
                    );
                }
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * ===============================
         * Selling Policies Ajax Functions
         * ===============================
         */
        public function SellingPolicyCreate()
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $response        = array(
                'status'  => 'error',
                'message' => 'Something went wrong!!!',
            );
            $formData        = ap4l_sanitize_array($_REQUEST[ 'formData' ]);
            $attributeValues = ap4l_sanitize_array($_REQUEST[ 'attributeValues' ]);
            $policyData      = array();

            foreach ($formData as $key => $value) {
                $policyData[ $value[ 'name' ] ] = $value[ 'value' ];
            }

            if (! empty($policyData) && ( $policyData[ 'formAction' ] == 'add')) {
                if (! empty($attributeValues)) {
                    $wpdb->insert(
                        AP4L_TABLE_PREFIX . 'parent_policies',
                        array(
                                'policy_name' => $policyData[ 'policyName' ],
                                'policy_type' => $policyData[ 'formType' ],
                                'status'      => isset($policyData[ 'policyStatus' ]) ? $policyData[ 'policyStatus' ] : 1,
                                'created_at'  => current_time('Y-m-d H:i:s'),
                                'updated_at'  => current_time('Y-m-d H:i:s'),
                            )
                    );
                    $policy_id = $wpdb->insert_id;
                    foreach ($attributeValues as $attrKey => $attrVal) {
                        $wpdb->insert(
                            AP4L_TABLE_PREFIX . 'selling_policy',
                            array(
                                    'main_policy_id'        => $policy_id,
                                    'product_attribute_id'  => $attrVal[ 'ap4l' ],
                                    'woocommerce_attribute' => $attrVal[ 'wc' ],
                                    'static_value'          => $attrVal[ 'staticvalue' ],
                                )
                        );
                        $inner_policy_id = $wpdb->insert_id;
                    }
                }
                if ($policy_id && $inner_policy_id) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'Selling policy successfully created.',
                    );
                }
            } elseif (! empty($policyData) && ( $policyData[ 'formAction' ] == 'edit')) {
                $main_update  = $wpdb->update(
                    AP4L_TABLE_PREFIX . 'parent_policies',
                    array(
                            'policy_name' => $policyData[ 'policyName' ],
                            'policy_type' => $policyData[ 'formType' ],
                            'status'      => ((!empty($policyData['policyStatus'])) ? $policyData['policyStatus'] : 1),
                            'updated_at'  => current_time('Y-m-d H:i:s'),
                        ),
                    array(
                            'id' => $policyData[ 'formEditId' ]
                        )
                );
                $mainError    = $wpdb->last_error;
                $NoChildError = true;

                if (! empty($attributeValues)) {
                    foreach ($attributeValues as $attrKey => $attrVal) {
                        $checkAttrRow = $wpdb->get_row("SELECT * FROM " . AP4L_TABLE_PREFIX . "selling_policy WHERE product_attribute_id = '" . $attrVal[ 'ap4l' ] . "' AND main_policy_id = '" . $policyData[ 'formEditId' ] . "'");
                        if ($checkAttrRow) {
                            $child_update = $wpdb->update(
                                AP4L_TABLE_PREFIX . 'selling_policy',
                                array(
                                        'product_attribute_id'  => $attrVal[ 'ap4l' ],
                                        'woocommerce_attribute' => $attrVal[ 'wc' ],
                                        'static_value'          => $attrVal[ 'staticvalue' ],
                                    ),
                                array(
                                        'product_attribute_id' => $attrVal[ 'ap4l' ],
                                        'main_policy_id'       => $policyData[ 'formEditId' ]
                                    )
                            );
                            $updateResult = $wpdb->last_error;

                            if ($child_update === false) {
                                $NoChildError = false;
                            }
                        } else {
                            $child_insert = $wpdb->insert(
                                AP4L_TABLE_PREFIX . 'selling_policy',
                                array(
                                        'main_policy_id'        => $policyData[ 'formEditId' ],
                                        'product_attribute_id'  => $attrVal[ 'ap4l' ],
                                        'woocommerce_attribute' => $attrVal[ 'wc' ],
                                        'static_value'          => $attrVal[ 'staticvalue' ],
                                    )
                            );
                            $insertResult = $wpdb->insert_id;

                            if ($child_insert === false || empty($insertResult)) {
                                $NoChildError = false;
                            }
                        }
                    }
                }

                if (($main_update || empty($mainError)) && !empty($NoChildError)) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'Selling policy successfully created.',
                    );
                }
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * ============================
         * Shipping Policies Ajax Functions
         * ============================
         */
        public function ListingCreate()
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $response    = array(
                'status'  => 'error',
                'message' => 'Something went wrong!!!',
            );
            $formData    = ap4l_sanitize_array($_REQUEST[ 'formData' ]);
            $listingData = array();
            foreach ($formData as $key => $value) {
                $listingData[ $value[ 'name' ] ] = $value[ 'value' ];
            }
            if (! empty($listingData) && ( $listingData[ 'formAction' ] == 'edit')) {
                $main_update = $wpdb->update(
                    AP4L_TABLE_PREFIX . 'listings',
                    array(
                            'listing_name'       => $listingData[ 'listingName' ],
                    //                            'seller_account_id'  => $listingData[ 'AccountId' ],
                            'shipping_policy_id' => $listingData[ 'ShippingPolicy' ],
                            'seller_policy_id'   => $listingData[ 'SellingPolicy' ],
                            'sync_policy_id'     => $listingData[ 'SyncPolicy' ],
                            'status'             => isset($listingData[ 'listingStatus' ]) ? $listingData[ 'listingStatus' ] : 1,
                            'updated_at'         => current_time('Y-m-d H:i:s'),
                        ),
                    array(
                            'id' => $listingData[ 'formEditId' ]
                        )
                );
                $mainError   = $wpdb->last_error;
                if (($main_update || ! $mainError )) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'Policy Updated Successfully!!',
                    );
                }
            } elseif (! empty($listingData) && ( $listingData[ 'formAction' ] == 'add')) {
                $wpdb->insert(
                    AP4L_TABLE_PREFIX . 'listings',
                    array(
                            'listing_name'       => $listingData[ 'listingName' ],
                            'seller_account_id'  => $listingData[ 'AccountId' ],
                            'shipping_policy_id' => $listingData[ 'ShippingPolicy' ],
                            'seller_policy_id'   => $listingData[ 'SellingPolicy' ],
                            'sync_policy_id'     => $listingData[ 'SyncPolicy' ],
                            'status'             => isset($listingData[ 'listingStatus' ]) ? $listingData[ 'listingStatus' ] : 1,
                            'created_at'         => current_time('Y-m-d H:i:s'),
                            'updated_at'         => current_time('Y-m-d H:i:s'),
                        )
                );
                $listing_id = $wpdb->insert_id;
                if ($listing_id) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'Listing Created Successfully!!',
                    );
                }
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * =================
         * Listing Delete
         * =================
         */
        public function ListingDelete()
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $response = array(
                'status'  => 'error',
                'message' => 'error while deleting listing!!',
            );
            $LisID    = sanitize_text_field($_REQUEST[ 'LisID' ]);
            if (! empty($LisID)) {
                $polDltRes = $this->removeListingAll($LisID);
                if ($polDltRes) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'Listing Deleted Successfully.',
                    );
                }
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * ==================
         * All Policy Change
         * ==================
         */
        public function ListingChange()
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $ListingID     = sanitize_text_field($_REQUEST[ 'ListingID' ]);
            $CheckBoxValue = sanitize_text_field($_REQUEST[ 'CheckBoxValue' ]);
            $successmsg    = sanitize_text_field($_REQUEST[ 'successmsg' ]);
            $response      = array(
                'status'  => 'error',
                'message' => 'You can not change value.',
            );
            if (! empty($ListingID)) {
                $updateRes = $wpdb->update(
                    AP4L_TABLE_PREFIX . 'listings',
                    array(
                            'status'     => $CheckBoxValue,
                            'updated_at' => current_time('Y-m-d H:i:s')
                        ),
                    array( 'id' => $ListingID )
                );
                if ($updateRes === 1) {
                    $status_rep            = ($CheckBoxValue == 1) ? 'Activated' : 'Deactivated';
                    $response[ 'status' ]  = 'success';
                    $response[ 'message' ] = str_replace('%status%', $status_rep, $successmsg);
                }
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * ============================
         * Listing Products Cookies
         * ============================
         */
        public function ListingProductCookie($param)
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $listingID = sanitize_text_field($_REQUEST[ 'listingID' ]);
            $response  = array(
                'status'  => 'error',
                'message' => 'Something went wrong!!!',
            );
            if ($listingID) {
                setcookie('productListingID', $listingID, time() + (60 * 5), "/");
                $response = array(
                    'status'  => 'success',
                    'message' => 'Redirect To A Product Listing Page.',
                );
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        public function ListingProductBtn($param)
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $listingID = isset($_COOKIE[ 'productListingID' ]) ? sanitize_text_field($_COOKIE[ 'productListingID' ]) : '';
            $response  = array(
                'status'  => 'error',
                'message' => 'Something went wrong!!!',
            );
            if ($listingID) {
                $all_listings = $wpdb->get_results("SELECT * FROM " . AP4L_TABLE_PREFIX . "listings WHERE id = '" . $listingID . "' order by id");
                if (! empty($all_listings)) {
                    $response = array(
                        'status'  => 'success',
                        'message' => 'cookie found successfully.',
                        'listID'  => $listingID,
                        'name'    => $all_listings[ 0 ]->listing_name,
                    );
                }
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        public function ListingProductAdd($param)
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $selectedProducts = ap4l_sanitize_array($_REQUEST[ 'selectedProducts' ]);
            $listingIdJs      = sanitize_text_field($_REQUEST[ 'listingIdJs' ]);
            $listingID        = ( ( ! empty($_COOKIE['productListingID']) ) ? sanitize_text_field($_COOKIE[ 'productListingID' ]) : $listingIdJs );
            $response         = array(
                'status'  => 'error',
                'message' => 'Something went wrong!!!',
                'redUrl'  => AP4L_LISTING_URL . '&viewlisting=' . $listingID . '&alert=error&msg=Something went wrong!!!',
            );
            if ($listingID && ! empty($selectedProducts)) {
                $ProductPolicies = $this->getListing($listingID, 1);
                $isAutoSyncOn = false;
                if (!empty($ProductPolicies)) {
                    $ProductPolicies   = $ProductPolicies[0];
                    $productSyncPolicy = $this->getPolicy('sync', $ProductPolicies->sync_policy_id, 1);
                    if (!empty($productSyncPolicy)) {
                        $ProductSyncedAllowed = $productSyncPolicy[0]->auto_sync_product;
                        if ($ProductSyncedAllowed) {
                            $isAutoSyncOn = true;
                        }
                    }
                }
                foreach ($selectedProducts as $key => $proID) {
                    update_post_meta($proID, 'ap4l_pro_listing', $listingID);
                    update_post_meta($proID, 'ap4l_pro_listing_date', current_time('Y-m-d H:i:s'));
//                    if ($isAutoSyncOn) {
//                    }
                    update_post_meta($proID, AP4L_PRODUCT_SYNCED, 0);
                }
                setcookie("productListingID", "", time() - 3600, "/");
                $response = array(
                    'status'  => 'success',
                    'message' => 'Products added successfully.',
                    'redUrl'  => AP4L_LISTING_URL . '&viewlisting=' . $listingID . '&alert=success&msg=Products added successfully.',
                );
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
        /*
         * ===================
         * Add Tracking action
         * ===================
         */
        public function AddTrackingOrderAP4L($param)
        {
            global $wpdb;
            check_ajax_referer(AP4L_PREFIX . 'nonce', 'security');
            $FormFields       = ap4l_sanitize_array($_REQUEST[ 'FormFields' ]);
            $response         = array(
                'status'  => 'error',
                'message' => 'Something went wrong!!!',
            );
            $FormFieldsUpdate = $returnObj        = array();
            if ($FormFields) {
                foreach ($FormFields as $key => $value) {
                    $FormFieldsUpdate[ $value[ 'name' ] ] = $value[ 'value' ];
                }
                $wpOrderID   = $FormFieldsUpdate[ 'wp_order_id' ];
                $ap4l_seller = $FormFieldsUpdate[ 'ap4l_seller' ];
                $bearer      = $this->getAccessToken($ap4l_seller);
                if ($bearer) {
                    $bearer        = $bearer[ 'accToken' ];
                    $apiPostData[] = $FormFieldsUpdate;
                    $apiData       = $this->elsaApiRequest('orders/add-order-tracking', [], true, $apiPostData, 'json', $bearer);
                    if (isset($apiData)) {
                        $responsehttpcode = $apiData[ 'httpcode' ];
                        $responseData     = $apiData[ 'data' ];
                        if (($responsehttpcode == 200 ) && ! empty($responseData)) {
                            $status = $responseData[ 0 ]->status;
                            if ($status === 'success') {
                                $response = array(
                                    'status'  => 'success',
                                    'message' => 'Tracking added successfully.',
                                );
                                update_post_meta($wpOrderID, 'ap4l_order_status', 'shipped');
                                $order    = wc_get_order($wpOrderID);
                                $note     = "AP4L Shipping Tracking Added. ID: " . $FormFieldsUpdate[ 'track_number' ];
                                if (! empty($FormFieldsUpdate[ 'carrier_code' ])) {
                                    $note .= " Carrir Code: " . $FormFieldsUpdate[ 'carrier_code' ] . " " . $FormFieldsUpdate[ 'shipping_service' ];
                                }
                                if (! empty($FormFieldsUpdate[ 'comment' ])) {
                                    $note .= " Comment: " . $FormFieldsUpdate[ 'comment' ];
                                }
                                $order->add_order_note($note);
                            } else {
                                $response = array(
                                    'status'  => 'error',
                                    'message' => 'Error : ' . implode(',', (array) $responseData[ 0 ]->errors),
                                );
                                $order    = wc_get_order($wpOrderID);
                                $note     = __("Error While Adding AP4L Shipping Tracking");
                                $order->add_order_note($note);
                            }
                        }
                    }
                }
            }
            header('Content-Type: application/json');
            echo wp_json_encode($response);
            exit();
        }
    }
}
