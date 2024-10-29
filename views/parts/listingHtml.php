<?php
$all_listings = $UserModal->getListing();
?>
<div class="accountModule">
    <?php if (empty($all_listings)) { ?>
        <div class="text-danger">There is no listing added yet.</div>
    <?php } else { ?>
        <table id="datatable" class="table table-sm table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Listing Name</th>
                    <th>Seller Name</th>
                    <th>Shipping Policy</th>
                    <th>Selling Policy</th>
                    <th>Sync Policy</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($all_listings)) { ?>
                    <?php
                    foreach ($all_listings as $all_listings) {
                        $editUrl = AP4L_LISTING_URL . '&action=edit&id=' . $all_listings->id;
                        ?>
                        <tr listing-id="<?php echo esc_attr($all_listings->id); ?>">
                            <td><?php echo wp_kses($all_listings->id, AP4L_ALLOWED_HTML); ?></td>
                            <td><?php echo wp_kses($all_listings->listing_name, AP4L_ALLOWED_HTML); ?></td>
                            <td><?php echo wp_kses($allAccount[ $all_listings->seller_account_id ], AP4L_ALLOWED_HTML); ?></td>
                            <td><?php echo wp_kses($allPoliciesName[ $all_listings->shipping_policy_id ], AP4L_ALLOWED_HTML); ?></td>
                            <td><?php echo wp_kses($allPoliciesName[ $all_listings->seller_policy_id ], AP4L_ALLOWED_HTML); ?></td>
                            <td><?php echo wp_kses($allPoliciesName[ $all_listings->sync_policy_id ], AP4L_ALLOWED_HTML); ?></td>
                            <td dataType="listing_status" id="<?php echo esc_attr($all_listings->id); ?>" class="checkBoxDesign">
                                <input successmsg="Listing Successfully %status%." class="formEditMethod listingStatusChange" type="checkbox" id="listingStatusChange<?php echo esc_attr($all_listings->id); ?>" name="listingStatusChange<?php echo esc_attr($all_policies->id); ?>" <?php echo esc_attr((isset($all_listings->status) && empty($all_listings->status)) ? '' : 'checked'); ?> />
                                <label class="checkToggleBtn" for="listingStatusChange<?php echo esc_attr($all_listings->id); ?>"></label>
                            </td>
                            <td><?php echo wp_kses($all_listings->created_at . " " . $UserModal->getTimeZone($all_listings->created_at), AP4L_ALLOWED_HTML); ?></td>
                            <td>
                                <button type="button"
                                        onclick="window.location.href = '<?php echo esc_url($editUrl); ?>'"
                                        class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-danger listingDlt">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-danger showAction">
                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                </button>
                                <div class="listingAction">
                                    <a class="" href="<?php echo esc_url(AP4L_LISTING_URL . '&viewlisting=' . $all_listings->id, AP4L_ALLOWED_HTML); ?>">Manage</a>
                                    <a class="ajaxRed" href="<?php echo esc_url(site_url() . '/wp-admin/edit.php?post_type=product', AP4L_ALLOWED_HTML); ?>">Add Product From List</a>
                                    <a class="ajaxRed" href="<?php echo esc_url(site_url() . '/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product', AP4L_ALLOWED_HTML); ?>">Add Product From Category</a>
                                </div>
                                <img class="loaderImage" src="<?php echo esc_url(AP4L_URL . 'images/loader.gif', AP4L_ALLOWED_HTML); ?>"/>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</div>