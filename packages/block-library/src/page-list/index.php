<?php
/**
 * Server-side rendering of the `core/pages` block.
 *
 * @package WordPress
 */

/**
 * Build an array with CSS classes and inline styles defining the colors
 * which will be applied to the pages markup in the front-end when it is a descendant of navigation.
 *
 * @param  array $attributes Block attributes.
 * @param  array $context    Navigation block context.
 * @return array Colors CSS classes and inline styles.
 */
function block_core_page_list_build_css_colors( $attributes, $context ) {
	$colors = array(
		'css_classes'           => array(),
		'inline_styles'         => '',
		'overlay_css_classes'   => array(),
		'overlay_inline_styles' => '',
	);

	// Text color.
	$has_named_text_color  = array_key_exists( 'textColor', $attributes );
	$has_picked_text_color = array_key_exists( 'customTextColor', $attributes );
	$has_custom_text_color = isset( $context['style']['color']['text'] );

	// If has text color.
	if ( $has_custom_text_color || $has_picked_text_color || $has_named_text_color ) {
		// Add has-text-color class.
		$colors['css_classes'][] = 'has-text-color';
	}

	if ( $has_named_text_color ) {
		// Add the color class.
		$colors['css_classes'][] = sprintf( 'has-%s-color', gutenberg_experimental_to_kebab_case( $attributes['textColor'] ) );
	} elseif ( $has_picked_text_color ) {
		$colors['inline_styles'] .= sprintf( 'color: %s;', $attributes['customTextColor'] );
	} elseif ( $has_custom_text_color ) {
		// Add the custom color inline style.
		$colors['inline_styles'] .= sprintf( 'color: %s;', $context['style']['color']['text'] );
	}

	// Background color.
	$has_named_background_color  = array_key_exists( 'backgroundColor', $attributes );
	$has_picked_background_color = array_key_exists( 'customBackgroundColor', $attributes );
	$has_custom_background_color = isset( $context['style']['color']['background'] );

	// If has background color.
	if ( $has_custom_background_color || $has_picked_background_color || $has_named_background_color ) {
		// Add has-background class.
		$colors['css_classes'][] = 'has-background';
	}

	if ( $has_named_background_color ) {
		// Add the background-color class.
		$colors['css_classes'][] = sprintf( 'has-%s-background-color', gutenberg_experimental_to_kebab_case( $attributes['backgroundColor'] ) );
	} elseif ( $has_picked_background_color ) {
		$colors['inline_styles'] .= sprintf( 'background-color: %s;', $attributes['customBackgroundColor'] );
	} elseif ( $has_custom_background_color ) {
		// Add the custom background-color inline style.
		$colors['inline_styles'] .= sprintf( 'background-color: %s;', $context['style']['color']['background'] );
	}

	// Overlay text color.
	$has_named_overlay_text_color  = array_key_exists( 'overlayTextColor', $attributes );
	$has_picked_overlay_text_color = array_key_exists( 'customOverlayTextColor', $attributes );

	// If it has a text color.
	if ( $has_named_overlay_text_color || $has_picked_overlay_text_color ) {
		$colors['overlay_css_classes'][] = 'has-text-color';
	}

	// Give overlay colors priority, fall back to Navigation block colors, then global styles.
	if ( $has_named_overlay_text_color ) {
		$colors['overlay_css_classes'][] = sprintf( 'has-%s-color', gutenberg_experimental_to_kebab_case( $attributes['overlayTextColor'] ) );
	} elseif ( $has_picked_overlay_text_color ) {
		$colors['overlay_inline_styles'] .= sprintf( 'color: %s;', $attributes['customOverlayTextColor'] );
	}

	// Overlay background colors.
	$has_named_overlay_background_color  = array_key_exists( 'overlayBackgroundColor', $attributes );
	$has_picked_overlay_background_color = array_key_exists( 'customOverlayBackgroundColor', $attributes );

	// If has background color.
	if ( $has_named_overlay_background_color || $has_picked_overlay_background_color ) {
		$colors['overlay_css_classes'][] = 'has-background';
	}

	if ( $has_named_overlay_background_color ) {
		$colors['overlay_css_classes'][] = sprintf( 'has-%s-background-color', gutenberg_experimental_to_kebab_case( $attributes['overlayBackgroundColor'] ) );
	} elseif ( $has_picked_overlay_background_color ) {
		$colors['overlay_inline_styles'] .= sprintf( 'background-color: %s;', $attributes['customOverlayBackgroundColor'] );
	}

	return $colors;
}

