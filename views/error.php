<?php
if (!defined('IN_CMS')) { exit(); }
?>
<h1>Error</h1>
<p>
<?php
  echo $msg;
?>
</p>
<a href="<?php echo get_url('plugin/secure_upload/index'); ?>">back</a>
