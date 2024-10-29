<?php
/*
 * ===================
 * Get Old Policy Data
 * ===================
 */
$ap4l_shipping_policy_id = '';
$policyId                = '';
$policy_name             = '';

$action_req = (!empty($_REQUEST['action'])) ? sanitize_text_field($_REQUEST['action']) : '';
$id_req = (!empty($_REQUEST['id'])) ? sanitize_text_field($_REQUEST['id']) : 0;
$policy_req = (!empty($_REQUEST['policy'])) ? sanitize_text_field($_REQUEST['policy']) : '';

if (!empty($id_req) && ($action_req == 'edit')) {
    $policyId                = $id_req;
    $CurrentPolicyData       = $UserModal->getPolicy('shipping', $policyId);
    $CurrentPolicyData       = $CurrentPolicyData[0];
    $policy_name             = $CurrentPolicyData->policy_name;
    $policyStatus            = $CurrentPolicyData->status;
    $ap4l_shipping_policy_id = $CurrentPolicyData->ap4l_shipping_policy_id;
}
?>
<div class="ShippingPolicyForm">
    <form id="ShippingPolicyForm" action="" method="post" class="generalFormDesign">
        <h2><?php echo wp_kses($action_req, AP4L_ALLOWED_HTML); ?> shipping policy</h2>
        <hr>
        <table class="form-table" role="presentation">
            <tbody>
                <?php include_once AP4L_DIR . 'views/common/policyCommonForm.php'; ?>
                <tr>
                    <th scope="row">
                        <label for="policyID">AP4L Shipping Policy Id<span>*</span></label>
                    </th>
                    <td>
                        <input class="regular-text" type="text" id="policyID" name="policyID" required="" value="<?php echo esc_attr($ap4l_shipping_policy_id); ?>">
                        <p class="description">Download your Shipping Policies by logging into your seller panel and using the sidebar navigation. Click on "Products" > "Bulk Import" >" Download Catalog Template" > "Shipping Policy Template". Enter the "shipping_policy_id" here to indicate which shipping policy you want to use.</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" id="formAction" name="formAction" value="<?php echo esc_attr($action_req); ?>"/>
        <input type="hidden" id="formType" name="formType" value="<?php echo esc_attr($policy_req); ?>"/>
        <input type="hidden" id="formEditId" name="formEditId" value="<?php echo esc_attr($policyId); ?>"/>
        <p class="submit">
            <input class="button button-primary" name="submit" type="submit" value="Save Changes">
            <a href="<?php echo esc_url(AP4L_POLICY_URL); ?>" class="button button-primary">cancel</a>
            <img class="loaderImage" src="<?php echo esc_url(AP4L_URL); ?>assets/images/loader.gif"/>
        </p>
    </form>
</div>
