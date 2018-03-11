<?php
/*
Plugin Name: Simple coComments
Plugin URI: http://notizblog.org/projects/simple-cocomments/
Description: Adds coComments support to your wordpress blog.
Author: Matthias Pfefferle
Version: 0.1.1
Author URI: https://notiz.blog/
*/

function simplecoco($postid) {
?>
	<script type="text/javascript">
	// this ensures coComment gets the correct values
	coco = {
		tool: "WordPress",
		siteurl: "<?php echo get_option('home'); ?>",
		sitetitle: "<?php bloginfo('name'); ?>",
		pageurl: "<?php the_permalink() ?>",
		pagetitle: "<?php the_title(); ?>",
		formID: "commentform",
		textareaID: "comment",
		<?php if ( $user_ID ) : ?>
		author: "<?php echo $user_identity; ?>",
		<?php else : ?>
		authorID: "author",
		<?php endif; ?>
		buttonID	: "submit"
	}
	</script>
	<script id="cocomment-fetchlet" src="http://www.cocomment.com/js/enabler.js" type="text/javascript">
		// this activates coComment
	</script>
<?php
}

add_action('comment_form', 'simplecoco');
?>
