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
  function sendData(data, index, num)
  {
    xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
        }
    }
    xmlhttp.open("GET", "<?php echo get_url('plugin/gallery/update'); ?>?orders="+data+"&index="+index+"&gallery_number="+num, true);
    xmlhttp.send();
  }
  $(document).ready(function() {
<?php
    for($count = 1; $count <= $rows; $count++)
    {
?>
    $("#list<?php echo $count; ?>").sortable({
      revert: true,
      deactivate: function(event, ui) {
        var ary = new Array();
        var ary_index = 0;
	var gallery_number = $(this).parent().attr('class');
        var length = gallery_number.length;
        gallery_number = gallery_number.substring(7, length);
        for(var i = 1; i <= <?php echo $files[$count]->total_images; ?>; i++)
        {
          if(i !== $("#list<?php echo $count; ?> li").index($("#list<?php echo $count; ?> ."+i)))
          {
            ary[ary_index] = i;
            ary_index++;
            ary[ary_index] = $("#list<?php echo $count; ?> li").index($("#list<?php echo $count; ?> ."+i));
            ary_index++;
          }
        }
        for(var i = 1; i <= <?php echo $files[$count]->total_images; ?>; i++)
        {
            var child_index = i + 1;
            $("#list<?php echo $count; ?> li:nth-child("+child_index+")").attr("class", i);
        }
        sendData(ary, ary_index, gallery_number);
      }
    });
    $("#draggable<?php echo $count ;?>").draggable({
        connectToSortable: "#list<?php echo $count; ?>",
        revert: "invalid",
        cancel: "div.delete_icon"
    });
    $("list<?php echo $count; ?>").disableSelection();
<?php 
    }
?>
  });
</script>

<table style="width:auto"><tr><td>
<div class="galleries">
<?php for($j = 1; $j <= $rows; $j++) { ?>
<div class="<?php echo 'gallery' . $j; ?>" id="gallery">
<div class="title_bar">
<?php echo $files[$j]->gallery_title; ?>
<a href="<?php echo get_url('plugin/gallery/delete_gallery/' . $j); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete?'); ?>');" style="float:right;"><img class="icon" src="<?php echo ICONS_URI;?>delete-16.png" alt="<?php echo __('delete file icon'); ?>" title="<?php echo __('Delete slider'); ?>" /></a>
</div>
<ul id="list<?php echo $j; ?>" class="lists">
  <li id="draggable" style="padding:0px"></li>
<?php for($i = 1; $i <= $files[$j]->total_images; $i++) {  ?>
<li id="draggable<?php echo $j; ?>" class="<?php echo $i; ?>">
  <a class="<?php echo "image" . $j . '_' . $i; ?>" href="<?php echo $files[$j]->images[$i]->image_path; ?>" rel='lightbox[<?php echo $j; ?>]' alt="<?php echo $files[$j]->images[$i]->keyword; ?>" title="<?php echo $files[$j]->images[$i]->title; ?>"><img class="image" src="<?php echo $files[$j]->images[$i]->thumbnail_path; ?>" /></a>
<a href ="<?php echo get_url('plugin/gallery/delete/' . $files[$j]->images[$i]->image_path) . '/' . $j; ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete?'); ?>');"><img class="icon" src="<?php echo ICONS_URI;?>delete-16.png" alt="<?php echo __('delete file icon'); ?>" title="<?php echo __('Delete file'); ?>" /></a>
  <a href="#edit-file-popup" id="<?php echo "edit". $j . '_' . $i; ?>" class="popupLink"><img class="icon" src="<?php echo ICONS_URI; ?>rename-16.png" alt="<?php echo __('edit file icon'); ?>" title="<?php echo __('Edit file'); ?>" /></a>

<?php
if($files[$j]->images[$i]->rollover != '')
{
?>
<script>
$(".image<?php echo $j . '_' . $i; ?>").hover(
	function() {
		$(".image<?php echo $j . '_' . $i; ?> img").attr("src", "<?php echo $files[$j]->images[$i]->rollover_path; ?>");
	},
	function() {
		$(".image<?php echo $j . '_' . $i; ?> img").attr("src", "<?php echo $files[$j]->images[$i]->thumbnail_path; ?>");
	}
);
</script>
<?php
}
?>
<script>
$("#edit<?php echo $j . '_' .$i; ?>").click(
	function() {
		var index = <?php echo $i; ?>;
		<?php $n = $i; ?>
		while(index >= parseInt($(this).parent().attr('class')))
		{
			if(index == parseInt($(this).parent().attr('class')))
			{
				<?php $image_number = $n; ?>
				<?php $gallery_number = $j; ?>
			}
			index--;
		}
		$('input:hidden[name=gallery_number]').val('<?php echo $gallery_number; ?>');
		$('input:hidden[name=image_number]').val('<?php echo $image_number; ?>');
		$('input:text[name=title]').val('<?php echo $files[$gallery_number]->images[$image_number]->title; ?>');
		$('textarea[name=description]').val('<?php echo $files[$gallery_number]->images[$image_number]->description; ?>');
		$('input:text[name=keyword]').val('<?php echo $files[$gallery_number]->images[$image_number]->keyword; ?>');
		$('input:text[name=url]').val('<?php echo $files[$gallery_number]->images[$image_number]->image_path; ?>');
	}
);

</script>
</li>
<?php 
	}  
?>
<li id="draggable" style="padding:0px"></li>
</ul>
</div>
<?php 
}
?>
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
        *Image: <input type="file" name="image" /><br />
        *Thumbnail: <input type="file" name="thumbnail" /><br />
	RollOver: <input type="file" name="rollover" /><br />
	*Gallery: <br />
	<?php
        	for($c = 1; $c <= $rows; $c++) {
        ?>
            	<input type="radio" name="gallery_number" value="<?php echo $c; ?>" /> <?php echo $files[$c]->gallery_title; ?>
        <?php
                }
        ?>
        <br /><br /><input type="submit" value="Upload" />
      </form>
    </div>
  </div>
</div>

<div id="boxes">
	<div id="edit-file-popup" class="window">
		<div class="titlebar">
			<?php echo __('Edit file'); ?>
			<a href="#" class="close"><img src="<?php echo ICONS_URI; ?>delete-disabled-16.png" /></a>
		</div>
		<div class="content">
			<form method="post" action="<?php echo get_url('plugin/gallery/edit'); ?>">
				<input type="hidden" name="gallery_number" />
				<input type="hidden" name="image_number" value="somevalue"/>
				Title:<br /><input type="text" name="title" maxlength="50"size="50"/><br />
				Description:<br /><textarea rows="5" name="description" maxlength="150" ></textarea><br />
				Keyword:<br /><input type="text" name="keyword" size="50"/><br />
				URL:<br /><input type="text" name="url" size="50" /><br /><br />
				<input type="submit" value="Save" />
			</form>
		</div>
	</div>
</div>

<div id="boxes">
        <div id="create-gallery" class="window">
                <div class="titlebar">
                        <?php echo __('Create Gallery'); ?>
                        <a href="#" class="close"><img src="<?php echo ICONS_URI; ?>delete-disabled-16.png" /></a>
                </div>
                <div class="content">
                        <form method="post" action="<?php echo get_url('plugin/gallery/create'); ?>" enctype="multipart/form-data">
                                Gallery Title:<input type="text" name="gallery" /><br />
                                <input type="submit" value="Create" />
                        </form>
                </div>
        </div>
</div>

