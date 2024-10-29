<?php
// check user capabilities
if (! current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
    return;
}
include_once AP4L_DIR . 'views/common/header.php';
include_once AP4L_DIR . 'classes/UserModal.php';

use Ap4l\UserModal;

$UserModal      = new UserModal();
$args           = array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false
);
$all_categories = get_categories($args);
?>
<div class="wrap CategoryModule ap4lModule">
    <div class="titleWrap">
        <h1 class="wp-heading-inline">AP4L Categories</h1>
        <hr class="wp-header-end">
    </div>
    <?php if (! empty($all_categories)) { ?>
        <div class="CategoryForm">
            <form id="CategoryForm" action="" method="post" class="generalFormDesign">
                <table class="form-table" role="presentation">
                    <tbody>
                        <?php
                        foreach ($all_categories as $key => $value) {
                            $nameAttr    = 'catId' . $value->term_id;
                            $termId      = $value->term_id;
                            $selectedCat = get_term_meta($termId, AP4L_CATEGORY_KEY, true);
                            ?>
                            <tr>
                                <th scope="row"><?php echo wp_kses($value->name, AP4L_ALLOWED_HTML); ?></th>
                                <td>
                                    <input type="hidden" class="catMapBox" name="<?php echo esc_attr($nameAttr); ?>" value="<?php echo esc_attr($termId); ?>"/>
                                    <?php $UserModal->getCategorySelectBox($termId, $selectedCat); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <p class="submit">
                    <input name="submit" type="submit" class="button button-primary" value="Save Changes">
                    <img class="loaderImage" src="<?php echo esc_url(AP4L_URL); ?>assets/images/loader.gif"/>
                </p>
            </form>
        </div>
    <?php } ?>
</div>