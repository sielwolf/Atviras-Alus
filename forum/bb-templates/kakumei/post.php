<div class="recipe_comment" id="position-<?php post_position(); ?>">
	<div class="recipe_comment_avatar">
		<a href="/brewers/<?=get_post_author_id()?>">
			<?php post_author_avatar_link(); ?>
			<span><?php post_author_link(); ?></span>
		</a>
	</div>
	<div class="recipe_comment_content" style="width: 700px;">
		<div style="min-height: 40px;">
			<?php post_text(); ?>
		</div>
		<div class="recipe_comment_date">
			<?php printf( __('Posted %s ago'), bb_get_post_time() ); ?> <a href="<?php post_anchor_link(); ?>">#</a> <?php bb_post_admin(); ?>
		</div>
	</div>
	<div class="clear"></div>
</div>