<?php
namespace Ap4l;

use \WP_List_Table;

if (! class_exists('productTable') && ! class_exists('WP_List_Table')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class productTable extends WP_List_Table
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
            case 'name':
            case 'image':
            case 'upc':
            case 'sku':
            case 'buyer':
            case 'price':
            case 'total':
            case 'quantity':
            case 'listed_date':
            case 'ap4l_status':
                return "<span>" . $item[$column_name] . "</span>";
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
            'cb'          => '<input type="checkbox" />',
            'image'       => __('<span class="wc-image tips">Image</span>', 'sp'),
            'name'        => __('Name', 'sp'),
            'upc'         => __('UPC', 'sp'),
            'sku'         => __('SKU', 'sp'),
            'price'       => __('Price', 'sp'),
            'quantity'    => __('Available Stock', 'sp'),
            'listed_date' => __('Listed Date', 'sp'),
            'ap4l_status' => __('AP4L List Status', 'sp'),
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
            'name'        => __('Name', 'sp'),
            'upc'         => __('UPC', 'sp'),
            'sku'         => __('SKU', 'sp'),
            'price'       => __('Price', 'sp'),
            'quantity'    => __('Available Stock', 'sp'),
            // 'listed_date' => __('Listed Date', 'sp'),
            'ap4l_status' => __('AP4L List Status', 'sp'),
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
    function column_name($item)
    {
        $actions = array(
            'id'   => sprintf('ID : %s', $item['id']),
            'edit' => sprintf('<a href="' . get_edit_post_link($item['id']) . '">Edit</a>'),
//            'remove' => sprintf('<a href="javascript:void(0);" class="listItemDlt" pro-id="' . $item['id'] . '">Remove From List</a>'),
        );
        return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions));
    }
    function get_bulk_actions()
    {
        $actions = array(
            'update' => 'Update Item(s) on ' . AP4L_NAME,
            'relist' => 'ReList Item(s) on ' . AP4L_NAME,
            'stop' => 'Stop Item(s) on ' . AP4L_NAME
//            'remove' => 'Remove From Listing'
        );
        return $actions;
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

        $selectedIDS = array();

        if (!empty($_REQUEST['id'])) {
            if (is_array($_REQUEST['id'])) {
                $selectedIDS = ap4l_sanitize_array($_REQUEST['id']);
            } else {
                $selectedIDS = array(sanitize_text_field($_REQUEST['id']));
            }
        }

        $UserModal = new UserModal();
        $viewlisting = (!empty($_REQUEST['viewlisting'])) ? sanitize_text_field($_REQUEST['viewlisting']) : '';

        if (! empty($selectedIDS)) {
            if ('remove' === $this->current_action()) {
                foreach ($selectedIDS as $id) {
                    if (! empty($id)) {
                        delete_post_meta($id, 'ap4l_pro_listing');
                    }
                }
            } elseif ('update' === $this->current_action()) {
                foreach ($selectedIDS as $id) {
                    if (! empty($id)) {
                        update_post_meta($id, AP4L_PRODUCT_NEEDS_UPDATE, 1);
                    }
                }
                $UserModal->createProductsInAP4L($selectedIDS);
            } elseif ('relist' === $this->current_action()) {
                $UserModal->ap4lProductBulkFunction($selectedIDS, 'relist', $viewlisting);
            } elseif ('stop' === $this->current_action()) {
                $UserModal->ap4lProductBulkFunction($selectedIDS, 'stop', $viewlisting);
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

        $searchword  = (!empty($_REQUEST[ 's' ])) ? sanitize_text_field($_REQUEST['s']) : '';
        $listingID  = (!empty($_REQUEST['viewlisting'])) ? sanitize_text_field($_REQUEST['viewlisting']) : 0;

        $UserModal  = new UserModal();

        if (!empty($listingID)) {
            $productIDs = $UserModal->getListingProducts($listingID, false, $searchword);
        } else {
            $productIDs = array();
        }

        $data = array();

        if (!empty($productIDs)) {
            foreach ($productIDs as $key => $proID) {
                $productObj       = wc_get_product($proID);
                $pro_img          = get_the_post_thumbnail_url($proID, array( 100, 100 ));
                $pro_listing_date = get_post_meta($proID, 'ap4l_pro_listing_date', true);
                $pro_listing_date = ! empty($pro_listing_date) ? $UserModal->getTimeFormated($pro_listing_date) : '-';
                $proUPC           = $UserModal->getProductUPC($proID);
                $proSKU           = get_post_meta($proID, '_sku', true);
                $proSynced        = get_post_meta($proID, AP4L_PRODUCT_SYNCED, true);
                $status           = ! empty($proSynced) ? 'Listed in AP4L' : 'Not In AP4L';
                $pro_listing_status = get_post_meta($proID, 'ap4l_product_status', true);
                $pro_listing_status = ! empty($pro_listing_status) ? ucwords($pro_listing_status) : ($proSynced ? 'Listed' : 'Pending');
                $image_html       = '';
                if ($pro_img) {
                    $image_html = '<a href="' . get_edit_post_link($proID) . '"><img width="150" height="150" style="max-width: 40px;max-height: 40px;" src="' . $pro_img . '" class="attachment-thumbnail size-thumbnail" alt=""></a>';
                }
                $data[] = array(
                    'id'          => $proID,
                    'image'       => $image_html,
                    'name'        => '<a class="row-title" href="' . get_edit_post_link($proID) . '">' . get_the_title($proID) . '</a>',
                    'upc'         => $proUPC,
                    'sku'         => $proSKU,
                    'price'       => $productObj->get_price_html(),
                    'quantity'    => $productObj->get_stock_quantity(),
                    'listed_date' => $pro_listing_date,
                    'ap4l_status' => $pro_listing_status,
                    'sync_status' => $status
                );
            }
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