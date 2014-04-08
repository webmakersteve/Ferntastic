<?php echo $scriptError; ?><div id="full-wrapper">

<div class="head">
    <h1><?=$res->login_title?></h1>
</div>
    <p>
    <?=$res->login_intro?>
    </p>

    <div class="login-aside-new">
    <form action="/auth" method="post">
        <h2><?=$res->login_form_title?></h2>
        <?php if (is_array($error) and isset($error['msg'])) echo '<div id="form-error">'.$error['msg'].'</div>'; ?>
        <div class="input-div username">
            <?php if ($prelog): ?>
            <label><strong<?php if (is_array($error) and $error['field'] == "email") echo ' class="formerror"';?>><?=$res->login_email_field?></strong></label>
            <div class="alt-text"><?=$username?></div>
            <input type="hidden" value="<?=$username?>" name="Email">
            <?php else: ?>
            <label for="Email">
                <strong<?php if (is_array($error) and $error['field'] == "email") echo ' class="formerror"';?>><?=$res->login_email_field?></strong>
            </label>
            <input id="Email" autocomplete="off" spellcheck="false" type="email" value="<?=(isset($oldpost['Email']))?$oldpost['Email']:""?>" name="Email" spellcheck="false"<?php if (is_array($error) and $error['field'] == "email") echo ' class="formerror"';?>>
            <?php endif; ?>
        </div>
        
        <div class="input-div password">
            <label for="Password">
                <strong<?php if (is_array($error) and $error['field'] == "password") echo ' class="formerror"';?>><?=$res->login_password_field?></strong>
            </label>
            <input id="Password" type="password" value="" name="Password" spellcheck="false" autocomplete="false"<?php if (is_array($error) and $error['field'] == "password") echo ' class="formerror"';?>>
        </div>
        <input type="hidden" value="login" name="op">
        <input type="hidden" name="continue" value="<?=isset($_GET['continue']) ? $_GET['continue'] : '/account.php'?>">
        <div class="form-row">

            <label class="checkbox-single" for="Remember">
                <input id="Remember" name="Remember_me" type="checkbox" value="1">
                <strong class="checkbox-label"><?=$res->login_remember_field?></strong>
            </label>
    
        </div>
        
        <div class="form-row">
            <input type="submit" value="Log in" class="ferns-submit rectangle-button green">
        </div>
        
        <div class="form-row">
            <a href="#"><?=$res->login_noremember_label?></a>
        </div>
        <?php if (isset($prelog) and $prelog): ?><div class="form-row"><a href="/?unsave"><?=$res->login_diffuser_label?></a></div><?php endif; ?>
    </form>
    </div> <!-- .aside -->
    
</div> <!-- .article -->