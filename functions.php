<?php
// Redirect shop page to brand page
add_action( 'wp', 'redirect' );
function redirect() {
  if ( is_shop() && !is_admin() && !is_search() ) {
    //header("Location: " . get_site_url() . "/product-category/brands/");
    //exit();
  }
}

/**
 * Storefront automatically loads the core CSS even if using a child theme as it is more efficient
 * than @importing it in the child theme style.css file.
 *
 * Uncomment the line below if you'd like to disable the Storefront Core CSS.
 *
 * If you don't plan to dequeue the Storefront Core CSS you can remove the subsequent line and as well
 * as the sf_child_theme_dequeue_style() function declaration.
 */
//add_action( 'wp_enqueue_scripts', 'sf_child_theme_dequeue_style', 999 );

/**
 * Dequeue the Storefront Parent theme core CSS
 */
function sf_child_theme_dequeue_style() {
    wp_dequeue_style( 'storefront-style' );
    wp_dequeue_style( 'storefront-woocommerce-style' );
}

/**
 * Note: DO NOT! alter or remove the code above this text and only add your custom PHP functions below this text.
 */

function wp_maintenance_mode() {
   if (!current_user_can('edit_themes') || !is_user_logged_in()) {
       wp_die('<h1>Barroso Pumps - Under Maintenance</h1><br />We are curently undergoing maintenance. Come back in 30 minutes or call our number at 787-230-2200.');
   }
}
if (!true) {
  add_action('get_header', 'wp_maintenance_mode');
}

// -- Header --

// Reorganize Secondary Navigation
add_action('wp_loaded', 'add_cart_to_secondary_nav');

function add_cart_to_secondary_nav() {
  remove_action( 'storefront_header', 'storefront_header_cart', 60);
  remove_action( 'storefront_header', 'storefront_secondary_navigation', 30);
  add_action( 'storefront_header', 'storefront_custom_secondary_nav', 40);
}

function storefront_custom_secondary_nav() {

  if ( has_nav_menu( 'secondary' ) ) {
    ?>
    <nav class="secondary-navigation" role="navigation" aria-label="<?php esc_html_e( 'Secondary Navigation', 'storefront' ); ?>">
      <?php
        /* Cart */
        if ( storefront_is_woocommerce_activated() ) {
    			if ( is_cart() ) {
    				$class = 'current-menu-item';
    			} else {
    				$class = '';
    			}
    		?>
    		<ul id="site-header-cart" class="site-header-cart menu">
    			<li class="<?php echo esc_attr( $class ); ?>">
    				<?php storefront_cart_link(); ?>
    			</li>
    			<li>
    				<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
    			</li>
    		</ul>
    		<?php
    		}
        /* Secondary Nav */
        if (is_user_logged_in()){
					wp_nav_menu(
						array(
							'theme_location'	=> 'secondary',
							'fallback_cb'			=> '',
						)
					);
				} else {
					wp_nav_menu(
						array(
							'menu'	=> 'register-menu',
							'fallback_cb'			=> '',
						)
					);
				}
      ?>
    </nav>
    <?php
  }
}

add_filter( 'storefront_handheld_footer_bar_links', 'add_phone_link' );
function add_phone_link( $links ) {
	$new_links = array(
		'phone' => array(
			'priority' => 10,
			'callback' => 'phone_link',
		),
	);

	$links = array_merge( $new_links, $links );

	return $links;
}

function phone_link() {
	echo '<a href="tel:+1787-230-2200"></a>';
}

// Custom header to frontpage
add_action( 'storefront_before_content', 'lookup_frontpage_header', 0 );

function lookup_frontpage_header() {

  $attributes = ["Horsepower", "Discharge Size", "Installation", "Child Category", "Max Flow"];

  if(is_front_page()){
    ?>
    <div class="hero-bg">
      <div class="hero-img">
        <div class="hero-content">
          <div class="hero-text">
            <header class="entry-header">
              <h1 class="entry-title">Find the right pump</h1>
            </header>
            <div class="entry-content"><?php
              display_lookup_fields($attributes);
              echo "<div id='lookup-btn' onclick='lookUp()'>Search products</div>";
            ?></div>
          </div>
          <div class="hero-contact">
            <p>Need help? Call us at <a href="tel:+1787-2302200">787-230-2200</a></p>
          </div>
        </div>
      </div>
    </div>
    <?php
  }
}

