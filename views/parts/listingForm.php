<?php
/*
 * ===================
 * Get Old Policy Data
 * ===================
 */
$listingName        = '';
$listingId          = '';
$disabled           = '';
$seller_account_id  = $sync_policy_id     = $shipping_policy_id = $seller_policy_id   = '';

$action_req = (!empty($_REQUEST['action'])) ? sanitize_text_field($_REQUEST['action']) : '';
$id_req = (!empty($_REQUEST['id'])) ? sanitize_text_field($_REQUEST['id']) : '';

if (!empty($id_req) && ($action_req == 'edit')) {
    global $wpdb;
    $disabled        = 'disabled';
    $listingId       = $id_req;
    $CurrListingData = $UserModal->getListing($listingId);
    if (! empty($CurrListingData) && (count($CurrListingData) == 1)) {
        $CurrListingData    = $CurrListingData[0];
        $listingStatus      = $CurrListingData->status;
        $listingName        = $CurrListingData->listing_name;
        $seller_account_id  = $CurrListingData->seller_account_id;
        $sync_policy_id     = $CurrListingData->sync_policy_id;
        $shipping_policy_id = $CurrListingData->shipping_policy_id;
        $seller_policy_id   = $CurrListingData->seller_policy_id;
    } else {
        wp_safe_redirect(AP4L_LISTING_URL);
    }
}
?>
<div class="ap4lListingForm">
    <form id="ap4lListingForm" action="" method="post" class="generalFormDesign">
        <h2><?php echo wp_kses($action_req, AP4L_ALLOWED_HTML); ?> Listing</h2>
        <hr>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="listingName">Listing Name<span>*</span></label>
                    </th>
                    <td>
                        <input class="regular-text" type="text" id="listingName" name="listingName" value="<?php echo esc_attr($listingName); ?>" autofocus>
                        <p class="description">You can name your listing anything, this is only for your own reference.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="AccountId">Account<span>*</span></label>
                    </th>
                    <td>
                        <select id="AccountId" name="AccountId" <?php echo esc_attr($disabled); ?>>
                            <option value="">Select Account</option>
                            <?php foreach ($accounts as $key => $value) { ?>
                                <option value="<?php echo esc_attr($value->id); ?>" <?php selected($value->id, $seller_account_id); ?>><?php echo esc_html($value->title); ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <?php if ($action_req == 'edit' && 1 == 0) { ?>
                    <tr>
                        <th scope="row">Policy Status <small>(Enabled/Disabled)</small></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">
                                    <span>Policy Status <small>(Enabled/Disabled)</small></span>
                                </legend>
                                <label for="listingStatus">
                                    <input id="listingStatus"  type="checkbox" name="listingStatus" value="1" <?php checked(1, $listingStatus); ?>>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <th scope="row">
                        <label for="SyncPolicy">Synchronization Policy<span>*</span></label>
                    </th>
                    <td>
                        <select id="SyncPolicy" name="SyncPolicy">
                            <option value="">Select Policy</option>

                            <?php if (!empty($allPolicies['synchronization'])) : ?>
                                <?php foreach ($allPolicies['synchronization'] as $key => $value) : ?>
                            <option value="<?php echo esc_attr($value['id']); ?>" <?php selected($value['id'], $sync_policy_id); ?>><?php echo esc_html($value['name']); ?></option>
                                <?php endforeach ?>
                            <?php endif ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ShippingPolicy">Shipping Policy<span>*</span></label>
                    </th>
                    <td>
                        <select id="ShippingPolicy" name="ShippingPolicy">
                            <option value="">Select Policy</option>

                            <?php if (!empty($allPolicies['shipping'])) : ?>
                                <?php foreach ($allPolicies['shipping'] as $key => $value) : ?>
                            <option value="<?php echo esc_attr($value['id']); ?>" <?php selected($value['id'], $shipping_policy_id); ?>><?php echo esc_html($value['name']); ?></option>
                                <?php endforeach ?>
                            <?php endif ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="SellingPolicy">Selling Policy<span>*</span></label>
                    </th>
                    <td>
                        <select id="SellingPolicy" name="SellingPolicy">
                            <option value="">Select Policy</option>

                            <?php if (!empty($allPolicies['selling'])) : ?>
                                <?php foreach ($allPolicies['selling'] as $key => $value) : ?>
                            <option value="<?php echo esc_attr($value['id']); ?>" <?php selected($value['id'], $seller_policy_id); ?>><?php echo esc_html($value['name']); ?></option>
                                <?php endforeach ?>
                            <?php endif ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" id="formAction" name="formAction" value="<?php echo esc_attr($action_req); ?>"/>
        <input type="hidden" id="formEditId" name="formEditId" value="<?php echo esc_attr($listingId); ?>"/>
        <p class="submit">
            <input class="button button-primary" name="submit" type="submit" value="Save Changes">
            <a href="<?php echo esc_url(AP4L_LISTING_URL); ?>" class="button button-primary">cancel</a>
            <img class="loaderImage" src="<?php echo esc_url(AP4L_URL); ?>assets/images/loader.gif"/>
        </p>
    </form>
</div>