/**
 * Build an array with CSS classes and inline styles defining the font sizes
 * which will be applied to the pages markup in the front-end when it is a descendant of navigation.
 *
 * @param  array $context Navigation block context.
 * @return array Font size CSS classes and inline styles.
 */
function block_core_page_list_build_css_font_sizes( $context ) {
	// CSS classes.
	$font_sizes = array(
		'css_classes'   => array(),
		'inline_styles' => '',
	);

	$has_named_font_size  = array_key_exists( 'fontSize', $context );
	$has_custom_font_size = isset( $context['style']['typography']['fontSize'] );

	if ( $has_named_font_size ) {
		// Add the font size class.
		$font_sizes['css_classes'][] = sprintf( 'has-%s-font-size', $context['fontSize'] );
	} elseif ( $has_custom_font_size ) {
		// Add the custom font size inline style.
		$font_sizes['inline_styles'] = sprintf( 'font-size: %spx;', $context['style']['typography']['fontSize'] );
	}

	return $font_sizes;
}

/**
 * Outputs Page list markup from an array of pages with nested children.
 *
 * @param boolean $open_submenus_on_click Whether to open submenus on click instead of hover.
 * @param boolean $show_submenu_icons Whether to show submenu indicator icons.
 * @param boolean $is_navigation_child If block is a child of Navigation block.
 * @param array   $nested_pages The array of nested pages.
 * @param array   $active_page_ancestor_ids An array of ancestor ids for active page.
 * @param array   $colors Color information for overlay styles.
 * @param integer $depth The nesting depth.
 *
 * @return string List markup.
 */