function display_lookup_fields( $attributes ) {
  ?>
    <div class="lookup-input">
      <select id="lookup-select-pa_product">
        <option value="" disabled>Choose Product</option>
        <option value="Pumps" selected>Pumps</option>
        <option value="Motors">Motors</option>
      </select>
    </div>
  <?php
  foreach ($attributes as $att){
    $attSlug = "pa_" . strtolower(str_replace(" ", "-", $att));

    switch($att){
      case "Child Category":
        $attTitle = "Category";
        break;
      default:
        $attTitle = $att;
    } 

    echo '<div class="lookup-input">';
    echo '<select id="lookup-select-' . $attSlug . '" class="sel-pumps">';
    print '<option value="" disabled selected>Choose ' . $attTitle . '</option>';
    $terms = get_terms($attSlug);
    foreach ( $terms as $term ) {
      echo '<option value="' . $term->slug . '">' . $term->name . " (" . $term->count . ")" . '</option>';
    }
    echo "</select></div>";
  }
}

// -- Homepage sections --

// Remove undesired sections
add_action( 'storefront_homepage', 'remove_sections', 0 );

function remove_sections(){
  remove_action( 'homepage', 'storefront_product_categories', 20);
  remove_action( 'homepage', 'storefront_recent_products', 30);
  remove_action( 'homepage', 'storefront_featured_products', 40);
  remove_action( 'homepage', 'storefront_popular_products', 50);
  remove_action( 'homepage', 'storefront_on_sale_products', 60);
  remove_action( 'homepage', 'storefront_best_selling_products', 70);
}

// Frontpage Pump Categories
add_action( 'homepage', 'pump_cats_section', 40 );

function get_pump_cats($pump_cats){
  $cats = array();
  foreach ($pump_cats as $cat_name) {
    $args = array(
        'taxonomy'     => 'product_cat',
        'name'         => $cat_name,
    );
    $cat = get_categories( $args )[0];
    $cats[] = $cat;
  }
  return $cats;
}

function pump_cats_section(){
  ?>
  <section class="storefront-product-section storefront-pump-cats">
    <h2 class="section-title">Pumps</h2>
    <div class="woocommerce">
      <ul class="products columns-4">
        <?php
        $pump_cats = array("Jet Pumps", "Sewage", "End Suction Pumps", "Engine Driven Pumps");
        $cats = get_pump_cats( $pump_cats );
        foreach ($cats as $cat){
          $thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
          $image = wp_get_attachment_url( $thumbnail_id );

          if ($cat->name === $pump_cats[array_key_last($pump_cats)]){
            echo '<li class="product-category product last">';
          } else {
            echo '<li class="product-category product test">';
          }
          ?>
            <a href="<?php echo get_site_url() . "/product-category/" . $cat->slug; ?>">
              <img src="<?php echo $image; ?>" alt="<?php echo $cat->name; ?>" width="324" height="" sizes="(max-width: 324px) 100vw, 324px" />
              <h2 class="woocommerce-loop-category__title"><?php echo $cat->name; ?></h2>
              <div><?php echo $cat->description; ?></div>
            </a>
          </li>
          <?php
        }
        ?>
      </ul>
    </div>
  </section>
  <div class="browse-container"><a href="<?php echo get_site_url() . "/pumps/"; ?>" class="browse">View all pumps >></a></div>
  <?php
}


// Frontpage Markets section
add_action( 'homepage', 'markets_section', 50 );

function get_markets($markets){
  $cats = array();
  foreach ($markets as $market) {
    $args = array(
        'taxonomy'     => 'product_cat',
        'name'         => $market,
    );
    $cat = get_categories( $args )[0];
    $cats[] = $cat;
  }
  return $cats;
}

function markets_section(){
  ?>
  <section class="storefront-product-section storefront-markets">
    <h2 class="section-title">Markets</h2>
    <div class="woocommerce">
      <ul class="products columns-4">
        <?php
        $cats = get_markets( array("Potable Water", "Wastewater", "Construction", "Agriculture") );
        foreach ($cats as $cat){
          $thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
          $image = wp_get_attachment_url( $thumbnail_id );
          ?>
          <li class="product-category product">
            <a href="<?php echo get_site_url() . "/product-category/markets/" . $cat->slug; ?>">
              <img src="<?php echo $image; ?>" alt="<?php echo $cat->name; ?>" width="324" height="" sizes="(max-width: 324px) 100vw, 324px" />
              <h2 class="woocommerce-loop-category__title"><?php echo $cat->name; ?></h2>
              <div><?php echo $cat->description; ?></div>
            </a>
          </li>
          <?php
        }
        ?>
      </ul>
    </div>
  </section>
  <div class="browse-container"><a href="<?php echo get_site_url() . "/product-category/markets/"; ?>" class="browse browse-blue">View all markets >></a></div>
  <?php
}

