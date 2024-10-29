<?php
/*
 * check user capabilities
 */
if (! current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
    return;
}

include_once AP4L_DIR . 'views/common/header.php';
include_once AP4L_DIR . 'classes/tableGrids/ordersTable.php';
// include_once AP4L_DIR . 'classes/OrderFunctions.php';

use Ap4l\ordersTable;

// use Ap4l\OrderFunctions;
?>

<div class="wrap MainListingModule ap4lModule">
    <div class="titleWrap">
        <h1 class="wp-heading-inline">AP4L Orders</h1>
        <hr class="wp-header-end">
    </div>

    <?php
    $page = sanitize_text_field(filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED));
    $paged = sanitize_text_field(filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT));
    ?>

    <form id="wpse-list-table-form" method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($page); ?>" />
        <input type="hidden" name="paged" value="<?php echo esc_attr($paged); ?>" />

        <?php
        $wp_list_table = new ordersTable();
        $wp_list_table->prepare_items();
        $wp_list_table->search_box('search', 'search_id');
        $wp_list_table->display();
        ?>
    </form>
</div>
