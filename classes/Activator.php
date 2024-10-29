<?php
/**
 * This class defines all code necessary to run during the plugin's activation.
 */
namespace Ap4l;

if (! class_exists('Activator')) {
    class Activator
    {
        public function __construct()
        {
            // NA
        }
        public function activate()
        {
            /*
             * =====================================
             * create custom database tables - start
             * =====================================
             */
            global $wpdb;
            $tableExists = $this->dbTableExists(AP4L_TABLE_PREFIX . "accounts");
            if (empty($tableExists)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "accounts" .
                        "(
                  id bigint UNSIGNED NOT NULL,
                  title varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  email varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  auth_token varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  sync_orders tinyint UNSIGNED DEFAULT '0' COMMENT '0=no, 1=yes',
                  sync_quantity tinyint(3) UNSIGNED DEFAULT 0 COMMENT '0=no, 1=yes',
                  sync_order_days INT NULL DEFAULT '60',
                  sync_order_date DATE NULL DEFAULT NULL,
                  is_active tinyint UNSIGNED DEFAULT '0' COMMENT '0=inactive, 1=active',
                  access_token varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  expires_at timestamp NULL DEFAULT NULL,
                  created_at timestamp NULL DEFAULT NULL,
                  updated_at timestamp NULL DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "accounts");
            }

            $tableExists_policies = $this->dbTableExists(AP4L_TABLE_PREFIX . "parent_policies");
            if (empty($tableExists_policies)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "parent_policies" .
                        "(
                    id int(11) NOT NULL,
                    policy_type varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    policy_name longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    status tinyint(3) UNSIGNED NOT NULL COMMENT '0=inactive, 1=active	',
                    created_at timestamp NULL DEFAULT NULL,
                    updated_at timestamp NULL DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "parent_policies");
            }

            $tableExists_sync_policy = $this->dbTableExists(AP4L_TABLE_PREFIX . "sync_policy");
            if (empty($tableExists_sync_policy)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "sync_policy" .
                        "(
                    id int(11) NOT NULL,
                    main_policy_id bigint UNSIGNED NOT NULL,
                    auto_sync_product tinyint(3) UNSIGNED DEFAULT 0 COMMENT '0=no, 1=yes',
                    auto_update_product tinyint(3) UNSIGNED DEFAULT 0 COMMENT '0=no, 1=yes'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "sync_policy");
            }

            $tableExists_shipping_policy = $this->dbTableExists(AP4L_TABLE_PREFIX . "shipping_policy");
            if (empty($tableExists_shipping_policy)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "shipping_policy" .
                        "(
                    id int(11) NOT NULL,
                    main_policy_id bigint UNSIGNED NOT NULL,
                    ap4l_shipping_policy_id varchar(255) DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "shipping_policy");
            }

            $tableExists_selling_policy = $this->dbTableExists(AP4L_TABLE_PREFIX . "selling_policy");
            if (empty($tableExists_selling_policy)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "selling_policy" .
                        "(
                    id int(11) NOT NULL,
                    main_policy_id bigint UNSIGNED NOT NULL,
                    product_attribute_id int(11) NOT NULL,
                    woocommerce_attribute varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    static_value longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "selling_policy");
            }

            $tableExists_cat_mapping_attributes = $this->dbTableExists(AP4L_TABLE_PREFIX . "cat_mapping_attributes");
            if (empty($tableExists_cat_mapping_attributes)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "cat_mapping_attributes" .
                        "(
                    id int(11) NOT NULL,
                    category_id int(11) DEFAULT NULL,
                    category_name varchar(255) DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "cat_mapping_attributes");
                $this->addCategoryAttributes();
            }

            $tableExists_parent_listings = $this->dbTableExists(AP4L_TABLE_PREFIX . "listings");
            if (empty($tableExists_parent_listings)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "listings" .
                        "(
                    id int(11) NOT NULL,
                    listing_name longtext NOT NULL,
                    seller_account_id int(11) NOT NULL,
                    shipping_policy_id int(11) DEFAULT NULL,
                    seller_policy_id int(11) DEFAULT NULL,
                    sync_policy_id int(11) DEFAULT NULL,
                    status tinyint(3) UNSIGNED DEFAULT 0 COMMENT '0=inactive, 1=active',
                    created_at timestamp NULL DEFAULT NULL,
                    updated_at timestamp NULL DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "listings");
            }

            $tableExists_product_attributes = $this->dbTableExists(AP4L_TABLE_PREFIX . "product_attributes");
            if (empty($tableExists_product_attributes)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "product_attributes" .
                        "(
                    id int(11) NOT NULL,
                    attribute_name varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    attribute_type varchar(255) DEFAULT NULL COMMENT 'product/seller',
                    ap4l_key varchar(255) DEFAULT NULL,
                    required tinyint(4) DEFAULT NULL COMMENT '0/1 - 0=no, 1=yes'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "product_attributes");
                $this->addProductAttributes();
            }

            $tableExists_wc_map_attributes = $this->dbTableExists(AP4L_TABLE_PREFIX . "wc_mapping_attributes");
            if (empty($tableExists_wc_map_attributes)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "wc_mapping_attributes" .
                        "(
                    id int(11) NOT NULL,
                    attribute_name varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    attribute_type varchar(255) DEFAULT NULL COMMENT 'text/select',
                    attribute_required varchar(255) DEFAULT NULL,
                    attribute_options varchar(255) DEFAULT NULL COMMENT 'multiple options with |(pipe) separator',
                    attribute_desc varchar(255) DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "wc_mapping_attributes");
                $this->addWcMappingAttributes();
            }

            $tableExists = $this->dbTableExists(AP4L_TABLE_PREFIX . "orders");
            if (empty($tableExists)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "orders" .
                        "(
                    id int(11) NOT NULL,
                    seller_order_id varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    order_status varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    wc_order_id varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    account_id bigint UNSIGNED DEFAULT '0',
                    created_at timestamp NULL DEFAULT NULL,
                    updated_at timestamp NULL DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "orders", 'id');
            }

            $tableExists_api_logs = $this->dbTableExists(AP4L_TABLE_PREFIX . "orders_logs");
            if (empty($tableExists_api_logs)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "orders_logs" .
                        "(
                    id int(11) NOT NULL,
                    api_endpoint varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    seller_id varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    api_payload longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    request_at timestamp NULL DEFAULT NULL,
                    api_response longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    response_at timestamp NULL DEFAULT NULL,
                    resposne_code int(11) DEFAULT NULL,
                    message longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "orders_logs", 'id');
            }

            $tableExists_listing_logs = $this->dbTableExists(AP4L_TABLE_PREFIX . "listing_logs");
            if (empty($tableExists_listing_logs)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "listing_logs" .
                        "(
                    id int(11) NOT NULL,
                    listing_id varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    product_id varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    api_endpoint varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    api_payload longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    request_at timestamp NULL DEFAULT NULL,
                    api_response longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    response_at timestamp NULL DEFAULT NULL,
                    resposne_code int(11) DEFAULT NULL,
                    cron tinyint UNSIGNED DEFAULT '0' COMMENT '0=no, 1=yes',
                    message longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "listing_logs", 'id');
            }

            $tableExists_quantity_to_wp = $this->dbTableExists(AP4L_TABLE_PREFIX . "quantity_to_wp");
            if (empty($tableExists_quantity_to_wp)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "quantity_to_wp" .
                        "(
                    id BIGINT UNSIGNED NOT NULL,
                    account_id BIGINT UNSIGNED NULL DEFAULT '0',
                    in_progress TINYINT UNSIGNED NULL DEFAULT '0',
                    last_page BIGINT UNSIGNED NULL DEFAULT '0',
                    last_product VARCHAR(255) NULL DEFAULT NULL,
                    products_at_start BIGINT UNSIGNED NULL DEFAULT '0',
                    products_at_finish BIGINT UNSIGNED NULL DEFAULT '0',
                    started_at DATETIME NULL DEFAULT NULL,
                    finished_at DATETIME NULL DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "quantity_to_wp", 'id');
            }

            $tableExists_quantity_to_wp_products = $this->dbTableExists(AP4L_TABLE_PREFIX . "quantity_to_wp_products");
            if (empty($tableExists_quantity_to_wp_products)) {
                $wpdb->query("CREATE TABLE IF NOT EXISTS " . AP4L_TABLE_PREFIX . "quantity_to_wp_products" .
                        "(
                    id BIGINT UNSIGNED NOT NULL,
                    parent_id BIGINT UNSIGNED NULL DEFAULT '0',
                    product_id BIGINT UNSIGNED NULL DEFAULT '0',
                    sku VARCHAR(255) NULL DEFAULT NULL,
                    quantity INT UNSIGNED NULL DEFAULT '0',
                    quantity_old INT UNSIGNED NULL DEFAULT '0',
                    created_at DATETIME NULL DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                $this->dbAddPrimaryKey(AP4L_TABLE_PREFIX . "quantity_to_wp_products", 'id');
            }

            flush_rewrite_rules();
        }
        public function dbTableExists($table)
        {
            global $wpdb;
            return $wpdb->query("SHOW TABLES LIKE '" . $table . "'");
        }
        /*
         * ======================================
         * Add primary key to the database table.
         * ======================================
         */
        public function dbAddPrimaryKey($table, $column = 'id')
        {
            global $wpdb;
            $wpdb->query("ALTER TABLE " . $table . " ADD PRIMARY KEY (" . $column . ")");
            $wpdb->query("ALTER TABLE " . $table . " MODIFY " . $column . " BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
            return true;
        }
        /*
         * ========================
         * Add Category Attribiites
         * ========================
         */
        public function csvToArray($csvFile)
        {
            $file_to_read = fopen($csvFile, 'r');
            while (! feof($file_to_read)) {
                $lines[] = fgetcsv($file_to_read, 1000, ',');
            }
            fclose($file_to_read);
            return $lines;
        }
        public function addCategoryAttributes()
        {
            global $wpdb;
            $wpdb->query("TRUNCATE TABLE " . AP4L_TABLE_PREFIX . "cat_mapping_attributes");
            $csvFile     = AP4L_DIR . 'assets/csv/categoryList.csv';
            $categoryCSV = $this->csvToArray($csvFile);
            unset($categoryCSV[ 0 ]);
            if (! empty($categoryCSV)) {
                foreach ($categoryCSV as $key => $value) {
                    $wpdb->insert(
                        AP4L_TABLE_PREFIX . 'cat_mapping_attributes',
                        array(
                                'category_id'   => $value[ 0 ],
                                'category_name' => $value[ 1 ]
                            ),
                    );
                }
            }
        }
        /* ======================
         * Add Product Attributes
         * ======================
         */
        public function addProductAttributes()
        {
            global $wpdb;
            $wpdb->query("TRUNCATE TABLE " . AP4L_TABLE_PREFIX . "product_attributes");
            $productAttributes = array(
                'UPC'                           => array( 'General', 1, 'upc' ),
                'Product Brand'                 => array( 'General', 1, 'product_brand' ),
                'Part Number'                   => array( 'General', 1, 'part_number' ),
                'Name'                          => array( 'General', 1, 'name' ),
                'Description'                   => array( 'General', 0, 'description' ),
                'Product Weight'                => array( 'General', 1, 'product_weight' ),
                'Features'                      => array( 'General', 0, 'features' ),
                'Important Notes'               => array( 'General', 0, 'important_notes' ),
                'Country Of Manufacture'        => array( 'General', 0, 'country_of_manufacture' ),
                'Harmonized Tariff Code'        => array( 'General', 0, 'harmonized_tariff_code' ),
                'Instruction Files'             => array( 'General', 0, 'instruction_files' ),
                'Video Urls'                    => array( 'General', 0, 'video_urls' ),
                'California Proposition65 Warn' => array( 'General', 1, 'california_proposition65_warn' ),
                'Old Part Number'               => array( 'General', 0, 'old_part_number' ),
                'Vendor Sku'                    => array( 'Seller', 1, 'vendor_sku' ),
                'Condition'                     => array( 'Seller', 1, 'condition' ),
                'Condition Note'                => array( 'Seller', 0, 'condition_note' ),
                'Price'                         => array( 'Seller', 1, 'price' ),
                'Sale Price'                    => array( 'Seller', 0, 'sale_price' ),
                'Sale From Date'                => array( 'Seller', 0, 'sale_from_date' ),
                'Sale To Date'                  => array( 'Seller', 0, 'sale_to_date' ),
                'Quantity'                      => array( 'Seller', 1, 'quantity' ),
                'Reorder Value'                 => array( 'Seller', 0, 'reorder_level' ),
                'Handling Time No of Days'      => array( 'Seller', 1, 'handling_time_no_of_days' ),
                'Eligible For Return'           => array( 'Seller', 1, 'eligible_for_return' ),
                'Return Within Days'            => array( 'Seller', 0, 'return_within_days' ),
                'Allow Free Return'             => array( 'Seller', 1, 'allow_free_return' ),
                'Warranty Type'                 => array( 'Seller', 1, 'warranty_type' ),
                'Warranty Description'          => array( 'Seller', 0, 'warranty_description' ),
                'Tax Category'                  => array( 'Seller', 1, 'tax_category' ),
                'Shipping Weight'               => array( 'Seller', 0, 'shipping_weight' ),
            );
            foreach ($productAttributes as $key => $value) {
                $wpdb->insert(
                    AP4L_TABLE_PREFIX . 'product_attributes',
                    array(
                            'attribute_name' => $key,
                            'attribute_type' => $value[ 0 ],
                            'required'       => $value[ 1 ],
                            'ap4l_key'       => $value[ 2 ]
                        ),
                );
            }
        }
        public function addWcMappingAttributes()
        {
            global $wpdb;
            $wpdb->query("TRUNCATE TABLE " . AP4L_TABLE_PREFIX . "wc_mapping_attributes");
            $productAttributes = array(
                'UPC'                           => array( 'text', 1, '', "The product's numeric Universal Product Code (usually 12 digits)" ),
                'Product Brand'                 => array( 'text', 1, '', "The product's brand or manufacturer" ),
                'Part Number'                   => array( 'text', 1, '', "The product's part number" ),
                'Features'                      => array( 'text', 0, '', "A description of the product's notable features, such as purpose, functions, capabilities, and visual traits." ),
                'Important Notes'               => array( 'text', 0, '', "Important notes that you would like to notify the customer about in regards to the product, such as if it only fits a specific type of vehicle, or is illegal in certain states." ),
                'Country Of Manufacture'        => array( 'text', 0, '', "The country in which the product was manufactured." ),
                'Harmonized Tariff Code'        => array( 'text', 0, '', "The code used to determine the tariff/duty rate of the product if it is being shipped to the US from another country. https://hts.usitc.gov/current" ),
                'Instruction Files'             => array( 'text', 0, '', "A direct URL to the product's installation instructions. Only allows file types: PDF, PNG, JPG, JPEG" ),
                'Video Urls'                    => array( 'text', 0, '', "URL to the product's youtube or vimeo video" ),
                'California Proposition65 Warn' => array( 'select', 1, 'Yes|No', 'Accepted values are "true" or "false". Use "true" if the product does have a Prop65 warning' ),
                'Old Part Number'               => array( 'text', 1, '', 'If this is a superseded part for another discontinued part, you can add the old part number as a reference' ),
                'Condition'                     => array( 'select', 1, 'new|used-mint|used-likenew|used-good|used-fair|used-poor','The condition of your product. Accepted values: "New", "Used-Mint", "Used-LikeNew", "Used-Good", "Used-Fair", "Used-Poor"' ),
                'Condition Note'                => array( 'text', 0, '', 'Optionally add a note that can be shown to buyers to give additional detail about the product’s condition' ),
                'Sale From Date'                => array( 'date', 0, '', 'Optionally limit your sale price to only be used within this date range. If you have a "Sale Price" set, but no start date is specified here, your sale will start immediately. The timezone is always set to UTC, so your sale will start at 12:01AM UTC on the date specified here. Format the date as YYYY-MM-DD' ),
                'Sale To Date'                  => array( 'date', 0, '', 'Optionally limit your sale price to only be used within this date range. If you have a "Sale Price" set, but no end date is specified here, your sale will continue forever until you either remove the "Sale Price" or you add a "To Date" here. The timezone is always set to UTC, so your sale will end at 12:59PM UTC on the date specified here. Format the date as YYYY-MM-DD' ),
                'Reorder Value'                 => array( 'text', 0, '', 'If your quantity falls below this number, we will send you a notification' ),
                'Handling Time No of Days'      => array( 'text', 1, '', 'The number of days it takes for you to hand a product to a shipping carrier after receiving an order.' ),
                'Eligible For Return'           => array( 'select', 1, 'Yes|No', 'Accepted values are "true" or "false". Use "true" if the product is eligible for returns' ),
                'Return Within Days'            => array( 'text', 1, '', "The number of days a customer has to return an item after they've received their purchase." ),
                'Allow Free Return'             => array( 'select', 0, 'Yes|No', 'Accepted values are "true" or "false". Use "true" if you are offering free returns to the buyer, where you will be paying for any return shipping.' ),
                'Warranty Type'                 => array( 'select', 1, 'Manufacturer|Vendor|NoWarranty', 'Specify whether the warranty is from the "Vendor", "Manufacturer", or "NoWarranty"' ),
                'Warranty Description'          => array( 'text', 0, '', "Optionally add details about the warranty, for example: how long the product is under warranty for and under what circumstances will the warranty be accepted." ),
                'Tax Category'                  => array( 'select', 1, 'Taxable|Nontaxable', 'Enter "Taxable" if your goods can be taxed, and "Nontaxable" they are exempt from sales tax. Almost all products are "Taxable"' ),
                'Shipping Weight'               => array( 'text', 0, '', "Optionally add the product's shipping weight, including wrapping and packaging weight. If not set, the product's weight will be used when calculating shipping costs" ),
            );
            foreach ($productAttributes as $key => $value) {
                $wpdb->insert(
                    AP4L_TABLE_PREFIX . 'wc_mapping_attributes',
                    array(
                            'attribute_name'     => $key,
                            'attribute_type'     => $value[ 0 ],
                            'attribute_required' => $value[ 1 ],
                            'attribute_options'  => $value[ 2 ],
                            'attribute_desc'     => $value[ 3 ]
                        ),
                );
            }
        }
    }
}
