<?php
/*
 * Wolf CMS - Content Management Simplified. <http://www.wolfcms.org>
 * Copyright (C) 2008-2010 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of Wolf CMS. Wolf CMS is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * The skeleton plugin serves as a basic plugin template.
 *
 * This skeleton plugin makes use/provides the following features:
 * - A controller without a tab
 * - Three views (sidebar, documentation and settings)
 * - A documentation page
 * - A sidebar
 * - A settings page (that does nothing except display some text)
 * - Code that gets run when the plugin is enabled (enable.php)
 *
 * Note: to use the settings and documentation pages, you will first need to enable
 * the plugin!
 *
 * @package Plugins
 * @subpackage skeleton
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2008
 * @license http://www.gnu.org/licenses/gpl.html GPLv3 license
 */
?>
<script src="<?php echo URL_PUBLIC ?>public/lightbox/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo URL_PUBLIC ?>public/lightbox/js/jquery-ui-1.8.18.custom.min.js"></script>
<script src="<?php echo URL_PUBLIC ?>public/lightbox/js/jquery.smooth-scroll.min.js"></script>
<script src="<?php echo URL_PUBLIC ?>public/lightbox/js/lightbox.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<link href="<?php echo URL_PUBLIC ?>public/lightbox/css/lightbox.css" rel="stylesheet" />

<h1><?php echo __('Gallery'); ?></h1>

<script>
  function sendData(data, index)
  {
    xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET", "<?php echo get_url('plugin/gallery/update'); ?>?orders="+data+"&index="+index, true);
    xmlhttp.send();
  }
  $(document).ready(function() {
    $("#list").sortable({
      revert: true,
      deactivate: function(event, ui) {
        var ary = new Array();
        var ary_index = 0;
        for(var i = 1; i <= <?php echo $rows; ?>; i++)
        {
          if(i !== $("#list li").index($("."+i)))
          {
            ary[ary_index] = i;
            ary_index++;
            ary[ary_index] = $("#list li").index($("."+i));
            ary_index++;
          }
        }
        for(var i = 1; i <= <?php echo $rows; ?>; i++)
        {
            var child_index = i+1;
            $("#list li:nth-child("+child_index+")").attr("class", i);
        }
        sendData(ary, ary_index);
      }
    });
    $("#draggable").draggable({
        connectToSortable: "#list",
        revert: "invalid",
        cancel: "div.delete_icon"
    });
    $("ul, li").disableSelection();
  });
</script>

<table style="width:auto"><tr><td>
<div class="gallery">
<ul id="list">
  <li id="draggable" style="padding:0px"></li>
<?php for($i = 1; $i <= $rows; $i++) {  ?>
<li id="draggable" class="<?php echo $i; ?>">
  <a class="<?php echo "image" . $i; ?>" href="<?php echo $files[$i]->image_path; ?>" rel='lightbox[image]'><img class="image" src="<?php echo $files[$i]->thumbnail_path; ?>" /></a>
<?php
if($files[$i]->rollover != '')
{
?>
<script>
$(".image<?php echo $i; ?>").hover(
	function() {
		$(".image<?php echo $i; ?> img").attr("src", "<?php echo $files[$i]->rollover_path; ?>");
	},
	function() {
		$(".image<?php echo $i; ?> img").attr("src", "<?php echo $files[$i]->thumbnail_path; ?>");
	}
);
</script>
<?php
}
?>
  <a href ="<?php echo get_url('plugin/gallery/delete/' . $files[$i]->image_path); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete?'); ?>');"><img class="delete_icon" src="<?php echo ICONS_URI;?>delete-16.png" alt="<?php echo __('delete file icon'); ?>" title="<?php echo __('Delete file'); ?>" /></a>
</li>
<?php }  ?>
  <li id="draggable" style="padding:0px"></li>
</ul>
</div>
</td></tr></table>

<div id="boxes">                                                                                                                                  
  <div id="upload-file-popup" class="window">
    <div class="titlebar">
      <?php echo __('Upload file'); ?>
      <a href="#" class="close"><img src="<?php echo ICONS_URI;?>delete-disabled-16.png"/></a>
    </div>
    <div class="content">
      <form method="post" action="<?php echo get_url('plugin/gallery/upload'); ?>" enctype="multipart/form-data">
      <input type="hidden" name="path" value="<?php echo ($dir == '') ? '/': $dir; ?>"/>
      <input type="checkbox" name="use_rollover" value="1" />Use Rollover Image<br />
        Image: <input type="file" name="image" /><br />
        Thumbnail: <input type="file" name="thumbnail" /><br />
	RollOver: <input type="file" name="rollover" /><br />
        <input type="submit" value="Upload" />
      </form>
    </div>
  </div>
</div>

