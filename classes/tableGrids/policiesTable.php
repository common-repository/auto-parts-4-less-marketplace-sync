<?php
namespace Ap4l;

use \WP_List_Table;

if (! class_exists('policiesTable') && ! class_exists('WP_List_Table')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class policiesTable extends WP_List_Table
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
            case 'policy_name':
            case 'policy_type':
            case 'created_at':
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
            'cb'          => '<input type="checkbox" />',
            'id'          => __('Id', 'sp'),
            'policy_name' => __('Policy Name', 'sp'),
            'policy_type' => __('Policy Type', 'sp'),
            'created_at'  => __('Created At', 'sp'),
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
            'id'          => array( 'id', true ),
            'policy_name' => array( 'policy_name', true ),
            'policy_type' => array( 'policy_type', true ),
            // 'created_at'  => array( 'created_at', true )
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
    function column_policy_name($item)
    {
        $actions = array(
            'edit'  => sprintf('<a href="' . AP4L_POLICY_URL . '&action=edit&id=%s&policy=%s">Edit</a>', $item['id'], $item['policy_type']),
            'trash' => sprintf('<a href="javascript:void(0);" policy-type="' . $item['policy_type'] . '" class="policyDlt" policy-id="' . $item['id'] . '">Delete</a>', $item['id']),
        );
        return sprintf('%1$s %2$s', $item['policy_name'], $this->row_actions($actions));
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
            $policyTypeFilter = (!empty($_REQUEST['policyType'])) ? sanitize_text_field($_REQUEST['policyType']) : '';

            $policyType       = array(
                'synchronization' => 'Synchronization',
                'shipping'        => 'Shipping',
                'selling'         => 'Selling',
            );

            ob_start();
            ?>

            <select name="policyType" id="policyType" class="policyType">
                <option value="">AP4L Policy Type</option>
                <?php foreach ($policyType as $key => $value) { ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $policyTypeFilter); ?>><?php echo esc_html($value); ?></option>
                <?php } ?>
            </select>

            <?php
            do_action('restrict_manage_comments');

            $output = ob_get_clean();
            if (! empty($output)) {
                echo wp_kses($output, AP4L_ALLOWED_HTML);
                submit_button(__('Filter'), '', 'filter_action', false, array( 'id' => 'post-query-submit' ));
            }
        }
        echo wp_kses('</div>', AP4L_ALLOWED_HTML);
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
                $UserModal = new UserModal();
                $removeEntries = $UserModal->removeEntryFromDB('parent_policies', $selectedIDS);
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

        $policyTypeFilter = (!empty($_REQUEST['policyType'])) ? sanitize_text_field($_REQUEST['policyType']) : '';

        $UserModal        = new UserModal();
        $all_policies     = $UserModal->getPolicies(1, $policyTypeFilter);
        $data             = array();
        foreach ($all_policies as $all_policies_val) {
            $id     = $all_policies_val->id;
            $title  = $all_policies_val->policy_name;
            $active = $all_policies_val->status;
            $data[] = array(
                'id'            => $id,
                'policy_name'   => '<a class="row-title" href="' . AP4L_POLICY_URL . '&action=edit&id=' . $id . '&policy=' . $all_policies_val->policy_type . '">' . $title . '</a>',
                'policy_type'   => ($all_policies_val->policy_type == 'sync') ? 'synchronization' : $all_policies_val->policy_type,
                'created_at'    => $UserModal->getTimeFormated($all_policies_val->created_at),
                'policy_status' => '<input type="checkbox" dataType="is_active" account_id="' . $id . '" successmsg="Account Successfully %status%." class="formEditMethod is_active" id="listingStatusChange' . $active . '" name="listingStatusChange" ' . checked($active, 1, false) . ' value="' . $active . '" />',
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