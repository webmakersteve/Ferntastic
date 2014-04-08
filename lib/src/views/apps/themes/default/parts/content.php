<div class="modal"></div>
<noscript>

    <style type="text/css">.site-inner-wrapper, {display: none;}</style>
    <!-- NO JS -->
</noscript>
<div class="wrapper">
	
    <div class="site-inner-wrapper">

        <header>

            <?php the_header(); ?>

        </header>

        <div id="body">

            <div class="loading message"<?=(Fn()->sessions->is_error()) ? ' style="display: block;"':''?>><?php Fn()->sessions->echo_error();?></div>
			<?php if(Fn()->sessions->is_error()): ?>
            <script type="text/javascript">
			$('.loading').delay(3000).fadeOut(500, null, function() {
				$(this).hide().val('');
			});
			</script>
            <?php endif; ?>
            <div class="gmail-style-sidebar">
				<?php
				Fn()->load_extension('fquery');
				$acc=Fn()->account;
				
				?><h2><?=function_exists('current_app') ? current_app()->row('name') : '<span class="ferns">91ferns</span> Account Manager'?></h2>
                
                	<div class="container">
						<? create_menu_by_user(); ?>
	                </div>
					
                    <div id="app-container" class="container">
                    
                    	<h2>My Apps</h2>
                    
                    	<ul class="app-menu" style="display: block;">
                        	<li><a href="/account">91ferns Account Manager</a></li>
					<?php
					$GLOBALS['listed_apps'] = array(0);
					
                    if (Fn()->account->sitesFQ->count > 1) {
						Fn()->account->sitesFQ->each(function($d)  use ($acc_arr) {
							$acc_arr = Fn()->account->apps;
							$apps = fQuery('apps', "id[x&=?]:order('ASC'),site[x=?],*", $acc_arr,$d->row('id'));
							?><li<?=$apps->count>0 ? ' class="site has_more"' : ' style="display:none;"' ?>><?=$d->row('name'); ?></li><?php
                            if ($apps->count>0) {
								?>
									<?php $apps->each(function($dd) {
										global $listed_apps;
										$listed_apps[] = $dd->row('id');
										?><li class="app-of-site"><?php if (!function_exists('current_app') or current_app()->row('name') != $dd->row('name')): ?><a href="/apps/<?=$dd->row('permalink')?>" data-id="<?=$dd->row('id')?>"><?=$dd->row('name');?></a><?php else: ?><span class="ferns"><?=$dd->row('name');?></span><?php endif; ?></li><?php
									}); 
							}
						}); 
					
					} elseif ($sites->count == 1) {
						$d = $sites->this();
						$apps = fQuery('apps', "id[x&=?]:order('ASC'),site[x=?],*", explode(',', $access_to), $d->row('id'));
						
						$apps->each(function($dd) {
							global $listed_apps;
							$listed_apps[] = $dd->row('id');
							?><li><?php if (!function_exists('current_app') or current_app()->row('name') != $dd->row('name')): ?><a href="/apps/<?=$dd->row('permalink')?>" data-id="<?=$dd->row('id')?>"><?=$dd->row('name');?></a><?php else: ?><span class="ferns"><?=$dd->row('name');?></span><?php endif; ?></li><?php
						});
					}
					//Now load the apps that have not yet been loaded. check the listed_apps array;
					if (count($GLOBALS['listed_apps']) > 0) {
						$sql = sprintf("SELECT * FROM apps WHERE id NOT IN (%s) AND id IN (%s) ORDER BY id ASC", implode(",", $GLOBALS['listed_apps']), implode(",", Fn()->account->apps));
					} else $sql = sprintf("SELECT * FROM apps WHERE id IN (%s) ORDER BY id ASC", implode(',', Fn()->account->apps));
	
					query( $sql );
					
					if (num_rows() > 0) {
						
					while ($row = assoc()):
					if ($row['id'] == 6) break;
					?><li class="uncategorized"><?php if (!function_exists('current_app') or current_app()->row('name') != $row['name']): ?><a href="/apps/<?=$row['permalink']?>" data-id="<?=$row['id']?>"><?=$row['name'];?></a><?php else: ?><span class="ferns"><?=$row['name'];?></span><?php endif; ?></li><?php
					endwhile;
					
					}
					?>
                        
                    </ul>

					</div>

              </div>

              <div class="mc-container">
                  <div class="main-content">
                  	<?php call_action('header'); ?>
                  	<?php call_action( 'before_content' ); ?>
				  	<?php call_action( 'content' ); ?>
                    <?php call_action( 'after_content' ); ?>
                  </div>
              </div><!-- .mc-container -->
             
			
        </div> <!-- #body -->

    </div> <!--.site-inner-wrapper-->
    <div class="push"></div><!-- .push -->

</div> <!-- .wrapper -->