// Add help section
add_action( 'storefront_before_footer', 'help_section', 60 );

function help_section() {
  ?>
  <div class="help-section">
    <h1>Can't find what you're looking for?</h1>
    <h2>Call us at <a href="tel:+1787-2302200">787-230-2200</a></h2>
  </div>
  <?php
}

// -- PUMPS page --

// Display categories
add_action( 'storefront_page', 'display_pump_cats', 11);

function display_pump_cats() {
  $page = strip_tags(get_the_title());
  if ($page == "Pumps") {

    // Uncategorized Pumps
    $primary_cats = array("All Pumps", "End Suction Pumps", "Engine Driven Pumps");
    foreach ($primary_cats as $cat_name){
      $args = array(
        'taxonomy' => 'product_cat',
        'name' => $cat_name
      );
      $cat = get_categories($args)[0];
      echo "<a href='" . "../product-category/" . $cat->slug . "/'>";
      echo "<div class='custom-pump-cat " . $cat->slug . "'>";
      display_cat_image($cat);
      echo "<h3>" . $cat->name . "</h3></div></a>";
    }

    // Submersible Pumps
    display_custom_pump_cats("Submersible Pumps");

    // Residential Pumps
    display_custom_pump_cats("Residential Pumps");

  }
}

function display_custom_pump_cats($category_name){
  $args = array(
    'taxonomy' => 'product_cat',
    'name' => $category_name
  );
  $parent_cat = get_categories($args)[0];
  echo "<a href='" . "../product-category/" . $parent_cat->slug . "/'>";
  echo "<h2 class='custom-pump-title'>" . $category_name . "</h2></a>";
  // children
  $args = array(
    'taxonomy' => 'product_cat',
    'parent' => $parent_cat->term_id
  );

  $pump_cats = get_categories($args);
  foreach ($pump_cats as $cat){
    echo "<a href='" . "../product-category/" . $parent_cat->slug . "/" . $cat->slug . "/'>";
    echo "<div class='custom-pump-cat " . $cat->slug . "'>";
    display_cat_image($cat);
    echo "<h3>" . $cat->name . "</h3></div></a>";
  }
}

function display_cat_image($cat){
  $thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
  $image_url = wp_get_attachment_url( $thumbnail_id );
  if (!$image_url) {
    $args = array(
      'post_type' => 'product',
      'product_cat' => $cat->slug,
      'posts_per_page' => '1',
    );
    $loop = new WP_Query( $args );
    while ( $loop->have_posts() ) : $loop->the_post();

      if ( has_post_thumbnail() )
          $image = get_the_post_thumbnail( get_the_ID() );
          echo $image;

    endwhile;
    wp_reset_query(); // Remember to reset
  } else {
    echo "<img src='" . $image_url . "' >";
  }
}

// Add description to subcategories & Markets, exclude Brands
add_action( 'woocommerce_after_subcategory_title', 'custom_add_product_description', 0 );

function custom_add_product_description($category) {
  $cat_id = $category->term_id;
  $prod_term = get_term($cat_id,'product_cat');
  $desc = $prod_term->description;

  if (!is_product_category('brands')) {
    echo '<div>'.$desc.'</div>';
  }
}

// -- Single product page --

// Display SKU in single product page
add_action( 'woocommerce_single_product_summary', 'product_show_sku', 5 );

function product_show_sku(){
  global $product;
  echo '<span id="product-sku">SKU: ' . $product->get_sku() . '</span>';
}

// Display phone button for single products
function product_call_button(){
  ?>
    <a href="tel:+1787-2302200"><div class="product-quote-btn" id="product-call"><p>Call <span id="mobile">us</span><span id="desktop">787-230-2200</span> for more information</p></div></a>
  <?php
}

// Replace price and cart from product in loop if #listonly or #rent tag

//checks for tag
function hasTag($product, $slug){
  $tag = get_term_by('slug', $slug, 'product_tag');
  $tag_id = $tag->term_id;
  if (in_array($tag_id, $product->get_tag_ids())) {
    return true;
  }
}

// removes all prices in loop
// remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price',10);

// removes prices for #rentonly and #listony in loop
function woocommerce_template_loop_price() {
  global $product;
  if (!hasTag($product, 'ListOnly') && !hasTag($product, 'RentOnly'))
    wc_get_template( 'loop/price.php' );
}

