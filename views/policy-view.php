<?php
// check user capabilities
if (! current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
    return;
}

include_once AP4L_DIR . 'views/common/header.php';
include_once AP4L_DIR . 'classes/UserModal.php';
include_once AP4L_DIR . 'classes/tableGrids/policiesTable.php';

use Ap4l\UserModal;
use Ap4l\policiesTable;

$action_req = (!empty($_REQUEST['action'])) ? sanitize_text_field($_REQUEST['action']) : '';
$policy_req = (!empty($_REQUEST['policy'])) ? sanitize_text_field($_REQUEST['policy']) : '';

$UserModal = new UserModal();
$checkArray = array('add', 'edit');
?>

<div class="wrap MainPolicyModule ap4lModule">
    <div class="titleWrap">
        <h1 class="wp-heading-inline">AP4L Policies</h1>

        <?php if (empty($action_req) || ($action_req != 'add' && $action_req != 'edit')) : ?>
            <select id="policySelect">
                <option value="">Add Policy</option>
                <option value="synchronization">Synchronization Policy</option>
                <option value="shipping">Shipping Policy</option>
                <option value="selling">Selling Policy</option>
            </select>
        <?php endif; ?>

        <?php if (in_array($action_req, $checkArray)) : ?>
            <a href="<?php echo esc_url(AP4L_POLICY_URL); ?>" class="page-title-action">Back</a>
        <?php endif; ?>

        <hr class="wp-header-end">
    </div>

    <?php
    if (isset($action_req) && isset($policy_req) && ($policy_req === 'shipping')) {
        include_once AP4L_DIR . 'views/parts/shippingPolicyForm.php';
    } elseif (isset($action_req) && isset($policy_req) && ($policy_req === 'synchronization')) {
        include_once AP4L_DIR . 'views/parts/syncPolicyForm.php';
    } elseif (isset($action_req) && isset($policy_req) && ($policy_req === 'selling')) {
        include_once AP4L_DIR . 'views/parts/sellPolicyForm.php';
    } else {
        $page = sanitize_text_field(filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED));
        $paged = sanitize_text_field(filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT));
        ?>

        <form id="wpse-list-table-form" method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr($page); ?>" />
            <input type="hidden" name="paged" value="<?php echo esc_attr($paged); ?>" />

            <?php
            $wp_list_table = new policiesTable();
            $wp_list_table->prepare_items();
            $wp_list_table->display();
            ?>
        </form>

        <?php
    }
    ?>
</div>
