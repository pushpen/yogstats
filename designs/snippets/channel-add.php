<section class="add">
	<h3>Add a channel</h2>
	<p>Add a channel here to have it's statistics be included in future reports.</p>
	<form id = 'createChannel' onsubmit="postForm(this);" method="POST">
		<label for="channel-name">YouTube Name:</label>
		<input type="text" name = "channelName" id="channel-name" />
		<div class="help-text"><strong>Without</strong> http://youtube.com/</div>
		<input type="button" onclick = "postFormButton(this);" value="Add channel" />
		<?php if($createError != '') echo '<p class="error-text">' . $createError . '</p>'; ?>
	</form>
</section>