add_action( 'woocommerce_after_shop_loop_item', 'replace_cart_for_quote', 9 );

function replace_cart_for_quote() {
  global $product;
  // removes add to cart
  if (hasTag($product, 'ListOnly') || hasTag($product, 'RentOnly')){
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
  }
  // adds request quote
  if (hasTag($product, 'ListOnly') || hasTag($product, 'Rent') || hasTag($product, 'RentOnly')){
    $product_url = get_site_url() . "/product/" . $product->slug;
    ?>
      <a href="<?php echo $product_url ?>" class="button loop-quote-btn">Request a Quote</a>
    <?php
  } elseif (!has_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart')){
    add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
  }
}

// add "or" option to rent products in single product page
add_action( 'woocommerce_after_add_to_cart_form', 'add_or_to_rent', 0  );

function add_or_to_rent() {
  global $product;
  if (hasTag($product, 'Rent')){
  ?>
  <p style="color:#666;margin-top:5px;margin-bottom:3px">Also availabe for rent.</p>
  <?php
  }
}

// Replace price and cart from single product page if #listonly or #rentonly
function woocommerce_template_single_price() {
  global $product;
  if ( !hasTag($product, 'ListOnly') && !hasTag($product, 'RentOnly') ) {
    wc_get_template( 'single-product/price.php' );
  }
}

// add rent prices to #rentonly
add_action( 'woocommerce_single_product_summary',  'rent_prices', 21 );

function rent_prices() {
  global $product;
  if ( hasTag($product, 'RentOnly') ){
    $day = $product->get_regular_price();
    $week = $day * 3;
    $month = $week * 3;
    ?>
    <h3 class="rental-prices-title">Rental Prices*</h3>
    <ul class="rental-prices">
      <li class="rent-price-title">Per Day: <span>$</span><p class="rent-price"><?php echo $day; ?></p></li>
      <li class="rent-price-title">Per Week: <span>$</span><p class="rent-price"><?php echo $week; ?></p></li>
      <li class="rent-price-title">Per Month: <span>$</span><p class="rent-price"><?php echo $month; ?></p></li>
    </ul>
    <p class="rental-prices-disclaimer">* Rate includes use of 8 hours a day. Additional usage will incur additional fees.</p>
    <?php
  }
}


// Adds quote request btn to #listonly and #rent products
function woocommerce_template_single_add_to_cart() {
  global $product;
  if ( !hasTag($product, 'ListOnly') && !hasTag($product, 'RentOnly') && !hasTag($product, 'Rent') ) {
    do_action( 'woocommerce_' . $product->get_type() . '_add_to_cart' );
    product_call_button();
  } else {
    // Sends email
    if (isset($_POST['submit'])) {
      add_action( 'shutdown', 'send_form_email', 30);
      ?>
        <div class="product-quote-btn form-sent">
          <p>Thank you, your request has been sent. We will get back to you within the next 24 hours.</p>
        </div>
      <?php
    } else {
      // Quote Button
      if (hasTag($product, 'ListOnly')) {
        $type = "sale";
        ?>
          <div class="product-quote-btn" id="quote-sale"><p>Purchase this product</p></div>
          <?php product_call_button(); ?>
          <script>var formType = "sale";</script>
        <?php
      } else {
        $type = "rent";
        if (hasTag($product, 'Rent')){
          do_action( 'woocommerce_' . $product->get_type() . '_add_to_cart' );
        }
        ?>
          <div class="product-quote-btn" id="quote-rent"><p>Rent this product</p></div>
          <?php product_call_button(); ?>
          <script>var formType = "rent";</script>
        <?php
      }
      user_info();
      global $wp;
      $postUrl = home_url( $wp->request );
      ?>
        <script>
          var postUrl = <?php echo json_encode($postUrl); ?>;
        </script>
        <style>
          .wcppec-checkout-buttons.woo_pp_cart_buttons_div {
            display: none;
          }
        </style>
      <?php
    }
  }
}