function block_core_page_list_render_nested_page_list( $open_submenus_on_click, $show_submenu_icons, $is_navigation_child, $nested_pages, $active_page_ancestor_ids = array(), $colors = array(), $depth = 0 ) {
	if ( empty( $nested_pages ) ) {
		return;
	}
	$markup = '';
	foreach ( (array) $nested_pages as $page ) {
		$css_class       = $page['is_active'] ? ' current-menu-item' : '';
		$style_attribute = '';

		$css_class .= in_array( $page['page_id'], $active_page_ancestor_ids, true ) ? ' current-menu-ancestor' : '';
		if ( isset( $page['children'] ) ) {
			$css_class .= ' has-child';
		}

		if ( $is_navigation_child ) {
			$css_class .= ' wp-block-navigation-item';

			if ( $open_submenus_on_click ) {
				$css_class .= ' open-on-click';
			} elseif ( $show_submenu_icons ) {
				$css_class .= ' open-on-hover-click';
			}
		}

		$navigation_child_content_class = $is_navigation_child ? ' wp-block-navigation-item__content' : '';

		// If this is the first level of submenus, include the overlay colors.
		if ( 1 === $depth && isset( $colors['overlay_css_classes'], $colors['overlay_inline_styles'] ) ) {
			$css_class .= ' ' . trim( implode( ' ', $colors['overlay_css_classes'] ) );
			if ( '' !== $colors['overlay_inline_styles'] ) {
				$style_attribute = sprintf( ' style="%s"', esc_attr( $colors['overlay_inline_styles'] ) );
			}
		}

		$markup .= '<li class="wp-block-pages-list__item' . $css_class . '"' . $style_attribute . '>';

		if ( isset( $page['children'] ) && $is_navigation_child && $open_submenus_on_click ) {
			$markup .= '<button class="' . $navigation_child_content_class . ' wp-block-navigation-submenu__toggle" aria-expanded="false">' . wp_kses(
				$page['title'],
				wp_kses_allowed_html( 'post' )
			) . '<span class="wp-block-page-list__submenu-icon wp-block-navigation__submenu-icon"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" role="img" aria-hidden="true" focusable="false"><path d="M1.50002 4L6.00002 8L10.5 4" stroke-width="1.5"></path></svg></span>' .
			'</button>';
		} else {
			$markup .= '<a class="wp-block-pages-list__item__link' . $navigation_child_content_class . ' "href="' . esc_url( $page['link'] ) . '">' . wp_kses(
				$page['title'],
				wp_kses_allowed_html( 'post' )
			) . '</a>';
		}

		if ( isset( $page['children'] ) ) {
			if ( $is_navigation_child && $show_submenu_icons && ! $open_submenus_on_click ) {
				$markup .= '<button class="wp-block-navigation__submenu-icon wp-block-navigation-submenu__toggle" aria-expanded="false">';
				$markup .= '<span class="wp-block-page-list__submenu-icon wp-block-navigation__submenu-icon"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" role="img" aria-hidden="true" focusable="false"><path d="M1.50002 4L6.00002 8L10.5 4" stroke-width="1.5"></path></svg></span>';
				$markup .= '</button>';
			}
			$markup .= '<ul class="submenu-container';
			// Extra classname is added when the block is a child of Navigation.
			if ( $is_navigation_child ) {
				$markup .= ' wp-block-navigation__submenu-container';
			}
			$markup .= '">' . block_core_page_list_render_nested_page_list( $open_submenus_on_click, $show_submenu_icons, $is_navigation_child, $page['children'], $active_page_ancestor_ids, $colors, $depth + 1 ) . '</ul>';
		}
		$markup .= '</li>';
	}
	return $markup;
}

/**
 * Outputs nested array of pages
 *
 * @param array $current_level The level being iterated through.
 * @param array $children The children grouped by parent post ID.
 *
 * @return array The nested array of pages.
 */
function block_core_page_list_nest_pages( $current_level, $children ) {
	if ( empty( $current_level ) ) {
		return;
	}
	foreach ( (array) $current_level as $key => $current ) {
		if ( isset( $children[ $key ] ) ) {
			$current_level[ $key ]['children'] = block_core_page_list_nest_pages( $children[ $key ], $children );
		}
	}
	return $current_level;
}

/**
 * Determines if page should be classified as top-level or child page. Broken
 * out to this function to explain instead of a complex if statement.
 * When only_child_pages is true, force child pages to be considered top level.
 * Thy are top level since the parent is not shown (only child pages).
 *
 * @param int     $page_parent_id Parent id of the page being tested.
 * @param boolean $only_child_pages Only showing child pages.
 * @param int     $parent_id Top level parent id for child pages, needed so we can nest grand children.
 * @return boolean True if page should be considered a child page.
 */
function block_core_page_list_treat_as_child( $page_parent_id, $only_child_pages, $parent_id ) {
	if ( ! $page_parent_id ) {
		return false;
	}

	// Check only child pages, and id matches parent, then it is top level and
	// should not be treated like a child page.
	if ( $only_child_pages && ( $page_parent_id === $parent_id ) ) {
		return false;
	}

	return true;
}

/**
 * Renders the `core/page-list` block on server.
 *
 * @param array $attributes The block attributes.
 * @param array $content The saved content.
 * @param array $block The parsed block.
 *
 * @return string Returns the page list markup.
 */
