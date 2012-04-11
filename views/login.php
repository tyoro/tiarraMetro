<div class="metro-pivot">
	<div class="pivot-item" name="login">
		<h3>login</h3>
<?php if(!empty($_GET['redirect'])): ?>
no login!<br/><hr/>
<?php endif ?>
<?php if($user->flashEnabled('authMessage')): ?>
<p class="error auth-message"><?php echo $user->flashMessage('authMessage'); ?></p>
<?php endif; ?>
		<form method="POST" id='login_form' name='login'>
			<input type="password" id='login_pass' name="pass" placeholder="input your password" /> 
			<input type="submit" id='login_button' name='login' value="login" />
			<input type="checkbox" id='cookie' name="cookie" value="true" checked='checked' /><label for="cookie">cookie</label>
		</form>
	</div>
</div>
