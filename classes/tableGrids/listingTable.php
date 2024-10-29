<?php
namespace Ap4l;

use \WP_List_Table;

if (! class_exists('listingTable') && ! class_exists('WP_List_Table')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class listingTable extends WP_List_Table
{
    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    function __construct()
    {
        parent::__construct(array(
            'singular' => 'id', //singular name of the listed records
            'plural'   => 'ids', //plural name of the listed records
            'ajax'     => false
        ));
    }
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'listing_name':
            case 'seller_name':
            case 'shipping_policy':
            case 'selling_policy':
            case 'sync_policy':
            case 'total_items':
            case 'act_items':
            case 'inact_items':
            case 'sold_qty':
//            case 'status':
                return "<span>" . $item[$column_name] . "</span>";
            default:
                return print_r($item, true);
        }
    }
    /**
     * Associative array of columns
     *
     * @return array
     */
    function get_columns()
    {
        $columns = [
            'cb'              => '<input type="checkbox" />',
            'id'              => __('ID', 'sp'),
            'listing_name'    => __('Listing Name', 'sp'),
            'seller_name'     => __('Account', 'sp'),
            'shipping_policy' => __('Shipping Policy', 'sp'),
            'selling_policy'  => __('Selling Policy', 'sp'),
            'sync_policy'     => __('Synchronization Policy', 'sp'),
            'total_items'     => __('Total items', 'sp'),
            'act_items'       => __('Active Items', 'sp'),
            'inact_items'     => __('Inactive Items', 'sp'),
            'sold_qty'        => __('Sold Quantity', 'sp'),
//            'status'          => __('Status', 'sp')
        ];
        return $columns;
    }
    /**

     * Decide which columns to activate the sorting functionality on

     * @return array $sortable, the array of columns that can be sorted by the user

     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id'  => array( 'id', true ),
            'listing_name'    => array( 'listing_name', true ),
            'seller_name'     => array( 'seller_name', true ),
            'shipping_policy' => array( 'shipping_policy', true ),
            'selling_policy'  => array( 'selling_policy', true ),
            'sync_policy'     => array( 'sync_policy', true ),
            'total_items'     => array( 'total_items', true ),
            'act_items'       => array( 'act_items', true ),
            'inact_items'     => array( 'inact_items', true ),
            'sold_qty'        => array( 'sold_qty', true ),
        );
        return $sortable_columns;
    }
    public function get_hidden_columns()
    {
        // Setup Hidden columns and return them
        return array();
    }
    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_listing_name($item)
    {
        $actions = array(
            'edit'                  => sprintf('<a href="' . AP4L_LISTING_URL . '&action=edit&id=%s">Edit</a>', $item['id']),
            'trash'                 => sprintf('<a href="javascript:void(0);" class="listingDlt" listing-id="' . $item['id'] . '">Delete</a>', $item['id']),
            'manage'                => sprintf('<a href="' . AP4L_LISTING_INNER_URL . '&viewlisting=%s&action=view" listing-id="' . $item['id'] . '">Manage</a>', $item['id']),
            'add_product_from_list' => sprintf('<a href="' . site_url() . '/wp-admin/edit.php?post_type=product" class="ajaxRed" listing-id="' . $item['id'] . '">Add From Products List</a>', $item['id']),
        );
        return sprintf('%1$s %2$s', $item['listing_name'], $this->row_actions($actions));
    }
    function get_bulk_actions()
    {
        $actions = array(
            'trash' => 'Delete'
        );
        return $actions;
    }
    protected function process_bulk_action()
    {
        global $wpdb;
        if ('trash' === $this->current_action()) {
            $selectedIDS = array();

            if (!empty($_REQUEST['id'])) {
                if (is_array($_REQUEST['id'])) {
                    $selectedIDS = ap4l_sanitize_array($_REQUEST['id']);
                } else {
                    $selectedIDS = array(sanitize_text_field($_REQUEST['id']));
                }
            }

            if (! empty($selectedIDS)) {
                $UserModal     = new UserModal();

                foreach ($selectedIDS as $key => $LisID) {
                    $polDltRes = $UserModal->removeListingAll($LisID);
                }
            }
        }
    }
    function extra_tablenav($which)
    {
        echo wp_kses('<div class="alignleft actions">', AP4L_ALLOWED_HTML);

        if ('top' === $which) {
            $accountId = (!empty($_REQUEST['accountId'])) ? sanitize_text_field($_REQUEST['accountId']) : '';
            $syncPolicy = (!empty($_REQUEST['syncPolicy'])) ? sanitize_text_field($_REQUEST['syncPolicy']) : '';
            $shipPolicy = (!empty($_REQUEST['shipPolicy'])) ? sanitize_text_field($_REQUEST['shipPolicy']) : '';
            $sellPolicy = (!empty($_REQUEST['sellPolicy'])) ? sanitize_text_field($_REQUEST['sellPolicy']) : '';

            $UserModal  = new UserModal();
            $accounts   = $UserModal->getAccounts();
            $policies   = $UserModal->getPolicies();
            $allPolicies = array();

            if (! empty($policies)) {
                foreach ($policies as $key => $value) {
                    $allPolicies[$value->policy_type][] = array(
                        'id'   => $value->id,
                        'name' => $value->policy_name,
                    );
                }
            }

            ob_start();

            if (! empty($accounts)) {
                ?>
                <select name="accountId" id="accountId" class="accountId">
                    <option value="">AP4L Account</option>
                    <?php foreach ($accounts as $key => $value) { ?>
                        <option value="<?php echo esc_attr($value->id); ?>" <?php selected($value->id, $accountId); ?>><?php echo esc_html($value->title); ?></option>
                    <?php } ?>
                </select>
                <?php
            }
            if (! empty($allPolicies['shipping'])) {
                ?>
                <select name="shipPolicy" id="shipPolicy" class="shipPolicy">
                    <option value="">AP4L Shipping Policy</option>
                    <?php foreach ($allPolicies['shipping'] as $key => $value) { ?>
                        <option value="<?php echo esc_attr($value['id']); ?>" <?php selected($value['id'], $shipPolicy); ?>><?php echo esc_html($value['name']); ?></option>
                    <?php } ?>
                </select>
                <?php
            }
            if (! empty($allPolicies['selling'])) {
                ?>
                <select name="sellPolicy" id="sellPolicy" class="sellPolicy">
                    <option value="">AP4L Selling Policy</option>
                    <?php foreach ($allPolicies['selling'] as $key => $value) { ?>
                        <option value="<?php echo esc_attr($value['id']); ?>" <?php selected($value['id'], $sellPolicy); ?>><?php echo esc_html($value['name']); ?></option>
                    <?php } ?>
                </select>
                <?php
            }
            if (! empty($allPolicies['synchronization'])) {
                ?>
                <select name="syncPolicy" id="syncPolicy" class="syncPolicy">
                    <option value="">AP4L Sync Policy</option>
                    <?php foreach ($allPolicies['synchronization'] as $key => $value) { ?>
                        <option value="<?php echo esc_attr($value['id']); ?>" <?php selected($value['id'], $syncPolicy); ?>><?php echo esc_html($value['name']); ?></option>
                    <?php } ?>
                </select>
                <?php
            }
            do_action('restrict_manage_comments');
            $output = ob_get_clean();
            if (! empty($output)) {
                echo wp_kses($output, AP4L_ALLOWED_HTML);
                submit_button(__('Filter'), '', 'filter_action', false, array( 'id' => 'post-query-submit' ));
            }
        }
        echo wp_kses('</div>', AP4L_ALLOWED_HTML);
    }
    protected function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'], // Let's simply repurpose the table's singular label ("movie").
            $item['id']                // The value of the checkbox should be the record's ID.
        );
    }
    private function table_data()
    {
        global $wpdb;

        $UserModal  = new UserModal();

        $accountId  = (!empty($_REQUEST['accountId'])) ? sanitize_text_field($_REQUEST['accountId']) : '';
        $shipPolicy = (!empty($_REQUEST['shipPolicy'])) ? sanitize_text_field($_REQUEST['shipPolicy']) : '';
        $sellPolicy = (!empty($_REQUEST['sellPolicy'])) ? sanitize_text_field($_REQUEST['sellPolicy']) : '';
        $syncPolicy = (!empty($_REQUEST['syncPolicy'])) ? sanitize_text_field($_REQUEST['syncPolicy']) : '';

        $getListing = $UserModal->getListing(null, null, $accountId, $shipPolicy, $sellPolicy, $syncPolicy);
        $accounts   = $UserModal->getAccounts();
        $policies   = $UserModal->getPolicies();
        $allAccount = array();
        $data       = array();

        if (! empty($accounts)) {
            foreach ($accounts as $key => $value) {
                $allAccount[$value->id] = $value->title;
            }
        }

        $allPolicies     = array();
        $allPoliciesName = array();

        if (! empty($policies)) {
            foreach ($policies as $key => $value) {
                $allPoliciesName[$value->id]        = $value->policy_name;
                $allPolicies[$value->policy_type][] = array(
                    'id'   => $value->id,
                    'name' => $value->policy_name,
                );
            }
        }
        foreach ($getListing as $getListingVal) {
            $list_id            = $getListingVal->id;
            $listing_name       = $getListingVal->listing_name;
            $seller_account_id  = key_exists($getListingVal->seller_account_id, $allAccount) ? $allAccount[$getListingVal->seller_account_id] : '-';
            $shipping_policy_id = key_exists($getListingVal->shipping_policy_id, $allPoliciesName) ? $allPoliciesName[$getListingVal->shipping_policy_id] : '-';
            $seller_policy_id   = key_exists($getListingVal->seller_policy_id, $allPoliciesName) ? $allPoliciesName[$getListingVal->seller_policy_id] : '-';
            $sync_policy_id     = key_exists($getListingVal->sync_policy_id, $allPoliciesName) ? $allPoliciesName[$getListingVal->sync_policy_id] : '-';
            $status             = $getListingVal->status;
            $allPro             = $UserModal->getListingProducts($list_id);
            $total              = count($allPro);
            $act                = count($UserModal->getListingProducts($list_id, true));
            $inact              = ($total > 0 ) ? ($total - $act) : 0;
            $inactiveProducts   = $UserModal->getInactiveProducts($allPro);
            $total               = $act + count($inactiveProducts);
            $total_orders       = 0;
            if (! empty($allPro)) {
                foreach ($allPro as $key => $value) {
                    $total_orders += count($UserModal->get_orders_ids_by_product_id($value));
                }
            }
            $data[] = array(
                'id'              => $list_id,
                'listing_name'    => '<a class="row-title" href="' . AP4L_LISTING_URL . '&action=edit&id=' . $list_id . '">' . $listing_name . '</a>',
                'seller_name'     => $seller_account_id,
                'shipping_policy' => $shipping_policy_id,
                'selling_policy'  => $seller_policy_id,
                'sync_policy'     => $sync_policy_id,
                'total_items'     => $total,
                'act_items'       => $act,
                'inact_items'     => count($inactiveProducts),
                'sold_qty'        => $total_orders,
//                'status'          => '<input type="checkbox" listing_id="' . $list_id . '" successmsg="Listing Successfully %status%." class="formEditMethod listingStatusChange" id="listingStatusChange' . $status . '" name="listingStatusChange" ' . checked($status, 1, false) . ' value="' . $status . '" />'
            );
        }
        return $data;
    }
    public function prepare_items()
    {
        global $wpdb;
        $columns               = $this->get_columns();
        $sortable              = $this->get_sortable_columns();
        $hidden                = $this->get_hidden_columns();
        $this->process_bulk_action();
        $data                  = $this->table_data();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        if (! empty($data)) {
            $totalitems = count($data);
            $user       = get_current_user_id();
            $screen     = get_current_screen();

            $option     = $screen->get_option('per_page', 'option');
            $per_page    = (!empty($option)) ? get_user_meta($user, $option, true) : 10;
            $per_page    = (!empty($per_page) && !is_array($per_page) && !is_object($per_page)) ? intval($per_page) : 10;

            if (empty($per_page)) {
                $per_page = $screen->get_option('per_page', 'default');
            }

            usort($data, function ($a, $b) {
                $orderby = ( ! empty($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : 'id'; //If no sort, default to title
                $order   = ( ! empty($_REQUEST['order'])) ? sanitize_text_field($_REQUEST['order']) : 'desc'; //If no order, default to asc

                if ($orderby === 'id' && $order == 'asc') {
                    $result = $a[$orderby] < $b[$orderby] ? -1 : 1; //Determine sort order
                    return $result;
                } elseif ($orderby === 'id' && $order == 'desc') {
                    $result = $a[$orderby] > $b[$orderby] ? -1 : 1; //Determine sort order
                    return $result;
                } else {
                    $result = strcmp($a[$orderby], $b[$orderby]) ? -1 : 1; //Determine sort order
                    return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
                }
            });

            if ($totalitems > 0 && $per_page > 0) {
                $totalpages = ceil($totalitems / $per_page);
            } else {
                $totalpages = 0;
            }

            $currentPage = $this->get_pagenum();

            if (!empty($data)) {
                $data = array_slice($data, (($currentPage - 1) * $per_page), $per_page);
            }

            $this->set_pagination_args(array(
                "total_items" => $totalitems,
                "total_pages" => $totalpages,
                "per_page"    => $per_page,
            ));
        }
        $this->items = $data;
    }
}