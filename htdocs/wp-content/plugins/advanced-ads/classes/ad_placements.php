<?php

/**
 * Advanced Ads
 *
 * @package   Advanced_Ads_Placements
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      http://webgilde.com
 * @copyright 2014 Thomas Maier, webgilde GmbH
 */

/**
 * grouping placements functions
 *
 * @since 1.1.0
 * @package Advanced_Ads_Placements
 * @author  Thomas Maier <thomas.maier@webgilde.com>
 */
class Advanced_Ads_Placements {

	/**
	 * get placement types
	 *
	 * @since 1.2.1
	 * @return arr $types array with placement types
	 */
	public static function get_placement_types() {
		$types = array(
			'default' => array(
				'title' => __( 'Manual Placement', ADVADS_SLUG ),
				'description' => __( 'Manual placement to use as function or shortcode.', ADVADS_SLUG ),
				'image' => ADVADS_BASE_URL . 'admin/assets/img/placements/manual.png'
				),
			'header' => array(
				'title' => __( 'Header Code', ADVADS_SLUG ),
				'description' => __( 'Injected in Header (before closing &lt;/head&gt; Tag, often not visible).', ADVADS_SLUG ),
				'image' => ADVADS_BASE_URL . 'admin/assets/img/placements/header.png'
				),
			'footer' => array(
				'title' => __( 'Footer Code', ADVADS_SLUG ),
				'description' => __( 'Injected in Footer (before closing &lt;/body&gt; Tag).', ADVADS_SLUG ),
				'image' => ADVADS_BASE_URL . 'admin/assets/img/placements/footer.png'
				),
			'post_top' => array(
				'title' => __( 'Before Content', ADVADS_SLUG ),
				'description' => __( 'Injected before the post content.', ADVADS_SLUG ),
				'image' => ADVADS_BASE_URL . 'admin/assets/img/placements/content-before.png'
				),
			'post_bottom' => array(
				'title' => __( 'After Content', ADVADS_SLUG ),
				'description' => __( 'Injected after the post content.', ADVADS_SLUG ),
				'image' => ADVADS_BASE_URL . 'admin/assets/img/placements/content-after.png'
				),
			'post_content' => array(
				'title' => __( 'Post Content', ADVADS_SLUG ),
				'description' => __( 'Injected into the post content. You can choose the paragraph after which the ad content is displayed.', ADVADS_SLUG ),
				'image' => ADVADS_BASE_URL . 'admin/assets/img/placements/content-within.png'
				),
		);
		return apply_filters( 'advanced-ads-placement-types', $types );
	}

	/**
	* update placements if sent
	 *
	 * @since 1.5.2
	*/
	static function update_placements(){

		// check user permissions
		if(!current_user_can('manage_options')){
		return;
		}
		remove_query_arg('message');

		if ( isset($_POST['advads']['placement']) && check_admin_referer( 'advads-placement', 'advads_placement' ) ){
		$success = self::save_new_placement( $_POST['advads']['placement'] );
		}
		// save placement data
		if ( isset($_POST['advads']['placements']) && check_admin_referer( 'advads-placement', 'advads_placement' )){
		$success = self::save_placements( $_POST['advads']['placements'] );
		}

		if(isset($success)){
		$message = $success ? 'updated' : 'error';
		wp_redirect( add_query_arg(array('message' => $message)) );
		}
	}

	/**
	 * save a new placement
	 *
	 * @since 1.1.0
	 * @param array $new_placement
	 * @return mixed true if saved; error message if not
	 */
	public static function save_new_placement($new_placement) {
		// load placements // -TODO use model
		$placements = Advanced_Ads::get_ad_placements_array();

		// create slug
		$new_placement['slug'] = sanitize_title( $new_placement['name'] );

		// check if slug already exists or is empty
		if ( $new_placement['slug'] === '' || isset( $placements[$new_placement['slug']]) || !isset( $new_placement['type'] ) ) {
			return false;
		}

		// make sure only allowed types are being saved
		$placement_types = Advanced_Ads_Placements::get_placement_types();
		$new_placement['type'] = (isset($placement_types[$new_placement['type']])) ? $new_placement['type'] : 'default';
		// escape name
		$new_placement['name'] = esc_attr( $new_placement['name'] );

		// add new place to all placements
		$placements[$new_placement['slug']] = array(
			'type' => $new_placement['type'],
			'name' => $new_placement['name'],
			'item' => $new_placement['item']
		);

		// save array
		update_option( 'advads-ads-placements', $placements );

		return true;
	}

