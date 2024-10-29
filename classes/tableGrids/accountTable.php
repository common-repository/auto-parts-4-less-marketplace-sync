<?php
namespace Ap4l;

use \WP_List_Table;

if (! class_exists('accountTable') && ! class_exists('WP_List_Table')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class accountTable extends WP_List_Table
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
            case 'title':
            case 'email':
            case 'sync_orders':
            case 'sync_qty':
                return "<span>" . $item[$column_name] . "<span>";
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
            'cb'          => '<input type="checkbox" />',
            'id'          => __('ID', 'sp'),
            'title'       => __('Account Title', 'sp'),
            'email'       => __('Seller Email', 'sp'),
            'sync_orders' => __('Sync Orders?', 'sp'),
            'sync_qty'    => __('Sync Quantity?', 'sp')
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
            'id'    => array( 'id', true ),
            'title' => array( 'title', true ),
            'email' => array( 'email', true ),
            'sync_orders' => array( 'sync_orders', true ),
            'sync_qty' => array( 'sync_qty', true ),
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
    function column_title($item)
    {
        $actions = array(
            'edit'  => sprintf('<a href="' . AP4L_ACCOUNT_URL . '&action=edit&id=%s">Edit</a>', $item['id']),
            'trash' => sprintf('<a href="javascript:void(0);" class="accountDeleteBtn" data-id="' . $item['id'] . '">Delete</a>', $item['id']),
        );
        return sprintf('%1$s %2$s', $item['title'], $this->row_actions($actions));
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
        $UserModal = new UserModal();

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
                foreach ($selectedIDS as $key => $accID) {
                    $polDltRes = $UserModal->removeAccountAll($accID);
                }
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
        $UserModal       = new UserModal();
        $accounts        = $UserModal->getAccounts();
        $selectionOption = array(
            1 => 'Yes',
            0 => 'No'
        );
        $data            = array();
        foreach ($accounts as $accountsVal) {
            $id          = $accountsVal->id;
            $title       = $accountsVal->title;
            $active      = $accountsVal->is_active;
            $sync_orders = ($accountsVal->sync_orders == 1) ? 'Yes' : 'No';
            $sync_qty    = ($accountsVal->sync_quantity == 1) ? 'Yes' : 'No';
//            $sync_order_html = '<select class="formEditMethod" account_id="' . $id . '" dataType="sync_orders" successmsg="Sync Order Successfully %status%.">';
//            foreach ( $selectionOption as $key => $value ) {
//                $sync_order_html .= '<option value="' . $key . '" "' . selected($key, $sync_orders, false) . '">' . $value . '</option>';
//            }
//            $sync_order_html .= '</select>';
//            $sync_qty_html   = '<select class="formEditMethod" account_id="' . $id . '" dataType="sync_quantity" successmsg="Sync Qty Successfully %status%.">';
//            foreach ( $selectionOption as $key => $value ) {
//                $sync_qty_html .= '<option value="' . $key . '" "' . selected($key, $sync_qty, false) . '">' . $value . '</option>';
//            }
//            $sync_qty_html .= '</select>';
            $data[]      = array(
                'id'          => $id,
                'title'       => '<a class="row-title" href="' . AP4L_ACCOUNT_URL . '&action=edit&id=' . $id . '">' . $title . '</a>',
                'email'       => $accountsVal->email,
                'auth_token'  => $accountsVal->auth_token,
                'active'      => '<input type="checkbox" dataType="is_active" account_id="' . $id . '" successmsg="Account Successfully %status%." class="formEditMethod is_active" id="listingStatusChange' . $active . '" name="listingStatusChange" ' . checked($active, 1, false) . ' value="' . $active . '" />',
                'sync_orders' => $sync_orders,
                'sync_qty'    => $sync_qty,
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
                $orderby = ( ! empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
                $order   = ( ! empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc

                if ($orderby === 'id' && $order === 'asc') {
                    $result = $a[$orderby] < $b[$orderby] ? -1 : 1; //Determine sort order
                    return $result;
                } elseif ($orderby === 'id' && $order === 'desc') {
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
