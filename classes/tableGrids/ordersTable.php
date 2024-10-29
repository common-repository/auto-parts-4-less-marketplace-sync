<?php
namespace Ap4l;

if (! class_exists('ordersTable') && ! class_exists('WP_List_Table')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if (!class_exists('UserModal')) {
    include_once AP4L_DIR . 'classes/UserModal.php';
}

use \WP_List_Table;
use Ap4l\UserModal;

class ordersTable extends WP_List_Table
{
    private $UserModal;

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    function __construct()
    {
        parent::__construct(array(
            'singular' => 'singular_name', //singular name of the listed records
            'plural'   => 'plural_name', //plural name of the listed records
            'ajax'     => false
        ));

        $this->UserModal = new UserModal();
    }
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'order_date':
            case 'wc_order_id':
            case 'ap4l_order_id':
            case 'items':
            case 'buyer':
            case 'account':
            case 'total':
            case 'status':
                return "<span>" . ucwords($item[$column_name]) . "</span>";
            default:
                return print_r(ucwords($item), true);
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
//            'cb'            => '<input type="checkbox" />',
            'id'            => __('Id', 'sp'),
            'order_date'    => __('Order Date', 'sp'),
            'wc_order_id'   => __('WC Order ID', 'sp'),
            'ap4l_order_id' => __('AP4L Order ID', 'sp'),
            'items'         => __('Product Name', 'sp'),
            'buyer'         => __('Buyer', 'sp'),
            'account'       => __('Account', 'sp'),
            'total'         => __('Total', 'sp'),
            'status'        => __('AP4L Status', 'sp'),
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
            'id'            => array( 'id', true ),
            // 'order_date'    => array( 'order_date', true ),
            'wc_order_id' => array( 'wc_order_id', true ),
            'ap4l_order_id' => array( 'ap4l_order_id', true ),
            'items'         => array( 'items', true ),
            'buyer'         => array( 'buyer', true ),
            'account'       => array( 'account', true ),
            'total'         => array( 'total', true ),
            'status'        => array( 'status', true )
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
    function column_wc_order_id($item)
    {
        $actions = array(
//            'edit'  => sprintf('<a href="' . get_edit_post_link($item[ 'wc_order_id_no' ]) . '">Edit</a>'),
//            'trash' => sprintf('<a href="javascript:void(0);">Delete</a>'),
        );
        return sprintf('%1$s %2$s', $item['wc_order_id'], $this->row_actions($actions));
    }
    function get_bulk_actions()
    {
        $actions = array(
//            'trash' => 'Delete'
        );
        return $actions;
    }
    function extra_tablenav($which)
    {
        echo wp_kses('<div class="alignleft actions">', AP4L_ALLOWED_HTML);
        if ('top' === $which) {
            $orderStatusFilter = (!empty($_REQUEST[ 'ap4lStatus' ])) ? sanitize_text_field($_REQUEST[ 'ap4lStatus' ]) : '';
            $accountId         = (!empty($_REQUEST[ 'accountId' ])) ? sanitize_text_field($_REQUEST[ 'accountId' ]) : '';

            $UserModal         = new UserModal();
            $accounts          = $UserModal->getAccounts();
            $orderStatus       = array(
                'new'       => 'New',
                'shipped'   => 'Shipped',
                'delivered' => 'Delivered',
                'canceled'  => 'Canceled',
            );
            ob_start();
            ?>

            <select name="ap4lStatus" id="ap4lStatus" class="ap4lStatus">
                <option value="">AP4L Status</option>
                <?php foreach ($orderStatus as $key => $value) { ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $orderStatusFilter); ?>><?php echo esc_html($value); ?></option>
                <?php } ?>
            </select>

            <?php
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
            do_action('restrict_manage_comments');
            $output = ob_get_clean();
            if (! empty($output)) {
                echo wp_kses($output, AP4L_ALLOWED_HTML);
                submit_button(__('Filter'), '', 'filter_action', false, array( 'id' => 'post-query-submit' ));
            }
        }
        echo wp_kses('</div>', AP4L_ALLOWED_HTML);
    }
    public function search_box($text, $input_id)
    {
        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>"><?php echo wp_kses($text, AP4L_ALLOWED_HTML); ?>:</label>
            <input type="search" id="<?php echo esc_attr($input_id); ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button($text, '', '', false, array( 'id' => 'search-submit' )); ?>
        </p>
        <?php
    }
    protected function process_bulk_action()
    {
        global $wpdb;
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
        $orderFunctions    = new OrderFunctions();

        $orderStatusFilter = (!empty($_REQUEST['ap4lStatus'])) ? sanitize_text_field($_REQUEST['ap4lStatus']) : '';
        $accountId         = (!empty($_REQUEST[ 'accountId' ])) ? sanitize_text_field($_REQUEST[ 'accountId' ]) : '';
        $searchword  = (!empty($_REQUEST[ 's' ])) ? sanitize_text_field($_REQUEST[ 's' ]) : '';

//        $ap4lOrders        = $orderFunctions->GetAP4LWooOrders($orderStatusFilter);
        $ap4lOrders       = $orderFunctions->getAP4LOrders($orderStatusFilter, $accountId, $searchword);
        $accounts          = $orderFunctions->getAccounts();
        $accounts          = $orderFunctions->getAccounts();
        $allAccount        = array();
        $data              = array();
        if (! empty($accounts)) {
            foreach ($accounts as $key => $value) {
                $allAccount[$value->id] = $value->title;
            }
        }
        foreach ($ap4lOrders as $key => $order_details) {
            $order_id    = $order_details->wc_order_id;
            $orderObj    = wc_get_order($order_id);
            $sellerId    = get_post_meta($order_id, 'ap4l_seller', true);
            $sellerName  = (!empty($sellerId) && !empty($allAccount[$sellerId])) ? $allAccount[$sellerId] : '-';
            $order_Items = '';
            foreach ($orderObj->get_items() as $item_id => $item) {
                $product_name = $item->get_name();
                $product_id   = $item->get_product_id();
                $quantity     = $item->get_quantity();
                $sku          = get_post_meta($product_id, '_sku', true);
                ob_start();
                ?>
                <p><span>Name: </span><?php echo wp_kses($product_name, AP4L_ALLOWED_HTML); ?></p>
                <?php if (! empty($sku)) { ?>
                    <p><span>SKU: </span><?php echo wp_kses($sku, AP4L_ALLOWED_HTML); ?></p>
                <?php } ?>
                <p><span>QTY: </span><?php echo wp_kses($quantity, AP4L_ALLOWED_HTML); ?></p>
                <?php
                $order_Items  .= ob_get_clean();
            }
            $data[] = array(
                'id'             => $order_id,
                'order_date'     => $this->UserModal->getTimeFormated(str_replace(array('T', '+00:00'), array(' ', ''), $orderObj->get_date_created())),
                'wc_order_id'    => '<a class="row-title" href="' . get_edit_post_link($order_id) . '">#' . $order_id . '</a>',
                'wc_order_id_no' => $order_id,
                'ap4l_order_id'  => get_post_meta($order_id, 'ap4l_order_id', true),
                'items'          => $order_Items,
                'buyer'          => $orderObj->get_billing_first_name() . ' ' . $orderObj->get_billing_last_name(),
                'account'        => $sellerName,
                'total'          => $orderObj->get_formatted_order_total(),
                'status'         => get_post_meta($order_id, 'ap4l_order_status', true),
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
        $totalitems            = count($data);
        $user                  = get_current_user_id();
        $screen                = get_current_screen();
        $this->_column_headers = array( $columns, $hidden, $sortable );

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
            $totalpages  = ceil($totalitems / $per_page);
        } else {
            $totalpages  = 0;
        }

        $currentPage = $this->get_pagenum();

        if (!empty($data)) {
            $data        = array_slice($data, (($currentPage - 1) * $per_page), $per_page);
        }

        $this->set_pagination_args(array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page"    => $per_page,
        ));

        $this->items = $data;
    }
}