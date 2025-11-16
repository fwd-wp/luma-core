<?php

/**
 * Displays the footer widget area.
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */

if (
	is_active_sidebar('footer-1')
	|| is_active_sidebar('footer-2')
	|| is_active_sidebar('footer-3')
	|| is_active_sidebar('footer-4')
) : ?>
	<div class="footer-widgets">
		<div class="footer-grid">
			<?php for ($i = 1; $i <= 4; $i++) : ?>
				<?php if (is_active_sidebar('footer-' . $i)) : ?>
					<div class="footer-col">
						<?php dynamic_sidebar('footer-' . $i); ?>
					</div>
				<?php endif; ?>
			<?php endfor; ?>
		</div>
	</div>
<?php endif; ?>