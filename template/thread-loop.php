<?php

global $user_ID, $wp_query, $et_sticky_pagename;
$thread 			= FE_Threads::convert($post);
$et_updated_date 	= et_the_time(strtotime($thread->et_updated_date));
$sticky 			= et_get_option('et_sticky_threads');
$user_authorize_to_view = wp_parse_args(array(),get_option( 'authorize_to_view'));

$term_id = !empty($thread->category[0]) ? $thread->category[0]->term_id : '';
$check_authorize = in_array($term_id, $user_authorize_to_view);
// Allow to view 
if($check_authorize || is_user_logged_in() || !get_option('user_view', false)){
	$authorize = true;	
}else{
	$authorize = false;	
}

?>
	<li class="<?php echo et_is_highlight($thread->ID); ?> thread-item <?php //echo in_array($post->ID, $sticky) ? 'sticky' : '' ?>" data-id="<?php echo $post->ID ?>" data-cat="<?php echo $thread->category[0]->slug ?>">
		<?php do_action('forumengine_before_thread_item', $thread) ?>
		<?php if(!is_author() && !is_page_template( 'page-member.php' )) {?>
		<?php if($authorize){?>
			<a href="<?php the_permalink() ?>">
		<?php }else{ ?>
			<a href="#modal_login" id="open_login" data-toggle="modal" data-url="<?php the_permalink() ?>">
		<?php }?>
				<span class="thumb avatar">
					<?php echo et_get_avatar($post->post_author);?>
					<?php do_action( 'fe_user_badge', $post->post_author ); ?>
				</span>
		</a>
		<?php } ?>
		<div class="f-floatright">
			<?php do_action('forumengine_before_thread_item_infomation', $thread) ?>
			<h2 class="title">
				<?php if($authorize){?>
					<a href="<?php the_permalink() ?>">
				<?php }else{ ?>
					<a href="#modal_login" id="open_login" data-toggle="modal" data-url="<?php the_permalink() ?>">
				<?php }?>
						<?php the_title() ?>
						<?php if ( $post->post_status == 'closed' ) { echo '<span class="icon" data-icon="("></span>'; } ?>
					</a>
			</h2>
			<div class="post-information">
				<span class="times-create"><?php printf( __( 'Updated %s in', ET_DOMAIN ),$et_updated_date); ?></span>
				<span class="type-category">
					<?php
					if ( !empty($thread->category[0]) )
						$color = FE_ThreadCategory::get_category_color($thread->category[0]->term_id);
					else
						$color = 0;
					?>
					<a href="<?php if($thread->category){ echo get_term_link( $thread->category[0]->slug, 'category' );}else{echo '#';} ?>">
						<span class="flags color-<?php echo $color ?>"></span>
						<?php
						if($thread->category) {
							echo $thread->category[0]->name;
						} else {
							_e('No category', ET_DOMAIN);
						}
						?>
					</a>.
				</span>
				<span class="author">
				<a href="<?php the_permalink() ?>#comments" class="et-entry-comments icon" data-icon="q"><?php echo get_comments_number() ?></a>
				</span>
				<span class="user-action">
					<span class="comment <?php if($thread->replied) echo 'active';?>"><span class="icon" data-icon="w"></span><?php echo bac_PostViews($thread->ID); ?></span>
				</span>
				<span class="undo-action hide">
					<?php printf( __('Want to %s ?',ET_DOMAIN) , '<a href="#" class="act-undo">' . __('undo', ET_DOMAIN) . '</a>' ); ?>
				</span>
			</div>
			<?php if(current_user_can("manage_threads")) {?>
			<div class="control-thread-group">
				<?php if ( $thread->post_status == 'pending' ){ ?>
					<a href="#" data="<?php echo $thread->ID; ?>" class="approve-thread" data-toggle="tooltip" title="<?php _e('Approve', ET_DOMAIN) ?>"><span class="icon" data-icon="3"></span></a>
					<a href="#" data="<?php echo $thread->ID; ?>" class="delete-thread" data-toggle="tooltip" title="<?php _e('Delete', ET_DOMAIN) ?>"><span class="icon" data-icon="#"></span></a>
				<?php } else {  ?>
					<a href="#" data-toggle="tooltip" title="<?php _e('Sticky', ET_DOMAIN) ?>" class="sticky-thread <?php if ( et_is_sticky_thread($thread->ID) ) echo 'active' ?>">
						<span class="icon" data-icon="S"></span>
					</a>
					<a href="#" data-toggle="tooltip" title="<?php _e('Sticky Home', ET_DOMAIN) ?>" class="sticky-thread-home <?php if ( !et_is_sticky_thread($thread->ID) ) echo 'collapse'; ?> <?php if ( et_is_sticky_thread($thread->ID, true) ) echo 'active'; ?>">
						<span class="icon" data-icon="G"></span>
					</a>
					<a href="#" class="close-thread <?php if ( $thread->post_status == 'closed' ) echo 'collapse' ?>" data-toggle="tooltip" title="<?php _e('Close', ET_DOMAIN) ?>"><span class="icon" data-icon="("></span></a>
					<a href="#" class="unclose-thread <?php if ( $thread->post_status != 'closed' ) echo 'collapse' ?>" data-toggle="tooltip" title="<?php _e('Unclose', ET_DOMAIN) ?>"><span class="icon" data-icon=")"></span></a>
					<a href="#" class="delete-thread" data-toggle="tooltip" title="<?php _e('Delete', ET_DOMAIN) ?>"><span class="icon" data-icon="#"></span></a>
				<?php } ?>
			</div>
			<?php } ?>
			<?php do_action('forumengine_after_thread_item_infomation', $thread) ?>
		</div>
		<?php do_action('forumengine_after_thread_item', $thread) ?>

		<?php if ( (et_is_sticky_thread($post->ID, true) && $et_sticky_pagename == 'home') || (et_is_sticky_thread($post->ID) && $et_sticky_pagename == 'category') ){
				echo '<div class="sticky-bar color-' . $color . '"></div>';
		} ?>
	</li>