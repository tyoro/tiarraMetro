<?php if( !empty($_GET['redirect']) ){ ?>
no login!<br/><hr/>
<?php } ?>
<div class="metro-pivot">
<div class='pivot-item'>
<h3 name="list">login</h3>
<form method="POST">
<input type="password" name="pass" /> 
<input type="submit" value="login" />
<label>cookie<input type="checkbox" value="true" name="cookie"></label>
</form>
</div>
</div>