	/**
	 * save placements
	 *
	 * @since 1.1.0
	 * @param array $placement_items
	 * @return mixed true if saved; error message if not
	 */
	public static function save_placements($placement_items) {

		// load placements // -TODO use model
		$placements = Advanced_Ads::get_ad_placements_array();

		foreach ( $placement_items as $_placement_slug => $_placement ) {
			// remove the placement
			if ( isset($_placement['delete']) ) {
				unset($placements[$_placement_slug]);
				continue;
			}
			// save item
			if ( isset($_placement['item']) ) {
				$placements[$_placement_slug]['item'] = $_placement['item']; }
			// save item options
			if ( isset($_placement['options']) ){
				$placements[$_placement_slug]['options'] = $_placement['options'];
				if ( isset($placements[$_placement_slug]['options']['index']) ) {
					$placements[$_placement_slug]['options']['index'] = absint( $placements[$_placement_slug]['options']['index'] ); }
			} else {
				$placements[$_placement_slug]['options'] = array();
			}
		}

		// save array
		update_option( 'advads-ads-placements', $placements );

		return true;
	}

	/**
	 * get items for item select field
	 *
	 * @since 1.1
	 * @return arr $select items for select field
	 */
	public static function items_for_select() {
		$select = array();
		$model = Advanced_Ads::get_instance()->get_model();

		// load all ad groups
		$groups = $model->get_ad_groups();
		foreach ( $groups as $_group ) {
			$select['groups']['group_' . $_group->term_id] = $_group->name;
		}

		// load all ads
		$ads = $model->get_ads( array('orderby' => 'name', 'order' => 'ASC') );
		foreach ( $ads as $_ad ) {
			$select['ads']['ad_' . $_ad->ID] = $_ad->post_title;
		}

		return $select;
	}

	/**
	 * get html tags for content injection
	 *
	 * @since 1.3.5
	 * @return arr $tags array with tags that can be used for content injection
	 */
	public static function tags_for_content_injection(){
		$tags = array(
			'p' => sprintf( __( 'paragraph (%s)', ADVADS_SLUG ), '&lt;p&gt;' ),
			'h2' => sprintf( __( 'headline 2 (%s)', ADVADS_SLUG ), '&lt;h2&gt;' ),
			'h3' => sprintf( __( 'headline 3 (%s)', ADVADS_SLUG ), '&lt;h3&gt;' ),
			'h4' => sprintf( __( 'headline 4 (%s)', ADVADS_SLUG ), '&lt;h4&gt;' ),
		);

		return $tags;
	}

	/**
	 * return content of a placement
	 *
	 * @since 1.1.0
	 * @param string $id   slug of the display
	 * @param array  $args optional arguments (passed to child)
	 */
	public static function output( $id = '', $args = array() ) {
		// get placement data for the slug
		if ( $id == '' ) {
			return;
		}

		$placements = Advanced_Ads::get_ad_placements_array();

		if ( isset( $placements[ $id ]['item'] ) && $placements[ $id ]['item'] !== '' ) {
			$_item = explode( '_', $placements[ $id ]['item'] );

			if ( ! isset( $_item[1] ) || empty( $_item[1] ) ) {
				return ;
			}

			// inject options
			if ( isset( $placements[ $id ]['options'] ) && is_array( $placements[ $id ]['options'] ) ) {
				foreach ( $placements[ $id ]['options'] as $_k => $_v ) {
					if ( ! isset( $args[ $_k ] ) ) {
						$args[ $_k ] = $_v;
					}
				}
			}
			// return either ad or group content
			switch ( $_item[0] ) {
				case 'ad':
				case Advanced_Ads_Select::AD:
					// create class from placement id (not if header injection)
					if ( ! isset( $placements[ $id ]['type'] ) || $placements[ $id ]['type'] !== 'header' ) {
						if ( ! isset( $args['output'] ) ) {
							$args['output'] = array();
						}
						if ( ! isset( $args['output']['class'] ) ) {
							$args['output']['class'] = array();
						}
						$class = 'advads-' . $id;
						if ( ! in_array( $class, $args['output']['class'] ) ) {
							$args['output']['class'][] = $class;
						}
					}

					// fix method id
					$_item[0] = Advanced_Ads_Select::AD;
					break;

				// avoid loops (programmatical error)
				case Advanced_Ads_Select::PLACEMENT:
					return;

				case Advanced_Ads_Select::GROUP:
				default:
			}

			// add the placement to the global output array
			$advads = Advanced_Ads::get_instance();
			$advads->current_ads[] = array('type' => 'placement', 'id' => $id, 'title' => $placements[ $id ]['name']);

			return Advanced_Ads_Select::get_instance()->get_ad_by_method( (int) $_item[1], $_item[0], $args );
		}
	}

