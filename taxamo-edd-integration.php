<?php
/**
 * Plugin Name:     Taxamo Integration for Easy Digital Downloads
 * Plugin URI:      @todo
 * Description:     Integrate Taxamo into Easy Digital Downloads. Make yourself Compatible with the VATMOSS EU Legislation
 * Version:         1.0.0
 * Author:          Winwar Media
 * Author URI:      http://winwar.co.uk
 * Text Domain:     taxamo-edd-integration
 *
 * @package         EDD\TaxamoEDDIntegration
 * @author          Winwar Media
 * @copyright       Copyright (c) 2014 Winwar Media
 *
 * IMPORTANT! Ensure that you make the following adjustments
 * before releasing your extension:
 *
 *
 * - Find all instances of @todo in the plugin and update the relevant
 *   areas as necessary.
 *
 * - All functions that are not class methods MUST be prefixed with the
 *   plugin name, replacing spaces with underscores. NOT PREFIXING YOUR
 *   FUNCTIONS CAN CAUSE PLUGIN CONFLICTS!
 */


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'EDD_Taxamo_EDD_Integration' ) ) {

    /**
     * Main EDD_Taxamo_EDD_Integration class
     *
     * @since       1.0.0
     */
    class EDD_Taxamo_EDD_Integration {

        /**
         *
         *
         * @var         EDD_Taxamo_EDD_Integration $instance The one true EDD_Taxamo_EDD_Integration
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_Taxamo_EDD_Integration
         */
        public static function instance() {
            if ( !self::$instance ) {
                self::$instance = new EDD_Taxamo_EDD_Integration();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_TAXAMOEDDINTEGRATION_VER', '1.0.0' );

            // Plugin path
            define( 'EDD_TAXAMOEDDINTEGRATION_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_TAXAMOEDDINTEGRATION_URL', plugin_dir_url( __FILE__ ) );

            // The URL of the updater
            define( 'EDD_TAXAMOEDDINTEGRATION_UPDATE_URL', 'http://winwar.co.uk' );

            // Name of Product
            define( 'EDD_TAXAMOEDDINTEGRATION_NAME', 'Taxamo Integration For Easy Digital Downloads' );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            // Include scripts
            require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/scripts.php';
            require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/functions.php';
            // require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/admin.php';
            require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/libraries/taxamo-api/Taxamo.php';
            require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/libraries/taxamo-api/enqueue-js.php';
            require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/libraries/taxamo-api/Taxamo/models/input_transaction_line.php';
            require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/libraries/taxamo-api/Taxamo/models/input_transaction.php';

            /**
             *
             *
             * @todo  Integrate Functions
             */

        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         *
         * @todo        The hooks listed in this section are a guideline, and
         *              may or may not be relevant to your particular extension.
         *              Please remove any unnecessary lines, and refer to the
         *              WordPress codex and EDD documentation for additional
         *              information on the included hooks.
         *
         *              This method should be used to add any filters or actions
         *              that are necessary to the core of your extension only.
         *              Hooks that are relevant to meta boxes, widgets and
         *              the like can be placed in their respective files.
         *
         *              IMPORTANT! If you are releasing your extension as a
         *              commercial extension in the EDD store, DO NOT remove
         *              the license check!
         */
        private function hooks() {

            global $edd_options;

            // EDD Hooks
            add_filter( 'edd_settings_extensions', array( $this, 'settings' ), 1 );

            add_filter( 'edd_get_cart_tax', array( $this, 'calculate_tax' ) );
            add_action( 'edd_cc_billing_top', array( $this, 'include_introduction_paragraph' ) );
            add_action( 'edd_purchase_form_user_info', array( $this, 'add_country_code' ) );
            add_action( 'edd_purchase_form_user_info', array( $this, 'include_vat_check' ) );

            add_filter( 'edd_checkout_error_checks', array( $this, 'check_vat_number' ), 10, 2 );
            add_filter( 'edd_payment_meta', array( $this, 'store_eu_data' ) );
            add_action( 'edd_purchase_data_before_gateway', array( $this, 'modify_tax' ), 10, 2 );
            add_action( 'edd_complete_purchase', array( $this, 'submit_order_to_taxamo' ) );

            add_action( 'edd_update_payment_status', array( $this, 'submit_refund_to_taxamo'), 200, 3 );

            add_action( 'edd_payment_personal_details_list', array( $this, 'view_order_vat_number' ), 10, 2 );


            // Admin Hooks
            if ( function_exists( 'edd_use_taxes' ) && !edd_use_taxes() ) {
                add_action( 'admin_notices', array( $this, 'enable_tax_notice' ) );
            }

            if ( !isset( $edd_options['taxedd_public_key'] ) || empty( $edd_options['taxedd_public_key'] ) || "" === $edd_options['taxedd_public_key'] || !isset( $edd_options['taxedd_private_key'] ) || empty( $edd_options['taxedd_private_key'] ) || "" === $edd_options['taxedd_private_key'] ) {
                add_action( 'admin_notices', array( $this, 'add_keys_notices' ) );
            }

            // Handle licensing
            /* if ( class_exists( 'EDD_License' ) ) {
                $license = new EDD_License( __FILE__, 'Taxamo Integration for Easy Digital Downloads', EDD_TAXAMOEDDINTEGRATION_VER, 'Winwar Media', null, 'http://winwar.co.uk' );
            } */
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_TAXAMOEDDINTEGRATION_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_plugin_name_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd-taxamo-edd-integration' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-taxamo-edd-integration', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-taxamo-edd-integration/' . $mofile;

            if ( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-taxamo-edd-integration/ folder
                load_textdomain( 'edd-taxamo-edd-integration', $mofile_global );
            } elseif ( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-taxamo-edd-integration/languages/ folder
                load_textdomain( 'edd-taxamo-edd-integration', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-taxamo-edd-integration', false, $lang_dir );
            }
        }


        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param array   $settings The existing EDD settings array
         * @return      array The modified EDD settings array
         */
        public function settings( $settings ) {
            $new_settings = array(
                array(
                    'id' => 'taxedd_header',
                    'name' => '<strong>' . __( 'Taxamo Integration', 'taxamoedd' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header',
                    'size' => 'regular'
                    ),
                array(
                    'id' => 'taxedd_public_key',
                    'name' => __( 'Taxamo Public Key', 'taxamoedd' ),
                    'desc' => __( 'Available from <a href="http://winwar.co.uk/recommends/taxamo/">Taxamo</a>.', 'taxamoedd' ),
                    'type' => 'text',
                    'size' => 'large',
                    'std'  => __( '', 'taxamoedd' )
                    ),
                array(
                    'id' => 'taxedd_private_key',
                    'name' => __( 'Taxamo Private Key', 'taxamoedd' ),
                    'desc' => __( 'Available from <a href="http://winwar.co.uk/recommends/taxamo/">Taxamo</a>.', 'taxamoedd' ),
                    'type' => 'text',
                    'size' => 'large',
                    'std'  => __( '', 'taxamoedd' )
                    ),
                array(
                    'id' => 'taxedd_custom_id_format',
                    'name' => __( 'Custom ID Format', 'taxamoedd' ),
                    'desc' => __( 'Format of the Custom ID.<br/>The string %%ID%% is replaced with the payment ID.', 'taxamoedd' ),
                    'type' => 'text',
                    'size' => 'large',
                    'std'  => __( '%%ID%%', 'taxamoedd' )
                    ),
                array(
                    'id' => 'taxedd_custom_invoice_format',
                    'name' => __( 'Custom Invoice Format', 'taxamoedd' ),
                    'desc' => __( 'Format of the Invoice Number.<br/>The string %%ID%% is replaced with the payment ID.', 'taxamoedd' ),
                    'type' => 'text',
                    'size' => 'large',
                    'std'  => __( '%%ID%%', 'taxamoedd' )
                    ),
                array(
                    'id' => 'taxedd_introduction_text',
                    'name' => __( 'Introduction Header Text', 'taxamoedd' ),
                    'desc' => __( 'This text will be added before the extra fields. Use this to link to your privacy policy and why you need this information', 'taxamoedd' ),
                    'type' => 'rich_editor',
                    'size' => 'large',
                    'std'  => __( '', 'taxamoedd' )
                    ),
                );

return array_merge( $settings, $new_settings );
}

        /*
         * Activation function fires when the plugin is activated.
         *
         * This function is fired when the activation hook is called by WordPress,
         *
         */
        public static function activation() {
            /*Activation functions here*/

        }

        /**
         * Adds to the checkout field the hidden value the taxamo software connects to.
         *
         * @return void
         */
        public static function add_country_code() {

            global $edd_options;
            global $user_ID;

            if ( is_user_logged_in() )
                $user_data = get_userdata( $user_ID );


            $intro_text = $edd_options['taxedd_introduction_text'];

            //echo '<p>' . $intro_text . '</p>';

            $taxamo = taxedd_get_country_code();

            if ( $taxamo && isset( $taxamo->country_code ) ) {
                ?>
                <input class="edd-country" type="hidden" name="edd_country" id="edd-country" value="<?php echo $taxamo->country_code; ?>"/>
                <?php
            }
        }

        /**
         * Adds above the billing address an introductory paragraph, should it be present.
         *
         * @return void
         */
        public static function include_introduction_paragraph() {
            global $edd_options;

            if ( isset( $edd_options['taxedd_introduction_text'] ) && !empty( $edd_options['taxedd_introduction_text'] ) && "" !== $edd_options['taxedd_introduction_text'] ) {
                $intro_text = $edd_options['taxedd_introduction_text'];
                echo '<p>' . $intro_text . '</p>';
            }

        }


        public static function include_vat_check() {
            ?>
            <p id="edd-vat-reg-check-wrap">
                <label for="edd_vatreg" class="edd-label">
                    <?php _e( 'I am registered for VAT in the EU', 'taxamoedd' ); ?>
                    <input class="edd-vatreg" type="checkbox" name="edd_vatreg" id="edd-vatreg" value="true" />
                </label>
            </p>

            <p id="edd-vat-reg-number-wrap">
                <label for="vat_number" class="edd-label">
                    <?php _e( 'VAT Number', 'taxamoedd' ); ?>
                </label>
                <span class="edd-description"><?php _e( 'If you are registered for VAT, place your VAT number here (no spaces).', 'taxamoedd' ); ?></span>
                <input type="text" id="vat_number" name="vat_number" class="vat-number edd-input" placeholder="<?php _e( 'VAT Number', 'taxamoedd' ); ?>" value=""/>
            </p>


            <?php
        }

        /**
         * Check the VAT Number is present if the checkbox is ticked.
         *
         * @return void
         */
        public static function check_vat_number( $valid_data, $data ) {
            global $edd_options;

            if ( $data['edd_vatreg'] ) {
                if ( !isset( $data['vat_number'] ) || empty( $data['vat_number'] ) || "" === $data['vat_number'] ) {
                    edd_set_error( 'taxedd-no-vat-number-error', __( 'If you are VAT registered, please enter a VAT number.', 'taxamoedd' ) );
                }

                $vatnumber = $string = preg_replace( '/\s+/', '', $data['vat_number'] );

                if ( isset( $edd_options['taxedd_private_key'] ) ) {
                    $private_key = $edd_options['taxedd_private_key'];

                    $vat_check = new Taxamo( new APIClient( $private_key, 'https://api.taxamo.com' ) );

                    $resp = $vat_check->validateTaxNumber( $data['billing_country'], $vatnumber );

                    if ( $resp->tax_deducted != "1" ) {
                        edd_set_error( 'taxedd-invalid-vat-number', __( 'The VAT number is invalid. Please double check or untick the VAT Registered Box.', 'taxamoedd' ) );
                    }

                } else {
                    edd_set_error( 'taxedd-no-prviate-key', __( 'Private key not present, so unable to complete purchase. Please contact shop owner.', 'taxamoedd' ) );
                }
            }
        }

        /**
         * Stores the country code in the payment meta.
         *
         * @return void
         */
        public static function store_eu_data( $payment_meta ) {

            $payment_meta['country']    = isset( $_POST['edd_country'] ) ? sanitize_text_field( $_POST['edd_country'] ) : $payment_meta['user_info']['address']['country'];
            $payment_meta['edd_vatreg'] = isset( $_POST['edd_vatreg'] ) ? true : false;

            // Check if user is VAT Registered with a Valid number. If so, set the Tax to 0.
            if ( isset( $_POST['vat_number'] ) ) {
                $payment_meta['vat_number'] = $_POST['vat_number'];
            } else {
                $payment_meta['vat_number'] = "";
                $payment_meta['tax'] = self::calculate_tax();
            }
            //wp_die(print_r($payment_meta));
            return $payment_meta;
        }



        /**
         * Add the Vat Number to View Order Details
         *
         * @param Array   $payment_meta The payment meta associated with this order.
         * @param Array   $user_info    The user information associated with this order.
         * @return void
         */
        public static function view_order_vat_number( $payment_meta, $user_info ) {
            $vatnumber = isset( $payment_meta['vat_number'] ) ? $payment_meta['vat_number'] : '';
            ?>
            <div class="column-container">
                <div class="column">
                    <strong><?php _e( 'VAT Number:', 'taxamoedd' ) ?></strong>&nbsp;
                    <input type="text" name="vatnumber" value="<?php esc_attr_e( $vatnumber ); ?>" class="medium-text" />
                    <p class="description"><?php _e( 'If the customer had a VAT number, it will be here', 'taxamoedd' ); ?></p>
                </div>
            </div>
            <?php
        }

        /**
         * Modify the tax if user has a VAT number.
         *
         * @param Array   $purchase_data Array of the purchase data
         * @param Array   $valid_data    Array of valid data
         * @return
         */
        public static function modify_tax( $purchase_data, $valid_data ) {

            // Check if we have a Valid VAT number, if so, remove the tax.
            if ( isset( $purchase_data['post_data']['vat_number'] ) && !empty( $purchase_data['post_data']['vat_number'] ) && "" !== $purchase_data['post_data']['vat_number'] &&
                $purchase_data['post_data']['edd_vatreg'] ) {
                $purchase_data['price'] = $purchase_data['price'] - $purchase_data['tax'];
            $purchase_data['tax'] = 0;
        }

        return $purchase_data;
    }

        /**
         *
         *
         * @param int     $payment_id ID of the EDD order just completed.
         * @global Array $edd_options   Array of all the EDD Options
         * @return void
         */
        public static function submit_order_to_taxamo( $payment_id ) {
            global $edd_options;

            if ( isset( $edd_options['taxedd_private_key'] ) ) {

                $private_key = $edd_options['taxedd_private_key'];

                $custom_id = $edd_options['taxedd_custom_id_format'];
                $custom_invoice  = $edd_options['taxedd_custom_invoice_format'];

                $custom_id = str_replace( '%%ID%%', $payment_id, $custom_id );
                $custom_invoice = str_replace( '%%ID%%', $payment_id, $custom_invoice );

                $taxamo = new Taxamo( new APIClient( $private_key, 'https://api.taxamo.com' ) );

                // Basic payment meta
                $payment_meta = edd_get_payment_meta( $payment_id );

                // Cart details
                $cart_items = edd_get_payment_meta_cart_details( $payment_id );

                $date = strtotime( $payment_meta['date'] );

                $transactionarray = array();

                $productid = 1;

                foreach ( $payment_meta['cart_details'] as $cart_detail ) {
                    $transaction_line = new Input_transaction_line();
                    $transaction_line->amount = $cart_detail['price'];
                    $transaction_line->custom_id = $cart_detail['name'] . $productid;
                    array_push( $transactionarray, $transaction_line );
                    $productid++;

                }

                $transaction = new Input_transaction();

                $transaction->currency_code = $payment_meta['currency'];
                $transaction->buyer_ip = $_SERVER['REMOTE_ADDR'];
                $transaction->billing_country_code = $payment_meta['country'];
                $transaction->buyer_email = $payment_meta['email'];
                $transaction->original_transaction_key = $payment_meta['key'];
                $transaction->custom_id = $custom_id;
                $transaction->invoice_number = $custom_invoice;

                if ( isset( $payment_meta['vat_number'] ) && !empty( $payment_meta['vat_number'] ) && "" !== $payment_meta['vat_number'] ) {
                    $transaction->buyer_tax_number = $payment_meta['vat_number'];

                    // We've already confirmed this is okay, so no need to check again.
                    $transaction->tax_deducted = true;
                }

                // Set Username
                if ( isset( $payment_meta['user_info']['first_name'] ) && isset( $payment_meta['user_info']['last_name'] ) ) {
                    $transaction->buyer_name = $payment_meta['user_info']['first_name'] . " " . $payment_meta['user_info']['last_name'];
                }

                // Build The Address
                $address = array();

                if ( isset( $payment_meta['user_info']['address']['line1'] ) && !empty( $payment_meta['user_info']['address']['line1'] )  && "" !== ( $payment_meta['user_info']['address']['line1'] ) ) {
                    $streename = array( "street_name"=>$payment_meta['user_info']['address']['line1'] );
                    $address = array_merge( $streename, $address );
                }

                if ( isset( $payment_meta['user_info']['address']['line2'] ) && !empty( $payment_meta['user_info']['address']['line2'] )  && "" !== ( $payment_meta['user_info']['address']['line2'] ) ) {
                    $addressdetailname = array( "address_detail"=>$payment_meta['user_info']['address']['line2'] );
                    $address = array_merge( $addressdetailname, $address );
                }

                if ( isset( $payment_meta['user_info']['address']['city'] ) && !empty( $payment_meta['user_info']['address']['city'] ) && "" !== ( $payment_meta['user_info']['address']['city'] ) ) {
                    $cityname = array( "city"=>$payment_meta['user_info']['address']['city'] );
                    $address = array_merge( $cityname, $address );
                }

                if ( isset( $payment_meta['user_info']['address']['state'] ) && !empty( $payment_meta['user_info']['address']['state'] )  && "" !== ( $payment_meta['user_info']['address']['state'] ) ) {
                    $regionname = array( "region"=>$payment_meta['user_info']['address']['state'] );
                    $address = array_merge( $regionname, $address );
                }

                if ( isset( $payment_meta['user_info']['address']['zip'] ) && !empty( $payment_meta['user_info']['address']['zip'] )  && "" !== ( $payment_meta['user_info']['address']['zip'] ) ) {
                    $postalcode = array( "postal_code"=>$payment_meta['user_info']['address']['zip'] );
                    $address = array_merge( $postalcode, $address );
                }

                $transaction->invoice_address = $address;

                $transaction->invoice_date = date( "Y-m-d", $date );

                $transaction->transaction_lines = $transactionarray;

                $resp = $taxamo->createTransaction( array( 'transaction' => $transaction ) );

                $taxamo->confirmTransaction( $resp->transaction->key, array( 'transaction' => $transaction ) );

                $transactionkey = array( 'taxamo_transaction_key' => $resp->transaction->key );
                $transactionlines = array();

                // Get all Transaction Lines and add keys to site.
                foreach ( $resp->transaction->transaction_lines as $transaction_line ) {

                    $temptransactionline = array(
                        "taxamo_line_key"=>$transaction_line->line_key,
                        "taxamo_total_amount"=>$transaction_line->total_amount
                        );

                    array_push($transactionlines, $temptransactionline);

                }
                
                $payment_meta = array_merge( $payment_meta, $transactionkey );
                $payment_meta = array_merge( $payment_meta, 
                    array( 'taxamo_transaction_lines' => $transactionlines));

                update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );

            }

        }


        function calculate_tax() {
            global $edd_options;

            if ( isset( $edd_options['taxedd_private_key'] ) ) {
                $private_key = $edd_options['taxedd_private_key'];
                $taxtaxamo = new Taxamo( new APIClient( $private_key, 'https://api.taxamo.com' ) );
                $countrycode = taxedd_get_country_code();
                $cart_items = edd_get_cart_content_details();


                $transaction = new Input_transaction();
                $transaction->currency_code = edd_get_currency();
                $transaction->buyer_ip = $_SERVER['REMOTE_ADDR'];
                $transaction->billing_country_code = $countrycode->country_code;
                $transactionarray = array();

                foreach ( $cart_items as $cart_item ) {

                    $customid++;
                    $transaction_line = new Input_transaction_line();
                    $transaction_line->amount = $cart_item['item_price'];
                    $transaction_line->custom_id = $cart_item['name'] . $customid;
                    array_push( $transactionarray, $transaction_line );

                }
                $transaction->transaction_lines = $transactionarray;

                $resp = $taxtaxamo->calculateTax( array( 'transaction' => $transaction ) );

                return $resp->transaction->tax_amount;
            }
        }

        /**
         * Add a notice to enable tax if switched off.
         *
         * @return void
         */
        function enable_tax_notice() {

            $url = admin_url( 'edit.php?post_type=download&page=edd-settings&tab=taxes' );
            ?>
            <div class="error">
                <p><?php _e( 'Taxamo Integration for Easy Digital Downloads needs Taxes to be Enabled. <a href="'.$url.'">Click Here to enable Taxes</a>.'  , 'taxamoedd' ); ?></p>
            </div>
            <?php
        }


        /**
         * Add a notice to add private and public keys.
         *
         * @return void
         */
        function add_keys_notices() {
            $url = admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions' );
            ?>
            <div class="error">
                <p><?php _e( 'You need to add the Taxamo Public & Private Keys to the extension. <a href="http://winwar.co.uk/recommends/taxamo"><strong>Sign Up for Taxamo</strong></a> and then <a href="'.$url.'">Click Here to add these fields</a>.'  , 'taxamoedd' ); ?></p>
            </div>
            <?php

        }


        /**
         * Submit the refund to Taxamo when refunded.
         *
         * @param int     $payment_id ID for the refund.
         * @param string  $new_status The new status
         * @param string  $old_status The old status
         * @return void
         */
        function submit_refund_to_taxamo( $payment_id, $new_status, $old_status ) {

            global $edd_options;

            if ( 'refunded' != $new_status )
                return;


            // Get Taxamo Tansaction Key.
            $payment_meta = edd_get_payment_meta( $payment_id );
            $taxamo_transaction_lines = $payment_meta['taxamo_transaction_lines'];
            $transaction_key = $payment_meta['taxamo_transaction_key'];

            // Get Order Total and create an array for it.
            foreach ($taxamo_transaction_lines as $taxamo_transaction_line ) {
                $line_key = $taxamo_transaction_line['taxamo_line_key'];
                $amount = $taxamo_transaction_line['taxamo_total_amount'];

                $taxamo_body_array = array( "total_amount"=>$amount,
                    "line_key" => $line_key );
                $taxamo_body_json = json_encode( $taxamo_body_array );

                // Create Taxamo Object and Submit a refund
                $private_key = $edd_options['taxedd_private_key'];
                $refundtaxamo = new Taxamo( new APIClient( $private_key, 'https://api.taxamo.com' ) );
                $resp = $refundtaxamo->createRefund( $transaction_key, $taxamo_body_array );
            }
        }
    }


    /**
     * The main function responsible for returning the one true EDD_Taxamo_EDD_Integration
     * instance to functions everywhere
     *
     * @since       1.0.0
     * @return      \EDD_Taxamo_EDD_Integration The one true EDD_Taxamo_EDD_Integration
     *
     * @todo        Inclusion of the activation code below isn't mandatory, but
     *              can prevent any number of errors, including fatal errors, in
     *              situations where your extension is activated but EDD is not
     *              present.
     */
    function EDD_Taxamo_EDD_Integration_load() {
        if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
            if ( ! class_exists( 'EDD_Extension_Activation' ) ) {
                require_once 'includes/class.extension-activation.php';
            }

            $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
            $activation = $activation->run();
            return EDD_Taxamo_EDD_Integration::instance();
        } else {
            return EDD_Taxamo_EDD_Integration::instance();
        }
    }

    /**
     * The activation hook is called outside of the singleton because WordPress doesn't
     * register the call from within the class hence, needs to be called outside and the
     * function also needs to be static.
     */
    register_activation_hook( __FILE__, array( 'EDD_Taxamo_EDD_Integration', 'activation' ) );

    add_action( 'plugins_loaded', 'EDD_Taxamo_EDD_Integration_load' );

} // End if class_exists check

if ( ! class_exists( 'EDD_License' ) )
    include dirname( __FILE__ ) . '/includes/EDD_License_Handler.php';