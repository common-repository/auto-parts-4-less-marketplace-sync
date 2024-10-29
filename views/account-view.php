<?php
// check user capabilities
if (! current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
    return;
}

include_once AP4L_DIR . 'views/common/header.php';
include_once AP4L_DIR . 'classes/tableGrids/accountTable.php';

use Ap4l\accountTable;

$action_req = (!empty($_REQUEST['action'])) ? sanitize_text_field($_REQUEST['action']) : '';

$addUrl         = AP4L_ACCOUNT_URL . '&action=add';
$checkArray     = array('add', 'edit');
?>

<div class="wrap MainAccountModule ap4lModule">
    <div class="titleWrap">
        <h1 class="wp-heading-inline">AP4L Accounts</h1>

        <?php if (empty($action_req) || $action_req == 'trash') : ?>
        <a href="<?php echo esc_url($addUrl); ?>" class="page-title-action">Add New</a>
        <?php endif; ?>

        <?php if (in_array($action_req, $checkArray)) : ?>
        <a href="<?php echo esc_url(AP4L_ACCOUNT_URL); ?>" class="page-title-action">Back</a>
        <?php endif; ?>

        <hr class="wp-header-end">
    </div>

    <?php
    if (in_array($action_req, $checkArray)) {
        include_once AP4L_DIR . 'views/parts/accountForm.php';
    } else {
        $page = sanitize_text_field(filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED));
        $paged = sanitize_text_field(filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT));
        ?>

        <form id="wpse-list-table-form" method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr($page); ?>" />
            <input type="hidden" name="paged" value="<?php echo esc_attr($paged); ?>" />

            <?php
            $wp_list_table = new accountTable();
            $wp_list_table->prepare_items();
            $wp_list_table->display();
            ?>
        </form>

        <?php
    }
    ?>
</div>
