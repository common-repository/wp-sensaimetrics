<?php
// No direct access to this file
defined( 'ABSPATH' ) or die();

class WP_Sensaimetrics_Connector {
  public function load() {
    $this->load_dependencies();
    $this->load_admin();
    $this->enqueue_script();
  }

  private function load_dependencies() {
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sensaimetrics-admin.php';
    
  }

  private function load_admin() {
    $admin = new Wp_Sensaimetrics_Admin();
    add_action( 'admin_init', array( $admin, 'register_my_setting' ) );
    add_action( 'admin_menu', array( $admin, 'create_nav_page' ) );
  }

  public static function build_sensaimetrics_script() {
    global $woocommerce;
    global $wp;
    global $product;
    $settings = get_option( 'wp_sensaimetrics' );
    $is_admin = is_admin() || current_user_can('manage_options');

    $sensaimetrics_id = trim($settings['sensaimetrics_id']);
    if (!$sensaimetrics_id) {
      return;
    }

    if (!is_array($settings) || ($is_admin && 'yes' === $settings['disable_for_admin'])) {
      return;
    }

    echo "
    <script>
      (function (s, e, n, s, a, i) {
        s.sm = s.sm || {start: new Date().getTime()};
        s._smSettings = {id: i};
        var f = e.getElementsByTagName('head')[0],j = e.createElement('script');
        j.async = true;j.src = n+i+a+'?v='+s.sm.start;f.parentNode.insertBefore(j, f);
      })('', document, 'https://tracker.sensaimetrics.io/static/sensaiTracker-',window,'.js','".$sensaimetrics_id."');
    </script>
    ";

    try {
      $this->sensaimetrics_check_login();
      $this->sensaimetrics_get_cart();
      $this->sensaimetrics_product_page();
      $this->sensaimetrics_is_order_page();
    } catch(Exception $e){
      echo "<script> console.error('Tracker') </script>";
    }
  }

  private function enqueue_script() {
    add_action( 'wp_head', array($this, 'build_sensaimetrics_script') );
  }

  public function sensaimetrics_is_order_page() {
    global $wp;
    if ( is_order_received_page() ) {
      $order_id  = absint( $wp->query_vars['order-received'] );
      $this->sensaimetrics_get_order($order_id);
    }
  }

  public function sensaimetrics_product_page() {
    if( is_product() ) {
      $product = wc_get_product();
      $product_id = $product->get_id();
      $product_sku=$product->get_sku();
      $product_name=$product->get_title();
      $code = '<script>
        function checkVariable() {
          try {
            if(typeof sensai !== "undefined"){
              sensai.broadcast("product_page",{
                product_id:'.$product_id.',
                product_sku:"'.$product_sku.'",
                product_name:"'.$product_name.'",
                product_url: window.document.location.href,
              });
            }
          } catch(err) {
            console.error("SensaiMetricsProductPage");
          }
        }
        setTimeout(checkVariable,1000);
      </script>';
      echo $code;
    }
  }

  public function sensaimetrics_check_login() {
    if( is_user_logged_in() ) {
      $user = wp_get_current_user();
      $customer_id = $user->data->ID;
      $customer_email = $user->data->user_email;
      $code = '<script>
        function checkVariable() {
          try {
            if(typeof sensai !== "undefined"){
              sensai.broadcast("login",{
                customer_id:'.$customer_id.',
                customer_email:"'.$customer_email.'"
              });
            }
          } catch(err) {
            console.error("SensaiMetricsLogin");
          }
        }
        setTimeout(checkVariable,1000);
      </script>';
      echo $code;
    }
  }

