<div id="user_info" style="display:block;">
	<?php
	$uid = (int)bb_get_current_user_info( 'id' );
	$sql = "SELECT * FROM mail_users WHERE user_id='".$uid."' AND mail_read='0'";
	$result = mysql_query($sql) or die(mysql_error());
	$msgs = mysql_num_rows($result);
	?>
	<div id="user_info_label">Prisijungęs:</div>
	<div id="user_info_name">
		<span><?=bb_get_current_user_info( 'name' )?></span>
		<a rel="nofollow" href="/mail/inbox" id="mail_counter"><?=$msgs;?></a>
	</div>
	<div class="clear"></div>
	<ul style="list-style-type:none;" id="user_info_submenu"> 
		<li><a href="/brewer/recipes" rel="nofollow">Receptai</a></li> 
		<li><a href="/brewer/favorites" rel="nofollow">Mėgstamiausi receptai</a></li> 
		<li><a href="/mail/inbox" rel="nofollow">Paštas</a></li> 
		<li><a href="/brew-session/brewer" rel="nofollow">Virimų istorija</a></li>
		<li><a href="/brewer/profile" rel="nofollow">Paskyra</a></li>
		<li><a href="/auth/logout" rel="nofollow">Atsijungti</a></li> 
	</ul>
</div>