function render_block_core_page_list( $attributes, $content, $block ) {
	global $post;
	static $block_id = 0;
	$block_id++;

	$only_child_pages = isset( $attributes['showOnlyChildPages'] ) && $attributes['showOnlyChildPages'];
	// The pages will be siblings (same parent) or set parent id equal to self if no children.
	$parent_id = ( $post->post_parent ) ? $post->post_parent : $post->ID;

	// TODO: When https://core.trac.wordpress.org/ticket/39037 REST API support for multiple orderby values is resolved,
	// update 'sort_column' to 'menu_order, post_title'. Sorting by both menu_order and post_title ensures a stable sort.
	// Otherwise with pages that have the same menu_order value, we can see different ordering depending on how DB
	// queries are constructed internally. For example we might see a different order when a limit is set to <499
	// versus >= 500.
	$query_args = array(
		'sort_column' => 'menu_order',
		'order'       => 'asc',
	);

	if ( $only_child_pages && $parent_id ) {
		$query_args['child_of'] = $parent_id;
	}

	$all_pages = get_pages( $query_args );

	// If thare are no pages, there is nothing to show.
	// Return early and empty to trigger EmptyResponsePlaceholder.
	if ( empty( $all_pages ) ) {
		return;
	}

	$top_level_pages = array();

	$pages_with_children = array();

	$active_page_ancestor_ids = array();

	foreach ( (array) $all_pages as $page ) {
		$is_active = ! empty( $page->ID ) && ( get_the_ID() === $page->ID );

		if ( $is_active ) {
			$active_page_ancestor_ids = get_post_ancestors( $page->ID );
		}

		// See function for logic when pages are treated like child pages.
		if ( block_core_page_list_treat_as_child( $page->post_parent, $only_child_pages, $parent_id ) ) {
			$pages_with_children[ $page->post_parent ][ $page->ID ] = array(
				'page_id'   => $page->ID,
				'title'     => $page->post_title,
				'link'      => get_permalink( $page->ID ),
				'is_active' => $is_active,
			);
		} else {
			$top_level_pages[ $page->ID ] = array(
				'page_id'   => $page->ID,
				'title'     => $page->post_title,
				'link'      => get_permalink( $page->ID ),
				'is_active' => $is_active,
			);

		}
	}

	$colors          = block_core_page_list_build_css_colors( $attributes, $block->context );
	$font_sizes      = block_core_page_list_build_css_font_sizes( $block->context );
	$classes         = array_merge(
		$colors['css_classes'],
		$font_sizes['css_classes']
	);
	$style_attribute = ( $colors['inline_styles'] . $font_sizes['inline_styles'] );
	$css_classes     = trim( implode( ' ', $classes ) );

	$nested_pages = block_core_page_list_nest_pages( $top_level_pages, $pages_with_children );

	$is_navigation_child = array_key_exists( 'isNavigationChild', $attributes ) ? $attributes['isNavigationChild'] : ! empty( $block->context );

	$open_submenus_on_click = array_key_exists( 'openSubmenusOnClick', $attributes ) ? $attributes['openSubmenusOnClick'] : false;

	$show_submenu_icons = array_key_exists( 'showSubmenuIcon', $attributes ) ? $attributes['showSubmenuIcon'] : false;

	$wrapper_markup = '<ul %1$s>%2$s</ul>';

	$items_markup = block_core_page_list_render_nested_page_list( $open_submenus_on_click, $show_submenu_icons, $is_navigation_child, $nested_pages, $active_page_ancestor_ids, $colors );

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => $css_classes,
			'style' => $style_attribute,
		)
	);

	return sprintf(
		$wrapper_markup,
		$wrapper_attributes,
		$items_markup
	);
}

	/**
	 * Registers the `core/pages` block on server.
	 */
function register_block_core_page_list() {
	register_block_type_from_metadata(
		__DIR__ . '/page-list',
		array(
			'render_callback' => 'render_block_core_page_list',
		)
	);
}
	add_action( 'init', 'register_block_core_page_list' );
