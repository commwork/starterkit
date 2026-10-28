<?php
function starterkit_acf_admin_head() {
	?>
	<style type="text/css">
		/* subheadline */
		.acf-field[data-name="subheadline"] .acf-input-wrap input[type=text] {
			color: #e0001b;
			font-weight: 700;
			text-transform: uppercase;
		}

		/* Headline */
		.acf-field[data-name="headline"] .acf-input-wrap input[type=text] {
			font-size: 1.25rem;
			font-weight: 700;
		}

		/* Chronicle */
		.acf-field[data-name="title_year"] .acf-input-wrap input[type=text] {
			font-size: 5rem;
			font-weight: 700;
			font-family: ui-monospace, "Cascadia Code", "SF Mono", Monaco, "Liberation Mono", Consolas, monospace;
		}
	</style>
	<?php
	}
add_action('acf/input/admin_head', 'starterkit_acf_admin_head');
?>