<div class="AP4LlistingInner">
    <?php
    $listingID = '';

    $viewlisting_req = (!empty($_REQUEST['viewlisting'])) ? sanitize_text_field($_REQUEST['viewlisting']) : '';

    if (! empty($viewlisting_req)) {
        $listingID  = $viewlisting_req;
        $getListing = $UserModal->getListing($listingID);

        if (empty($getListing)) {
            wp_safe_redirect(AP4L_LISTING_URL);
        } else {
            $productIDs      = $UserModal->getListingProducts($listingID);
//            $productIDs      = $getListPro->posts;
            $listingProCount = count($productIDs);
            $ap4lProIDs   = $UserModal->getListingProducts($listingID, true);
//            $ap4lProIDs      = $ap4lProListed->posts;
            $ap4lProCount    = count($ap4lProIDs);
            $getListing      = $getListing[0];
            $lisgingName     = $getListing->listing_name . ' Listing Details';
            $created_at      = $this->getTimeFormated($getListing->created_at);
            $updated_at      = $this->getTimeFormated($getListing->updated_at);
            $status          = ($getListing->status == 1) ? 'Enabled' : 'Disabled';
            ?>
            <div class="InnerView">
                <div class="topPart" style="display: none;" >
                    <h2 style="display: none;" ><?php echo wp_kses($lisgingName, AP4L_ALLOWED_HTML); ?></h2>
                    <a class="button" style="display: none;" href="<?php echo esc_url(AP4L_LISTING_URL); ?>">Back</a>
                    <a class="button ap4l-all-product" style="display: none;" href="#qb-popup-all-product">Syn. All Product</a>
                    <a class="button ap4l-all-update-product" style="display: none;" href="#qb-popup-all-update-product">Update. All Product</a>
                    <hr>
                </div>
                <div class="ListingInformation">
                    <div class="row">
                        <div class="col-sm">
                            <p><span>Listing: </span><?php echo wp_kses($lisgingName, AP4L_ALLOWED_HTML); ?></p>
                            <p style="display: none;" ><span>Listing Status: </span><?php echo wp_kses($status, AP4L_ALLOWED_HTML); ?></p>
                            <p><span>Account: </span><?php echo wp_kses(((!empty($getListing->seller_account_id)) ? $allAccount[$getListing->seller_account_id] : '-'), AP4L_ALLOWED_HTML); ?></p>
                            <?php if (key_exists($getListing->sync_policy_id, $allPoliciesName)) { ?>
                                <p><span>Synchronization Policy: </span><?php echo wp_kses($allPoliciesName[$getListing->sync_policy_id], AP4L_ALLOWED_HTML); ?></p>
                            <?php }if (key_exists($getListing->shipping_policy_id, $allPoliciesName)) { ?>
                                <p><span>Shipping Policy: </span><?php echo wp_kses($allPoliciesName[$getListing->shipping_policy_id], AP4L_ALLOWED_HTML); ?></p>
                            <?php } if (key_exists($getListing->seller_policy_id, $allPoliciesName)) { ?>
                                <p><span>Selling Policy: </span><?php echo wp_kses($allPoliciesName[$getListing->seller_policy_id], AP4L_ALLOWED_HTML); ?></p>
                            <?php } ?>
                            <p><span>Created At: </span><?php echo wp_kses($created_at, AP4L_ALLOWED_HTML); ?></p>
                            <p><span>Updated At: </span><?php echo wp_kses($updated_at, AP4L_ALLOWED_HTML); ?></p>
                        </div>
                        <div class="col-sm">
                            <canvas id="ap4l-product-chart" style="width: 100%;"></canvas>
                            <script>
                                var data = {
                                    labels: [
                                        "AP4L Product - (<?php echo esc_html($ap4lProCount); ?>)",
                                        "WooCommerce Product - (<?php echo esc_html($listingProCount); ?>)"
                                    ],
                                    datasets: [{
                                            data: [<?php echo esc_html($ap4lProCount); ?>, <?php echo esc_html($listingProCount); ?>],
                                            backgroundColor: [
                                                "#76B51B",
                                                "#258481"
                                            ],
                                            hoverBackgroundColor: [
                                                "#618430",
                                                "#3BC1BF"
                                            ]
                                        }]
                                };
                                var ctx = jQuery("#ap4l-product-chart");
                                var myChart = new Chart(ctx, {
                                    type: 'pie',
                                    data: data
                                });
                            </script>
                        </div>
                    </div>
                </div>
                <?php
                $page = sanitize_text_field(filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED));
                $paged = sanitize_text_field(filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT));
                ?>

                <form id="wpse-list-table-form" method="get">
                    <input type="hidden" name="page" value="<?php echo esc_attr($page); ?>" />
                    <input type="hidden" name="paged" value="<?php echo esc_attr($paged); ?>" />
                    <input type="hidden" name="viewlisting" value="<?php echo esc_attr($listingID); ?>" />

                    <?php
                    $wp_list_product_table->search_box('search', 'search_id');
                    $wp_list_product_table->display();
                    ?>
                </form>
            </div>
            <?php
        }
    } else {
        wp_safe_redirect(AP4L_LISTING_URL);
    }
    ?>
</div>