  public function sensaimetrics_get_cart() {
    $cart = WC()->cart->get_cart();
    $coupons = WC()->cart->get_coupons();
    $cart_shipping_total= WC()->cart->shipping_total;
    $cart_total = WC()->cart->cart_contents_total;
    $cart_hash = WC()->cart->get_cart_hash();
    $items = [];

    foreach($cart as $item){
      $product = wc_get_product($item['product_id']);
      $product_info['product_id']=$item['product_id'];
      $product_info['url']=get_permalink($item['product_id']);
      $product_info['slug']=$product->get_slug();
      $product_info['sku']=$product->get_sku();
      $product_info['name']=$product->get_title();
      $product_info['description']=$product->get_description();
      $product_info['price']=$product->get_price();
      $product_info['line_total']=$item['line_total'];
      $product_info['quantity']=$item['quantity'];
      array_push($items, $product_info);
    }

    if( sizeof($items) != 0 ){ 
      $items = json_encode($items);
      $coupons = json_encode($coupons);
      $code = '<script>
        function checkVariable() {
          try{
            if(typeof sensai !== "undefined"){
              sensai.broadcast("cart",{
                "coupons":'.$coupons.',
                "cart_hash": "'.$cart_hash.'",
                "cart_total":'.$cart_total.',
                "cart_shipping_total":'.$cart_shipping_total.',
                "items":'.$items.'
              });
            }
          } catch(err) {
            console.error("SensaMetricsCart");
          }
        }

        setTimeout(checkVariable,1000);
      </script>';
      echo $code;
    }
  }

public function sensaimetrics_get_order($order_id) {
    $order = wc_get_order($order_id);
    $coupons = $order->get_used_coupons();
    $coupon_arr = [];
    foreach($coupons as $coupon_name) {
      $coupon_post_obj = get_page_by_title($coupon_name, OBJECT, 'shop_coupon');
      $coupon_id = $coupon_post_obj->ID;
      $coupon_obj = new WC_Coupon($coupon_id);
      $coupon_data['id'] = $coupon_id;
      $coupon_data['discount_type'] = $coupon_obj->discount_type;
      $coupon_data['amount'] = $coupon_obj->amount;
      $coupon_data['code'] = $coupon_obj->code;
      $coupon_data['date_created'] = $coupon_obj->date_created->date;
      $coupon_data['date_modified'] = $coupon_obj->date_modified->date;
      $coupon_data['usage_count'] = $coupon_obj->usage_count;
      $coupon_data['product_ids'] = $coupon_obj->product_ids;
      $coupon_data['excluded_product_ids'] = $coupon_obj->excluded_product_ids;
      $coupon_data['usage_limit'] = $coupon_obj->usage_limit;
      $coupon_data['usage_limit_per_user'] = $coupon_obj->usage_limit_per_user;
      $coupon_data['limit_usage_to_x_items'] = $coupon_obj->limit_usage_to_x_items;
      $coupon_data['product_categories'] = $coupon_obj->product_categories;
      $coupon_data['excluded_product_categories'] = $coupon_obj->excluded_product_categories;
      $coupon_data['minimum_amount'] = $coupon_obj->get_minimum_amount();
      $coupon_data['maximum_amount'] = $coupon_obj->get_maximum_amount();
      $coupon_data['free_shipping'] = $coupon_obj->get_free_shipping();
      $coupon_data['description'] = $coupon_obj->get_description();
      array_push($coupon_arr, $coupon_data);
    }
    $coupons = json_encode($coupon_data);
    $order_total_shipping = $order->get_total_shipping();
    $order_total = $order->get_total();
    $order_total_tax = $order->get_total_tax();
    $order_discount_total = $order->get_discount_total();
    $transaction_id = $order->transaction_id;
    $order_date = $order->order_date;
    
    $items = $order->get_items();
    $products = [];
    foreach($items as $item) {
      $product = wc_get_product($item['product_id']);
      $product_info['product_id']=$item['product_id'];
      $product_info['url']=get_permalink($item['product_id']);
      $product_info['sku']=$product->get_sku();
      $product_info['slug']=$product->get_slug();
      $product_info['name']=$product->get_title();
      $product_info['description']=$product->get_description();
      $product_info['price']=$product->get_price();
      $product_info['line_total']=$item['line_total'];
      $product_info['quantity']=$item['quantity'];
      $product_info['variation_id']=$item['variation_id'];
      $product_info['type']=$product->get_type();
      array_push($products, $product_info);
    }

    $shipping_lines = [];
    foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){
      $shipping_data['order_id']  = $order_id;
      $shipping_data['id']        = $shipping_item_obj->get_id();
      $shipping_data['name']      = $shipping_item_obj->get_name();
      $shipping_data['type']      = $shipping_item_obj->get_type();
      $shipping_data['title']     = $shipping_item_obj->get_method_title();
      $shipping_data['id']        = $shipping_item_obj->get_method_id(); // The method ID
      $shipping_data['total']     = $shipping_item_obj->get_total();
      $shipping_data['total_tax'] = $shipping_item_obj->get_total_tax();
      $shipping_data['taxes']     = $shipping_item_obj->get_taxes();
      array_push($shipping_lines, $shipping_data);
    }

    $shipping = array('city' => $order->get_shipping_city(),
                      'state' => $order->get_shipping_state(),
                      'country' => $order->get_shipping_country(),
                      'postcode' => $order->get_shipping_postcode(),
                      'customer_id' => $order->get_user_id(),
                      'customer_email' => $order->get_billing_email(),
                    );
    $billing = array('city' => $order->get_billing_city(),
                     'state' => $order->get_billing_state(),
                     'country' => $order->get_billing_country(),
                     'postcode' => $order->get_billing_postcode()
                    );
    $shipping_lines = json_encode($shipping_lines);
    $shipping = json_encode($shipping);
    $billing = json_encode($billing);
    $products = json_encode($products);
    echo '<script>
      function checkVariable() {
        try{
          if(typeof sensai !== "undefined"){
            sensai.broadcast("purchase",{
              "order_id":'.$order_id.',
              "coupons":'.$coupons.',
              "total_shipping":'.$order_total_shipping.',
              "total":'.$order_total.',
              "date":"'.$order_date.'",
              "items":'.$products.',
              "shipping_lines":'.$shipping_lines.',
              "shipping":'.$shipping.',
              "billing":'.$billing.',
            });
           }
        } catch(err) {
          console.error("SensaiMetricsOrder");
        }
      }
      setTimeout(checkVariable,1000);
    </script>';
  }

}