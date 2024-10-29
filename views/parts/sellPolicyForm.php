<?php
$accounts              = $UserModal->getAccounts();
$attributes            = $UserModal->getProductAttributes();
$woocommerceAttributes = $UserModal->getWoocommerceAttributes();

if (! empty(($attributes))) {
    $categorizedAttr = [];
    foreach ($attributes as $attributes_val) {
        $categorizedAttr[$attributes_val->attribute_type][] = $attributes_val;
    }
}

$childPolicy       = [];
$seller_account_id = '';
$policy_name       = '';
$policyId          = '';
$editPage          = false;

$action_req = (!empty($_REQUEST['action'])) ? sanitize_text_field($_REQUEST['action']) : '';
$id_req = (!empty($_REQUEST['id'])) ? sanitize_text_field($_REQUEST['id']) : 0;
$policy_req = (!empty($_REQUEST['policy'])) ? sanitize_text_field($_REQUEST['policy']) : '';

if (!empty($id_req) && ($action_req == 'edit')) {
    global $wpdb;
    $policyId          = $id_req;
    $CurrentPolicyData = $UserModal->getPolicy('selling', $policyId);
    $CurrentPolicyData = $CurrentPolicyData[0];
    $policy_name       = $CurrentPolicyData->policy_name;
    $policyStatus      = $CurrentPolicyData->status;
    $childPolicy       = $wpdb->get_results("SELECT * FROM " . AP4L_TABLE_PREFIX . "selling_policy WHERE main_policy_id = '" . $policyId . "'", ARRAY_A);
    $editPage          = true;
}

function getAttrSingleArr($childPolicy, $attrID, $returnStaticVal = false)
{
    if (! empty($childPolicy)) {
        $attrEntry = array_values(array_filter($childPolicy, function ($item) use ($attrID) {
                    return $item['product_attribute_id'] == $attrID;
        }));
        if (empty($attrEntry)) {
            return '';
        }
        if ($returnStaticVal) {
            if ($attrEntry[0]['woocommerce_attribute'] == '' && $attrEntry[0]['static_value'] !== '') {
                return $attrEntry[0]['static_value'];
            }
        } else {
            if ($attrEntry[0]['woocommerce_attribute'] == '' && $attrEntry[0]['static_value'] !== '') {
                return 'staticvalue';
            } else {
                return $attrEntry[0]['woocommerce_attribute'];
            }
        }
    }
}
function generateWCAttrHtml($attrID, $woocommerceAttributes, $childPolicy, $uniqueAttrKey)
{
    $wcAttHtml = '<option disabled selected value="">WooCommerce Attribute</option>';
    $wcAttHtml .= '<option value="">--------------</option>';
    ob_start();
    $mapped_value = getAttrSingleArr($childPolicy, $attrID);
    if ($uniqueAttrKey !== 'general-wcAttr-0' && $uniqueAttrKey !== 'seller-wcAttr-0') : ?>
    <option value="<?php echo esc_attr('staticvalue'); ?>" <?php selected($mapped_value, 'staticvalue'); ?>><?php echo esc_html('Custom Static Value'); ?></option>
    <?php endif;
    if (! empty($woocommerceAttributes)) {
        foreach ($woocommerceAttributes as $key => $value) {
            if (is_array($value)) {
                $mapped_value = getAttrSingleArr($childPolicy, $attrID);
                ?>
                <optgroup label="<?php echo esc_attr($value['name']); ?>">
                    <?php foreach ($value['options'] as $sub_key => $sub_value) : ?>
                        <option value="<?php echo esc_attr($sub_key); ?>" <?php selected($mapped_value, $sub_key); ?>><?php echo esc_html($sub_value); ?></option>
                    <?php endforeach ?>
                </optgroup>
                <?php
            } else {
                $mapped_value = getAttrSingleArr($childPolicy, $attrID);
                ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($mapped_value, $key); ?>><?php echo esc_html($value); ?></option>
            <?php } ?>
            <?php
        }
    }
    $mapped_value = getAttrSingleArr($childPolicy, $attrID);
    ?>

    <?php  ?>

    <?php
    $wcAttHtml    .= ob_get_clean();
    return $wcAttHtml;
}
?>
<div class="SellingPolicyForm">
    <form id="SellingPolicyForm" action="" method="post" class="generalFormDesign">
        <h2><?php echo wp_kses($action_req, AP4L_ALLOWED_HTML); ?> Selling Policy</h2>
        <hr>
        <table class="form-table" role="presentation">
            <tbody>
                <?php include_once AP4L_DIR . 'views/common/policyCommonForm.php'; ?>

                <?php
                if (! empty($categorizedAttr)) {
                    foreach ($categorizedAttr as $catKey => $childCats) {
                        if (! empty($childCats)) {
                            echo wp_kses("<th class='attr-title'><label>" . $catKey . "</label><hr></th>", AP4L_ALLOWED_HTML);
                            foreach ($childCats as $attrKey => $attrValue) {
                                $attrID         = $attrValue->id;
                                $attrName       = $attrValue->attribute_name;
                                $attrRequired   = $attrValue->required;
                                $attrName       = $attrRequired ? $attrName . "<span>*</span>" : $attrName;
                                $attributeType  = sanitize_title($catKey);
                                $uniqueAttrKey  = $attributeType . "-wcAttr-" . $attrKey;
                                $otherOptionVal = '';
                                if ($editPage) {
                                    $otherOptionVal = getAttrSingleArr($childPolicy, $attrID, true);
                                }
                                ?>
                                <tr>
                                    <th scope="row">
                                        <label><?php echo wp_kses($attrName, AP4L_ALLOWED_HTML); ?></label>
                                    </th>
                                    <td>
                                        <select id="<?php echo esc_attr($uniqueAttrKey); ?>" name="<?php echo esc_attr($uniqueAttrKey); ?>" class="SellerPolicyAttribute attributeSelectionField <?php echo esc_attr((!empty($attrRequired)) ? 'required' : ''); ?>" data-attribute-type="<?php echo esc_attr($catKey); ?>" data-ap4latrr-id="<?php echo esc_attr($attrID); ?>">
                                            <?php echo wp_kses(generateWCAttrHtml($attrID, $woocommerceAttributes, $childPolicy, $uniqueAttrKey), AP4L_ALLOWED_HTML); ?>
                                        </select>

                                        <?php if (! empty($otherOptionVal)) { ?>
                                            <input type="text" placeholder="Custom Static Value"  name="<?php echo esc_attr($uniqueAttrKey . '-other'); ?>" class="required" id="<?php echo esc_attr($uniqueAttrKey . '-other'); ?>" value="<?php echo esc_attr($otherOptionVal); ?>" aria-invalid="false">
                                        <?php } else { ?>
                                            <input type="text" placeholder="Custom Static Value"  style="display:none;" class="required" name="<?php echo esc_attr($uniqueAttrKey . '-other'); ?>" id="<?php echo esc_attr($uniqueAttrKey . '-other'); ?>" value="" aria-invalid="false">
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                    }
                }
                ?>
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