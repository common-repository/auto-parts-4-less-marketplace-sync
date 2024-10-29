<?php
$all_policies = $UserModal->getPolicies();
?>
<div class="accountModule">
    <?php if (empty($all_policies)) { ?>
        <div class="text-danger">There is no policies added yet.</div>
    <?php } else { ?>
        <table id="datatable" class="table table-sm table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Policy Type</th>
                    <th>Policy Name</th>
                    <th>Policy Status</th>
                    <th>Seller Name</th>
                    <th>Created Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($all_policies)) { ?>
                    <?php
                    foreach ($all_policies as $all_policies) {
                        $editUrl = AP4L_POLICY_URL . '&action=edit&id=' . $all_policies->id . '&policy=' . $all_policies->policy_type;
                        ?>
                        <tr policy-type="<?php echo esc_attr($all_policies->policy_type); ?>"
                            policy-id="<?php echo esc_attr($all_policies->id); ?>">
                            <td><?php echo wp_kses($all_policies->id, AP4L_ALLOWED_HTML); ?></td>
                            <td><?php echo wp_kses($all_policies->policy_type, AP4L_ALLOWED_HTML); ?></td>
                            <td><?php echo wp_kses($all_policies->policy_name, AP4L_ALLOWED_HTML); ?></td>
                            <td dataType="policy_status" id="<?php echo esc_attr($all_policies->id); ?>" class="checkBoxDesign">
                                <input successmsg="Policy Successfully %status%." class="formEditMethod policyStatusChange" type="checkbox" id="policyStatusChange<?php echo esc_attr($all_policies->id); ?>" name="policyStatusChange<?php echo esc_attr($all_policies->id); ?>" <?php echo esc_attr((isset($all_policies->status) && empty($all_policies->status)) ? '' : 'checked'); ?> />
                                <label class="checkToggleBtn" for="policyStatusChange<?php echo esc_attr($all_policies->id); ?>"></label>
                            </td>
                            <td><?php echo wp_kses($allAccount[ $all_policies->seller_account_id ], AP4L_ALLOWED_HTML); ?></td>
                            <td><?php echo wp_kses($all_policies->created_at . ' ' . $UserModal->getTimeZone($all_policies->created_at), AP4L_ALLOWED_HTML); ?></td>
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-primary policyView">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </button>
                                <button type="button"
                                        onclick="window.location.href = '<?php echo esc_url($editUrl); ?>'"
                                        class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-danger policyDlt">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                <img class="loaderImage" src="<?php echo esc_url(AP4L_URL); ?>images/loader.gif"/>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
        <!--View Policy Popup-->
        <div class="form-popup policyViewPopup">
            <div class="modal-overlay search-modal-toggle"></div>
            <div class="modal-wrapper modal-transition">
                <div class="container">
                    <div class="modal-body">
                        <button class="modal-close formpopup-toggle">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                        <div class="modal-content">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--View Policy Popup END-->
    <?php } ?>
</div>