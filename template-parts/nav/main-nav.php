<?php
/**
 * Header and main navigation.
 *
 * Items come from the WordPress menu location `primary` ("Main").
 * A top-level item with children opens the existing desktop panel and mobile accordion.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

$starterkit_menu_item_state = static function ( $item ) {
	$classes  = array_filter( (array) ( $item->classes ?? array() ) );
	$current  = array( 'current-menu-item', 'current_page_item' );
	$ancestor = array( 'current-menu-ancestor', 'current-menu-parent', 'current_page_ancestor', 'current_page_parent' );

	if ( array_intersect( $current, $classes ) ) {
		return 'current_page_item';
	}

	if ( array_intersect( $ancestor, $classes ) ) {
		return 'current_page_ancestor';
	}

	return '';
};

$starterkit_menu_link_attrs = static function ( $url, $target = '' ) {
	$attrs = ' href="' . esc_url( $url ) . '"';

	if ( is_string( $target ) && '' !== $target && '_self' !== $target ) {
		$attrs .= ' target="' . esc_attr( $target ) . '" rel="noopener noreferrer"';
	}

	return $attrs;
};

$starterkit_menu_by_parent = array();
$starterkit_menu_locations = get_nav_menu_locations();

if ( ! empty( $starterkit_menu_locations['primary'] ) ) {
	$starterkit_menu_raw = wp_get_nav_menu_items( (int) $starterkit_menu_locations['primary'] );

	if ( is_array( $starterkit_menu_raw ) ) {
		_wp_menu_item_classes_by_context( $starterkit_menu_raw );

		foreach ( $starterkit_menu_raw as $starterkit_menu_raw_item ) {
			$starterkit_menu_by_parent[ (int) $starterkit_menu_raw_item->menu_item_parent ][] = $starterkit_menu_raw_item;
		}
	}
}

$starterkit_menu_node = static function ( $item ) use ( &$starterkit_menu_node, $starterkit_menu_by_parent, $starterkit_menu_item_state ) {
	$children = array();

	foreach ( $starterkit_menu_by_parent[ (int) $item->ID ] ?? array() as $child ) {
		$children[] = $starterkit_menu_node( $child );
	}

	return array(
		'id'       => (int) $item->ID,
		'title'    => trim( wp_strip_all_tags( (string) $item->title ) ),
		'url'      => (string) $item->url,
		'target'   => (string) $item->target,
		'state'    => $starterkit_menu_item_state( $item ),
		'children' => $children,
	);
};

$starterkit_menu_link_block = static function ( $node ) {
	return array(
		'type'   => 'link',
		'url'    => $node['url'],
		'target' => $node['target'],
		'label'  => $node['title'],
		'state'  => $node['state'],
	);
};

$starterkit_menu_items = array();

foreach ( $starterkit_menu_by_parent[0] ?? array() as $starterkit_top_item ) {
	$node = $starterkit_menu_node( $starterkit_top_item );

	if ( '' === $node['title'] ) {
		continue;
	}

	$columns    = array();
	$loose_links = array();

	foreach ( $node['children'] as $child ) {
		if ( '' === $child['title'] ) {
			continue;
		}

		if ( empty( $child['children'] ) ) {
			if ( '' === $child['url'] ) {
				continue;
			}

			$loose_links[] = $starterkit_menu_link_block( $child );
			continue;
		}

		$blocks = array(
			array(
				'type'  => 'h2',
				'text'  => $child['title'],
				'state' => $child['state'],
			),
		);

		foreach ( $child['children'] as $grandchild ) {
			if ( '' === $grandchild['title'] ) {
				continue;
			}

			if ( ! empty( $grandchild['children'] ) ) {
				$blocks[] = array(
					'type'  => 'h3',
					'text'  => $grandchild['title'],
					'state' => $grandchild['state'],
				);

				foreach ( $grandchild['children'] as $great_grandchild ) {
					if ( '' === $great_grandchild['title'] || '' === $great_grandchild['url'] ) {
						continue;
					}

					$blocks[] = $starterkit_menu_link_block( $great_grandchild );
				}

				continue;
			}

			if ( '' === $grandchild['url'] ) {
				continue;
			}

			$blocks[] = $starterkit_menu_link_block( $grandchild );
		}

		$columns[] = $blocks;
	}

	if ( ! empty( $loose_links ) ) {
		array_unshift( $columns, $loose_links );
	}

	$starterkit_menu_items[] = array(
		'key'          => $node['id'],
		'title'        => $node['title'],
		'url'          => $node['url'],
		'target'       => $node['target'],
		'columns'      => $columns,
		'has_panel'    => ! empty( $columns ),
		'active_state' => $node['state'],
	);
}
?>

<header class="header">
	<div class="container-fluid">
		<div class="row header-content">
			<div class="col-12 header-main">
				<div class="header-logo">
					<?php if ( ! is_front_page() ) : ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Zur Startseite', 'starterkit' ); ?>">
					<?php endif; ?>
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.svg" alt="Logo der AWO Vorpommern">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-small.svg" alt="Logo der AWO">
					<?php if ( ! is_front_page() ) : ?>
						</a>
					<?php endif; ?>
				</div>

				<div class="header-nav-parent">
					<ul class="header-nav-list">
						<?php foreach ( $starterkit_menu_items as $menu_item ) : ?>
							<?php
							$item_classes = array(
								'page-item-' . (int) $menu_item['key'],
							);
							if ( $menu_item['has_panel'] ) {
								$item_classes[] = 'page_item_has_children';
							}
							if ( '' !== $menu_item['active_state'] ) {
								$item_classes[] = $menu_item['active_state'];
							}
							?>
							<li class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
								<?php if ( $menu_item['has_panel'] ) : ?>
									<a class="header-nav-trigger" id="header-nav-trigger-<?php echo (int) $menu_item['key']; ?>"<?php echo $starterkit_menu_link_attrs( $menu_item['url'], $menu_item['target'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-expanded="false" aria-controls="header-nav-summary-<?php echo (int) $menu_item['key']; ?>">
										<?php echo esc_html( $menu_item['title'] ); ?>
									</a>
								<?php else : ?>
									<a class="header-nav-trigger"<?php echo $starterkit_menu_link_attrs( $menu_item['url'], $menu_item['target'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										<?php echo esc_html( $menu_item['title'] ); ?>
									</a>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
						<li>
							<a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" class="header-search-link" aria-label="<?php esc_attr_e( 'Suche', 'starterkit' ); ?>">
								<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/svg/24/24-zoom.svg" alt="" aria-hidden="true" width="24" height="24">
							</a>
						</li>
					</ul>
				</div>

				<button type="button" class="header-burger" aria-label="<?php esc_attr_e( 'Open menu', 'starterkit' ); ?>" aria-expanded="false" aria-controls="header-nav-mobile">
					<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/svg/32/32-menu-3.svg" alt="" class="header-burger-icon header-burger-icon-closed" width="24" height="24">
					<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/svg/32/32-xmark.svg" alt="" class="header-burger-icon header-burger-icon-opened" width="24" height="24">
				</button>
			</div>

			<div class="col-12 header-nav-children">
				<div class="header-nav-children-content scrollbar">
					<div class="row">
						<div class="col-12 header-nav-summaries">
							<?php foreach ( $starterkit_menu_items as $menu_item ) : ?>
								<?php if ( ! $menu_item['has_panel'] ) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<div id="header-nav-summary-<?php echo (int) $menu_item['key']; ?>" class="row header-nav-summary page-item-<?php echo (int) $menu_item['key']; ?>" aria-hidden="true" aria-labelledby="header-nav-trigger-<?php echo (int) $menu_item['key']; ?>">
									<button type="button" class="header-nav-summary-toggle" aria-label="<?php echo esc_attr( sprintf( __( 'Untermenü von %s schließen', 'starterkit' ), $menu_item['title'] ) ); ?>">
										<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/mobile-menu-open.svg" alt="" aria-hidden="true" class="header-nav-summary-toggle-icon header-nav-summary-toggle-icon-open" width="24" height="24">
									</button>
									<div class="col-12 header-nav-entries-content">
										<div class="row g-4">
											<?php foreach ( $menu_item['columns'] as $column ) : ?>
												<div class="col-12 col-md-6 col-lg header-nav-entry-column">
													<div class="header-nav-entry-stack">
														<?php foreach ( $column as $block ) : ?>
															<?php if ( 'h2' === $block['type'] ) : ?>
																<h2 class="header-nav-entry-heading h5"><?php echo esc_html( $block['text'] ); ?></h2>
															<?php elseif ( 'h3' === $block['type'] ) : ?>
																<h3 class="header-nav-entry-subheading h6"><?php echo esc_html( $block['text'] ); ?></h3>
															<?php elseif ( 'link' === $block['type'] ) : ?>
																<a class="header-nav-entry-link<?php echo '' !== $block['state'] ? ' ' . esc_attr( $block['state'] ) : ''; ?>"<?php echo $starterkit_menu_link_attrs( $block['url'], $block['target'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $block['label'] ); ?></a>
															<?php endif; ?>
														<?php endforeach; ?>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>

			<nav id="header-nav-mobile" class="col-12 header-nav-mobile" aria-hidden="true">
				<ul class="header-nav-mobile-list scrollbar">
					<?php foreach ( $starterkit_menu_items as $menu_item ) : ?>
						<li class="header-nav-mobile-item<?php echo $menu_item['has_panel'] ? ' has-children' : ''; ?><?php echo '' !== $menu_item['active_state'] ? ' ' . esc_attr( $menu_item['active_state'] ) : ''; ?>">
							<?php if ( $menu_item['has_panel'] ) : ?>
								<button type="button" class="header-nav-mobile-trigger h5" aria-expanded="false" aria-controls="mobile-children-<?php echo (int) $menu_item['key']; ?>">
									<?php echo esc_html( $menu_item['title'] ); ?>
								</button>
								<ul id="mobile-children-<?php echo (int) $menu_item['key']; ?>" class="header-nav-mobile-children" hidden>
									<?php foreach ( $menu_item['columns'] as $column_index => $column ) : ?>
										<li class="header-nav-mobile-item">
											<ul class="header-nav-mobile-children header-nav-mobile-children--nested">
												<?php
												$section_index = 0;
												$section_open  = false;

												foreach ( $column as $block ) :
													if ( 'h2' === $block['type'] ) :
														if ( $section_open ) :
															?>
															</ul>
														</li>
															<?php
															$section_open = false;
														endif;

														$section_index++;
														$section_id = 'mobile-section-' . (int) $menu_item['key'] . '-' . (int) $column_index . '-' . (int) $section_index;
														?>
														<li class="header-nav-mobile-item has-children<?php echo '' !== $block['state'] ? ' ' . esc_attr( $block['state'] ) : ''; ?>">
															<button type="button" class="header-nav-mobile-trigger h6 header-nav-mobile-heading" aria-expanded="false" aria-controls="<?php echo esc_attr( $section_id ); ?>">
																<?php echo esc_html( $block['text'] ); ?>
															</button>
															<ul id="<?php echo esc_attr( $section_id ); ?>" class="header-nav-mobile-children" hidden>
														<?php
														$section_open = true;
														continue;
													endif;

													$block_item_class = '' !== $block['state'] ? ' class="' . esc_attr( $block['state'] ) . '"' : '';
													?>
													<li<?php echo $block_item_class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
														<?php if ( 'h3' === $block['type'] ) : ?>
															<h4 class="header-nav-mobile-subheading"><?php echo esc_html( $block['text'] ); ?></h4>
														<?php elseif ( 'link' === $block['type'] ) : ?>
															<a<?php echo $starterkit_menu_link_attrs( $block['url'], $block['target'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $block['label'] ); ?></a>
														<?php endif; ?>
													</li>
													<?php
												endforeach;

												if ( $section_open ) :
													?>
													</ul>
												</li>
													<?php
												endif;
												?>
											</ul>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php else : ?>
								<a class="h5 header-nav-mobile-title"<?php echo $starterkit_menu_link_attrs( $menu_item['url'], $menu_item['target'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $menu_item['title'] ); ?></a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>
	</div>
</header>
