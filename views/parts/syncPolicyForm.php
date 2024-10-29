<?php
/*
 * ===================
 * Get Old Policy Data
 * ===================
 */
$policyId            = '';
$auto_sync_product   = $auto_update_product = $policy_name         = '';

$action_req = (!empty($_REQUEST['action'])) ? sanitize_text_field($_REQUEST['action']) : '';
$id_req = (!empty($_REQUEST['id'])) ? sanitize_text_field($_REQUEST['id']) : 0;
$policy_req = (!empty($_REQUEST['policy'])) ? sanitize_text_field($_REQUEST['policy']) : '';

if (!empty($id_req) && ($action_req == 'edit')) {
    $policyId            = $id_req;
    $CurrentPolicyData   = $UserModal->getPolicy('sync', $policyId);
    $CurrentPolicyData   = $CurrentPolicyData[0];
    $policy_name         = $CurrentPolicyData->policy_name;
    $policyStatus        = $CurrentPolicyData->status;
    $auto_sync_product   = $CurrentPolicyData->auto_sync_product;
    $auto_update_product = $CurrentPolicyData->auto_update_product;
}

$selectionOption = array(
    1 => 'Yes',
    0 => 'No'
);
?>

<div class="SyncPolicyForm">
    <form id="SyncPolicyForm" action="" method="post" class="generalFormDesign">
        <h2><?php echo wp_kses($action_req, AP4L_ALLOWED_HTML); ?> Synchronization policy</h2>
        <hr>
        <table class="form-table" role="presentation">
            <tbody>
                <?php include_once AP4L_DIR . 'views/common/policyCommonForm.php'; ?>
                <tr>
                    <th scope="row">Automatically List Products?</th>
                    <td>
                        <select id="autoSyncProducts" name="autoSyncProducts">
                            <?php foreach ($selectionOption as $key => $value) { ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $auto_sync_product); ?>><?php echo esc_html($value); ?></option>
                            <?php } ?>
                        </select>
                        <p class="description">If set to "Yes", then products will be automatically listed immediately when they are added to a listing set with this Synchronization Policy set.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Automatically Update Products?</th>
                    <td>
                        <select id="autoUpdateProducts" name="autoUpdateProducts">
                            <?php foreach ($selectionOption as $key => $value) { ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $auto_update_product); ?>><?php echo esc_html($value); ?></option>
                            <?php } ?>
                        </select>
                        <p class="description">If set to "Yes", then products will be automatically updated when they are added to a listing set with this Synchronization Policy set.</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" id="formAction" name="formAction" value="<?php echo esc_attr($action_req); ?>"/>
        <input type="hidden" id="formType" name="formType" value="<?php echo esc_attr($policy_req); ?>"/>
        <input type="hidden" id="formEditId" name="formEditId" value="<?php echo esc_attr($policyId); ?>"/>
        <p class="submit">
            <input name="submit" type="submit" class="button button-primary" value="Save Changes">
            <a href="<?php echo esc_url(AP4L_POLICY_URL); ?>" class="button button-primary">cancel</a>
            <img class="loaderImage" src="<?php echo esc_url(AP4L_URL . 'assets/images/loader.gif'); ?>"/>
        </p>
    </form>
</div>