	/**
	 * inject ads directly into the content
	 *
	 * @since 1.2.1
	 * @param string $placement_id id of the placement
	 * @param arr $options placement options
	 * @param string $content
	 * @return type
	 * @link inspired by http://www.wpbeginner.com/wp-tutorials/how-to-insert-ads-within-your-post-content-in-wordpress/
	 */
	public static function &inject_in_content($placement_id, $options, &$content) {
		// test ad is emtpy
		$whitespaces = json_decode('"\t\n\r \u00A0"');
		$adContent = Advanced_Ads_Select::get_instance()->get_ad_by_method( $placement_id, 'placement', $options );
		if ( trim( $adContent, $whitespaces ) === '' ) {
			return $content;
		}

		// parse document as DOM (fragment - having only a part of an actual post given)
		// -TODO may want to verify the wpcharset is supported by server (mb_list_encodings)
		// -TODO mb extension might not be available for really old hosts
		// prevent messages from dom parser
		$wpCharset = get_bloginfo('charset');
		$content = mb_convert_encoding($content, 'HTML-ENTITIES', $wpCharset);

		$dom = new DOMDocument('1.0', $wpCharset);
		// may loose some fragments or add autop-like code
		libxml_use_internal_errors(true); // avoid notices and warnings - html is most likely malformed
		$success = $dom->loadHtml('<!DOCTYPE html><html><meta http-equiv="Content-Type" content="text/html; charset=' . $wpCharset . '" /><body>' . $content);
		libxml_use_internal_errors(false);
		if ($success !== true) {
			// -TODO handle cases were dom-parsing failed (at least inform user)
			return $content;
		}

		// parse arguments
		$tag = isset($options['tag']) ? $options['tag'] : 'p';
		$tag = preg_replace('/[^a-z0-9]/i', '', $tag); // simplify tag

		// only has before and after
		$before = isset($options['position']) && $options['position'] === 'before';
		$paragraph_id = isset($options['index']) ? $options['index'] : 1;
		$paragraph_id = max( 1, (int) $paragraph_id );
		$paragraph_select_from_bottom = isset($options['start_from_bottom']) && $options['start_from_bottom'];

		// select positions
		$xpath = new DOMXPath($dom);
		$items = $xpath->query('/html/body/' . $tag);
		$offset = null;

		// filter empty tags from items
		$paragraphs = array();
		foreach ($items as $item) {
			if ( isset($item->textContent) && trim($item->textContent, $whitespaces) !== '' ) {
				$paragraphs[] = $item;
			}
		}

		$paragraph_count = count($paragraphs);
		if ($paragraph_count >= $paragraph_id) {
			$offset = $paragraph_select_from_bottom ? $paragraph_count - $paragraph_id : $paragraph_id - 1;

			// convert HTML to XML!
			$adDom = new DOMDocument('1.0', $wpCharset);
			libxml_use_internal_errors(true);
			$adDom->loadHtml('<!DOCTYPE html><html><meta http-equiv="Content-Type" content="text/html; charset=' . $wpCharset . '" /><body>' . $adContent);
			$adNode = $adDom->lastChild->lastChild; // >html>body
			libxml_use_internal_errors(false);
			$adContent = $adDom->saveXML($adNode);
			$adContent = substr($adContent, 6, -7);
			$adNode = $dom->createDocumentFragment();
			$adNode->appendXML($adContent);

			// inject
			if ($before) {
				$refNode = $paragraphs[$offset];
				$refNode->parentNode->insertBefore($adNode, $refNode);
			} else {
				// append before next node or as last child to body
				$refNode = $paragraphs[$offset]->nextSibling;
				if (isset($refNode)) {
					$refNode->parentNode->insertBefore($adNode, $refNode);
				} else {
					// append to body; -TODO using here that we only select direct children of the body tag
					$paragraphs[$offset]->parentNode->appendChild($adNode);
				}
			}
		}

		// convert to text-representation
		$content = $dom->saveHTML();
		// remove head and tail (required for dom parser but unwanted for content)
		$content = substr($content, stripos($content, '<body>') + 6);
		$content = str_replace(array('</body>', '</html>'), '', $content);

		// no fall-back desired: if there are too few paragraphs do nothing

		// fix shortcode quotes (malformed by backend editor)
		$matches = array();
		if (0 < preg_match_all('/\[[^]]+\]/Siu', $content, $matches, PREG_OFFSET_CAPTURE) && isset($matches[0])) {
			foreach ($matches[0] as $match) {
				$offset = $match[1];
				$content = substr($content, 0, $offset) . str_replace(array('“', '″', '&#8220;', '&quote;', '&#8243;'), '"', $match[0]) . substr($content, $offset + strlen($match[0]));
			}
		}

		return $content;
	}
}
