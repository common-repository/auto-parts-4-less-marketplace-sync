<?php
// check user capabilities
if (! current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
    return;
}
include_once AP4L_DIR . 'views/common/header.php';
include_once AP4L_DIR . 'classes/UserModal.php';
include_once AP4L_DIR . 'classes/tableGrids/listingTable.php';
include_once AP4L_DIR . 'classes/tableGrids/productTable.php';

use Ap4l\UserModal;
use Ap4l\listingTable;
use Ap4l\productTable;

$viewlisting_req = (!empty($_REQUEST['viewlisting'])) ? sanitize_text_field($_REQUEST['viewlisting']) : '';
$action_req = (!empty($_REQUEST['action'])) ? sanitize_text_field($_REQUEST['action']) : '';

$UserModal       = new UserModal();
$accounts        = $UserModal->getAccounts();
$policies        = $UserModal->getPolicies();

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

$allAccount = array();

if (! empty($accounts)) {
    foreach ($accounts as $key => $value) {
        $allAccount[$value->id] = $value->title;
    }
}

$addUrl     = admin_url() . 'admin.php?page=' . AP4L_SLUG . '-listings&action=add';
$checkArray = array( 'add', 'edit', 'view' );
?>

<div class="wrap MainListingModule ap4lModule">
    <div class="titleWrap">
        <h1 class="wp-heading-inline">AP4L Listings</h1>

        <?php if (empty($action_req) || ($action_req == 'trash')) : ?>
        <a href="<?php echo esc_url($addUrl); ?>" class="page-title-action">Add New</a>
        <?php endif; ?>

        <?php if ((!empty($action_req) && in_array($action_req, $checkArray) ) || !empty($viewlisting_req)) : ?>
        <a href="<?php echo esc_url(AP4L_LISTING_URL); ?>" class="page-title-action">Back</a>
        <?php endif; ?>

        <hr class="wp-header-end">
    </div>

    <?php
    if (!empty($action_req) && ($action_req == 'add' || $action_req == 'edit')) {
        include_once AP4L_DIR . 'views/parts/listingForm.php';
    } else {
        $page = sanitize_text_field(filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED));
        $paged = sanitize_text_field(filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT));
        ?>

        <form id="wpse-list-table-form" method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr($page); ?>" />
            <input type="hidden" name="paged" value="<?php echo esc_attr($paged); ?>" />

            <?php
            $wp_list_table = new listingTable();
            $wp_list_table->prepare_items();
            $wp_list_table->display();
            ?>
        </form>

        <?php
    }
    ?>
</div>
