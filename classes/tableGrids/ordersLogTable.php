<?php
namespace Ap4l;

use \WP_List_Table;

if (! class_exists('ordersLogTable') && ! class_exists('WP_List_Table')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class ordersLogTable extends WP_List_Table
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
            case 'request_at':
            case 'api_endpoint':
            case 'seller_id':
            case 'request_at':
            case 'api_response':
            case 'response_at':
            case 'resposne_code':
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
            'cb'            => '<input type="checkbox" />',
            'id'            => __('Id', 'sp'),
            'request_at'    => __('Request At', 'sp'),
            'api_endpoint'  => __('API Endpoint', 'sp'),
            'seller_id'     => __('Seller', 'sp'),
            'response_at'   => __('Responce At', 'sp'),
            'resposne_code' => __('Responce Code', 'sp'),
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
            'id'           => array( 'id', true ),
            // 'request_at'   => array( 'request_at', true ),
            'api_endpoint' => array( 'api_endpoint', true ),
            'seller_id' => array( 'seller_id', true ),
            // 'response_at' => array( 'response_at', true ),
            'resposne_code' => array( 'resposne_code', true ),
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
    function column_api_endpoint($item)
    {
        $actions = array(
            'edit' => sprintf('<a href="' . AP4L_ORDERS_LOGS_URL . '&action=view&logid=' . $item['id'] . '">More Detail</a>'),
//            'trash' => sprintf('<a href="javascript:void(0);">Delete</a>'),
        );
        return sprintf('%1$s %2$s', $item['api_endpoint'], $this->row_actions($actions));
    }
    function get_bulk_actions()
    {
        $actions = array(
            'trash' => 'Delete'
        );
        return $actions;
    }
    function extra_tablenav($which)
    {
        echo wp_kses('<div class="alignleft actions">', AP4L_ALLOWED_HTML);
        if ('top' === $which) {
            $accountId  = (!empty($_REQUEST['accountId'])) ? sanitize_text_field($_REQUEST['accountId']) : '';
            $apiEnd      = (!empty($_REQUEST[ 'apiEnd' ])) ? sanitize_url($_REQUEST[ 'apiEnd' ]) : '';
            $apiRes      = (!empty($_REQUEST[ 'apiRes' ])) ? sanitize_text_field($_REQUEST[ 'apiRes' ]) : '';

            $UserModal  = new UserModal();
            $accounts   = $UserModal->getAccounts();
            $apiEndA   = array(
                'orders'              => 'orders',
                'orders/full-details' => 'orders/full-details',
            );
            $apiResA       = array(
                '200' => '200',
                '401' => '401',
                '402' => '402',
                '403' => '403',
                '500' => '500',
                '501' => '501',
                '502' => '502',
                '503' => '503',
            );

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

            if (! empty($apiEndA)) {
                ?>
                <select name="apiEnd" id="apiEnd" class="apiEnd">
                    <option value="">AP4L API EndPoint</option>
                    <?php foreach ($apiEndA as $key => $value) { ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $apiEnd); ?>><?php echo esc_html($value); ?></option>
                    <?php } ?>
                </select>
                <?php
            }
            if (! empty($apiResA)) {
                ?>
                <select name="apiRes" id="apiRes" class="apiRes">
                    <option value="">AP4L API Response Code</option>
                    <?php foreach ($apiResA as $key => $value) { ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $apiRes); ?>><?php echo esc_html($value); ?></option>
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
                $removeEntries = $UserModal->removeOrderLogs($selectedIDS);
            }
        }
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

        $accountId   = (!empty($_REQUEST[ 'accountId' ])) ? sanitize_text_field($_REQUEST['accountId']) : '';
        $apiEnd      = (!empty($_REQUEST[ 'apiEnd' ])) ? sanitize_text_field($_REQUEST[ 'apiEnd' ]) : '';
        $apiRes      = (!empty($_REQUEST[ 'apiRes' ])) ? sanitize_text_field($_REQUEST[ 'apiRes' ]) : '';
        $searchword  = (!empty($_REQUEST[ 's' ])) ? sanitize_text_field($_REQUEST[ 's' ]) : '';

        $orderFunctions = new OrderFunctions();
        $allOrderLogs   = $orderFunctions->getAllOrderLogs('', $accountId, $apiEnd, $apiRes, $searchword);
        $data           = array();
        foreach ($allOrderLogs as $key => $value) {
            $seller_id     = $value->seller_id;
            $seller_detail = '';
            if (! empty($seller_id)) {
                $seller_info   = $orderFunctions->getAccounts($seller_id);
                if (! empty($seller_info) && key_exists(0, $seller_info)) {
                    $seller_detail = '<a class="row-title" href="' . AP4L_ACCOUNT_URL . '&action=edit&id=' . $seller_id . '">' . $seller_info[0]->title . '</a>';
                    $seller_detail .= "<p><span>ID : </span>" . $seller_id . "</p>";
                }
            }
            $data[] = array(
                'id'            => $value->id,
                'response_at'   => $orderFunctions->getTimeFormated($value->request_at),
                'api_endpoint'  => $value->api_endpoint,
                'seller_id'     => $seller_detail,
                'api_payload'   => $value->api_payload,
                'request_at'    => $orderFunctions->getTimeFormated($value->request_at),
                'api_response'  => $value->api_response,
                'resposne_code' => $value->resposne_code,
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