function send_form_email() {
  global $product;
  // variables
  $honeypot = $_POST["tel"];
  $recipient = "enrique@barrosopumps.com";
  $formValid = true;
  // form values
  $name = $_POST["firstname"] . " " . $_POST["surname"];
  $company = $_POST["company"];
  $email = $_POST["email"];
  $phone = $_POST["phone"];
  $productname = $product->name;
  $qty = $_POST["qty"];
  if ($type == "rent"){
    $subject = "Rental Quote";
    $date = $_POST["date"];
    $duration = $_POST["amount"] . " " . $_POST["duration"];
    $mailBody = "Company: $company\nName: $name\nEmail: $email\nPhone: $phone\nDate: $date\nDuration: $duration\nProduct: $productname\nQuantity: $qty";
  } else if ($type == "sale"){
    $subject = "Sale Quote";
    $mailBody = "Company: $company\nName: $name\nEmail: $email\nPhone: $phone\nProduct: $productname\nQuantity: $qty";
  }

  // validate name
  $name = filter_var(trim($name), FILTER_SANITIZE_STRING);
  if(!filter_var($name, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z\s]+$/")))){
    $formValid = false;
  }
  // validate company
  if ($company != ''){
    $company = filter_var(trim($company), FILTER_SANITIZE_STRING);
    if(!filter_var($company, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[!-z\s]+$/")))){
      $formValid = false;
    }
  }
  // validate email
  $email = filter_var($email, FILTER_SANITIZE_EMAIL);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $formValid = false;
  }
  // validate phone
  $phone = filter_var(trim($phone), FILTER_SANITIZE_STRING);
  if(!filter_var($phone, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^\+?(\(?[0-9]{3}\)?|[0-9]{3})[-\.\s]?[0-9]{3}[-\.\s]?[0-9]{4}$/")))){
    $formValid = false;
  }
  if ($type == "rent"){
    // validate date
    $date = filter_var(trim($date), FILTER_SANITIZE_STRING);
    if(!filter_var($date, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[\-\/\\\.\d]+$/")))){
      $formValid = false;
    }
    // validate duration
    $duration = filter_var(trim($duration), FILTER_SANITIZE_STRING);
    if(!filter_var($duration, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Zí\s\d]+$/")))){
      $formValid = false;
    }
  }
  // validate qty
  $qty = filter_var(trim($qty), FILTER_SANITIZE_STRING);
  if(!filter_var($qty, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[\d]+$/")))){
    $formValid = false;
  }

  if ($formValid){
    mail($recipient, $subject, $mailBody, "From: website@barrosopumps.com");
  }
}

function user_info() { // sends user info to js
  $user = wp_get_current_user();
  ?>
    <script>
      var uName = <?php echo json_encode($user->user_firstname); ?>;
      var uSurname = <?php echo json_encode($user->user_lastname); ?>;
      var uEmail = <?php echo json_encode($user->user_email); ?>;
    </script>
  <?php
}

// -- Checkout page --

// Add "Is your billing address in Puerto Rico?" input
add_action( 'woocommerce_before_checkout_billing_form', 'billing_in_pr', 0);

function billing_in_pr(){
  ?>
  <div class="address-pr-box">
    <p id="address-pr">Is your billing address in Puerto Rico?</p>
    <div class="pr-checkbox">
      <img class="pr-flag" src="<?php echo get_site_url() . "/wp-content/themes/storefront-child/assets/images/PR-flag.png" ?>" alt="PR_flag" title="Puerto Rico flag">
      <input type="checkbox" class="input-checkbox" id="is-pr-checkbox" onchange="isInPuertoRico()"></input>
    </div>
  </div>
  <?php
}

// Change select Country to Puerto Rico
function change_default_checkout_country() {
  return 'PR';
}
add_filter( 'default_checkout_billing_country', 'change_default_checkout_country' );

// Remove footer credit
add_action( 'init', 'custom_remove_footer_credit', 10 );

function custom_remove_footer_credit() {
    remove_action( 'storefront_footer', 'storefront_credit', 20 );
    add_action( 'storefront_footer', 'custom_storefront_credit', 20 );
}

function custom_storefront_credit() {
  ?>
 	<div class="site-info">
 		&copy; <?php echo get_bloginfo( 'name' ) . ' ' . get_the_date( 'Y' ); ?>
 	</div><!-- .site-info -->
 	<?php
}

// -- Mobile --

// Add Map to mobile header
  add_action('wp_loaded', 'add_map');

  function add_map() {
    add_action('storefront_header', 'storefront_custom_map', 42);
  }

  function storefront_custom_map() {
      ?>
      <div class="mobile-map">
        <a href="https://www.google.com/maps/place/Barroso+Pumps/@18.3871217,-65.980246,18.34z/data=!4m5!3m4!1s0x8c036696b9db3c51:0x781f69d2446d8d0d!8m2!3d18.3873439!4d-65.9796121"></a>
      </div>
      <?php
  }

// Change "My Account" in footer to Phone
add_filter( 'storefront_handheld_footer_bar_links', 'remove_handheld_footer_links' );
function remove_handheld_footer_links( $links ) {
	unset( $links['my-account'] );

	return $links;
}

// -- Contact Page --

// Contact us Map
add_action( 'storefront_custom_content', 'add_custom_contact', 1);

function add_custom_contact() {
  $page = strip_tags(get_the_title());
  if ($page == "Contact") {
    ?>
    <div class="map-bg">
      <div class="map-wrapper">
			  <iframe class="google-map" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1490.4237041354743!2d-65.98024599818983!3d18.387121650036708!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8c036696b9db3c51%3A0x781f69d2446d8d0d!2sBarroso%20Pumps!5e0!3m2!1sen!2sus!4v1579794426307!5m2!1sen!2sus"></iframe>
      </div>
    </div>
    <div class="contact-title">
      <h1>Contact Us</h1>
    </div>
		<div class="contact-logo">
			<?php storefront_site_title_or_logo(); ?>
		</div>
		<div class="contact-info">
			<p>Number</p>
      <a href="tel:+1787-230-2200" onclick="goog_report_conversion ('tel:+1787-230-2200')">787-230-2200</a>
			<p>Mobile Number</p>
      <a href="tel:+1787-399-1555">787-399-1555</a>
			<p>Email</p>
      <a href="mailto:enrique@barrosopumps.com">enrique@barrosopumps.com</a>
			<p>Sales Email</p>
      <a href="mailto:ventas@barrosopumps.com">ventas@barrosopumps.com</a>
		</div>
    <?php
  }
}

// -- Search --
// No products found
add_action('woocommerce_no_products_found', 'no_products_found_cta', 15);

function no_products_found_cta(){
  ?>
  <div class="no-products-found-cta">
    <div class="site-search">
      <div class="widget woocommerce widget_product_search">
        <form role="search" method="get" class="woocommerce-product-search" action="https://www.barrosopumps.com/">
          <label class="screen-reader-text" for="woocommerce-product-search-field-0">Search for:</label>
          <input type="search" id="woocommerce-product-search-field-custom" class="search-field" placeholder="Find the right pump&hellip;" value="" name="s" />
            <button type="submit" value="Search">Search</button>
          <input type="hidden" name="post_type" value="product" />
        </form>
      </div>
    </div>
    <p>The product you are looking for might be under a different name or it might not be listed on the website. Give us a call at <a href="tel:+1787-2302200">787-230-2200</a>.</p>
  </div>
  <?php
}

// -- Wordpress --

// Allow HTML in term (category, tag) descriptions
foreach ( array( 'pre_term_description' ) as $filter ) {
    remove_filter( $filter, 'wp_filter_kses' );
}

foreach ( array( 'term_description' ) as $filter ) {
    remove_filter( $filter, 'wp_kses_data' );
}

// Create custom product field: Product Key
// Shows product field in edit product page
add_action( 'woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields' ); 

// Saves product field
add_action( 'woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save' );

function woocommerce_product_custom_fields () {
  global $woocommerce, $post;
  echo '<div class=" product_custom_field ">';
  woocommerce_wp_textarea_input(
    array(
        'id' => '_custom_product_textarea',
        'placeholder' => 'Product Key',
        'label' => __('Product Key', 'woocommerce')
    )
  );
  echo '</div>';
}

// Updates database with custom field data
function woocommerce_product_custom_fields_save($post_id)
{
  // Custom Product Textarea Field
  $woocommerce_custom_procut_textarea = $_POST['_custom_product_textarea'];
  if (!empty($woocommerce_custom_procut_textarea))
      update_post_meta($post_id, '_custom_product_textarea', esc_html($woocommerce_custom_procut_textarea));
}

// Change password strength
function iconic_min_password_strength($strength) {
  return 1;
}

add_filter ( 'woocommerce_min_password_strength', 'iconic_min_password_strength', 10, 1 );

// Add support for custom SVG logo
add_action( 'after_setup_theme', 'svgsupport_theme_support', 11 );

function svgsupport_theme_support() {
	remove_theme_support( 'custom-logo' );
	add_theme_support( 'custom-logo', array(
		'flex-width'  => true,
		'flex-height' => true,
	) );
}

// Add script to html
function wpb_adding_scripts() {
    wp_register_script('custom_script', get_theme_file_uri() . '/script.js', array('jquery'),'1.1', true);
    wp_enqueue_script('custom_script');
}

add_action( 'wp_enqueue_scripts', 'wpb_adding_scripts', 999 );
