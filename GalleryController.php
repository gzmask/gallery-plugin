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

/**
 * Use this SkeletonController and this skeleton plugin as the basis for your
 * new plugins if you want.
 */
class GalleryController extends PluginController {

    public function __construct() {
        $this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('../../plugins/gallery/views/sidebar'));
    }

    public function index() {
        $this->lightbox_display();
    }

    private function lightbox_display() {
          require_once(CMS_ROOT . DS . 'config.php');

          $imageslist = array();
	  $galleries = array();
          $db = mysql_connect('localhost', DB_USER, DB_PASS);
          if($db) {
            mysql_select_db('gallery', $db);
            $select_sql = 'select * from galleries where 1';
            $result = mysql_query($select_sql) or Flash::setNow('error', __('Cannot select from database'));
            $num_rows = mysql_num_rows($result);
            
            for($curr = 1; $curr <= $num_rows; $curr++) {
		$gallery_order = mysql_result($result, $curr-1, 'gallery_number');
		$total_images = mysql_result($result, $curr-1, 'total_images');
		$gallery_title = mysql_result($result, $curr-1, 'title');
	      	$gallery = new stdClass;
		$gallery->order = $gallery_order;
		$gallery->total_images = $total_images;
		$gallery->gallery_title = $gallery_title;
              	$select_sql = 'select * from image_order where g_id = ' . $curr;
              	$images = mysql_query($select_sql) or Flash::setNow('error', __('Cannot select from database'));
	      	for($i = 1; $i <= $total_images; $i++) {
              		$image = mysql_result($images, $i-1, 'image_name');
              		$thumbnail = mysql_result($images, $i-1, 'thumbnail_name');
              		$rollover = mysql_result($images, $i-1, 'rollover_name');
	      		$order = mysql_result($images, $i-1, 'order_number');
	      		$title = mysql_result($images, $i-1, 'title');
	      		$description = mysql_result($images, $i-1, 'description');
	      		$keyword = mysql_result($images, $i-1, 'keyword');
              		$image_path = URL_PUBLIC . 'public/gallery/' . $image;
              		$thumbnail_path = URL_PUBLIC . 'public/gallery/thumbnails/' . $thumbnail;
	      		$rollover_path = URL_PUBLIC . 'public/gallery/rollovers/' . $rollover;
	      		$object = new stdClass;
              		$object->order = $order;
              		$object->image_path = $image_path;
              		$object->thumbnail_path = $thumbnail_path;
	      		$object->rollover_path = $rollover_path;
	      		$object->rollover = $rollover;
	      		$object->title = $title;
	      		$object->description = $description;
	      		$object->keyword = $keyword;
              		$imageslist[$object->order] = $object;
	      	}
		$gallery->images = $imageslist;
	      	$galleries[$curr] = $gallery;
            }
          }
          mysql_close($db);
          
          $this->display('gallery/views/index', array(
            'files' => $galleries,
            'rows' => $num_rows
          ));         
    }
 
    function upload() {
        if (!AuthUser::hasPermission('file_manager_upload')) {
          Flash::set('error', __('You do not have sufficient permissions to upload a file.'));
          redirect(get_url('plugin/gallery/index'));
        }

        require_once(CMS_ROOT . DS . 'config.php');

        $imageName = $_FILES['image']['name'];
        $thumbnailName = $_FILES['thumbnail']['name']; 
	$rolloverName = $_FILES['rollover']['name'];        
	$gallery_number = $_POST['gallery_number'];
	$path = $_POST['path'];
        $use_rollover = $_POST['use_rollover'];
	$path = str_replace('..', '', $path);

        $imageName = preg_replace('/ /', '_', $imageName);
        $imageName = preg_replace('/[^a-z0-9_\-\.]/i', '', $imageName);
        $thumbnailName = preg_replace('/ /', '_', $thumbnailName);
        $thumbnailName = preg_replace('/[^a-z0-9_\-\.]/i', '', $thumbnailName);
	$rolloverName = preg_replace('/ /', '_', $rolloverName);
        $rolloverName = preg_replace('/[^a-z0-9_\-\.]/i', '', $rolloverName);

        if(isset($_FILES) && $gallery_number != '') {
          $origin = $imageName;
          $origin = basename($origin);
          $dest = FILES_DIR . '/gallery/';
          $file_ext = (strpos($origin, '.') === false ? '' : '.' . substr(strrchr($origin, '.'), 1));
          $file_name = substr($origin, 0, strlen($origin) - strlen($file_ext)) . '_' . $i . $file_ext;
          $full_dest = $dest . $imageName;
          if(!move_uploaded_file($_FILES['image']['tmp_name'], $full_dest)) {
            Flash::set('error', __('File has not been uploaded!'));
          }
          
          $origin = $thumbnailName;
          $origin = basename($origin);
          $dest = FILES_DIR . '/gallery/thumbnails/';
          $file_ext = (strpos($origin, '.') === false ? '' : '.' . substr(strrchr($origin, '.'), 1));
          $file_name = substr($origin, 0, strlen($origin) - strlen($file_ext)) . '_' . $i . $file_ext;
          $full_dest = $dest . $thumbnailName;
          if(!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $full_dest)) {
            Flash::set('error', __('File has not been uploaded!'));
          }
	
	  if($use_rollover)
	  {
	    $origin = $rolloverName;
	    $origin = basename($origin);
	    $dest = FILES_DIR . '/gallery/rollovers/';
	    $file_ext = (strpos($origin, '.') === false ? '' : '.' . substr(strrchr($origin, '.'), 1));
            $file_name = substr($origin, 0, strlen($origin) - strlen($file_ext)) . '_' . $i . $file_ext;
            $full_dest = $dest . $rolloverName;
            if(!move_uploaded_file($_FILES['rollover']['tmp_name'], $full_dest)) {
              Flash::set('error', __('File has not been uploaded!'));
            }
	  }
        }
	else
	{
		Flash::set('error', __('Cannot upload files'));
        }
        $db = mysql_connect('localhost', DB_USER, DB_PASS);
        if($db) {
          mysql_select_db('gallery', $db);
          
          if($imageName != '' && $thumbnailName != '' && $gallery_number != '') {
            $select_sql = "select * from image_order where g_id = '$gallery_number'";
            $result = mysql_query($select_sql);
            $num_rows = mysql_num_rows($result);
            $num_rows++;
	    $insert_sql = "insert into image_order (order_number, image_name, thumbnail_name, rollover_name, g_id)" .
			  "values ('$num_rows', '$imageName', '$thumbnailName', '$rolloverName', '$gallery_number')";
            mysql_query($insert_sql) or Flash::setNow('error', __('Cannot insert to database'));
	    $update_sql = "update galleries set total_images = total_images+1 where gallery_number = '$gallery_number'";
            mysql_query($update_sql) or Flash::set('error', __('Cannot update database'));
          }
          else {
            Flash::setNow('error', __('There is no file to upload'));
          }
        }
        mysql_close($db);
	redirect(get_url('plugin/gallery/index'));
    }

    function create() {
	require_once(CMS_ROOT . DS . 'config.php');

	$title = $_GET['gallery'];
	$db = mysql_connect('localhost', DB_USER, DB_PASS);
	if($db) {
		mysql_select_db('gallery', $db);
		$select_sql = "select * from galleries where 1";
		$result = mysql_query($select_sql);
		$num_rows = mysql_num_rows($result);
		$num_rows++;
		if($title == '')
			$title = 'gallery' . $num_rows;
		$insert_sql = "insert into galleries (gallery_number, total_images, title) values ('$num_rows', 0, '$title')";
		mysql_query($insert_sql) or Flash::set('error', __('Cannot insert into database'));
	}
	redirect(get_url('plugin/gallery/index'));
    }

    function update() {
      require_once(CMS_ROOT . DS . 'config.php');

      $order = $_GET['orders'];
      $length = $_GET['index'];
      $gallery_number = $_GET['gallery_number'];
      $db = mysql_connect('localhost', DB_USER, DB_PASS);
      if($db) {
        mysql_select_db('gallery', $db);
        $num = explode(',', $order, $length);

        for($i = 0; $i < $length; $i = $i + 2)
        {
          $update_sql = "update image_order set order_number = -order_number where g_id = '$gallery_number' and order_number = " . $num[$i];
          mysql_query($update_sql);
        }
        for($i = 0; $i < $length; $i = $i + 2)
        {
          $update_sql = "update image_order set order_number = " . $num[$i+1] . " where g_id = '$gallery_number' and order_number = " . -$num[$i];
          mysql_query($update_sql);
        }
      }
      mysql_close($db);
    }

    function delete() {
        require_once(CMS_ROOT . DS . 'config.php');

        $args = func_get_args();
	$gallery_number = array_pop($args);
        $image_name = array_pop($args);
        $file = FILES_DIR . '/gallery/' . $image_name;
        
        if(is_file($file)) {
          $db = mysql_connect('localhost', DB_USER, DB_PASS);
          if($db) {
            mysql_select_db('gallery', $db);
            $select_sql = "select * from image_order where g_id = '$gallery_number'";
            $result = mysql_query($select_sql) or Flash::setNow('error', __('Cannot select from database'));
            $rows = mysql_num_rows($result);
            $select_sql = "select * from image_order where image_name = '$image_name' and g_id = '$gallery_number'";
            $result = mysql_query($select_sql) or Flash::setNow('error', __('Cannot select from database'));
            $thumbnail_name = mysql_result($result, 0, 'thumbnail_name');
	    $rollover_name = mysql_result($result, 0, 'rollover_name');
            $order = mysql_result($result, 0, 'order_number');

            if(!unlink($file))
                Flash::setNow('error', __('Cannot delete image file'));
            $file = FILES_DIR . '/gallery/thumbnails/' . $thumbnail_name;
            if(!unlink($file))
                Flash::setNow('error', __('Cannot delete thumbnail file'));
	    if($rollover_name != '')
	    {
	    	$file = FILES_DIR . '/gallery/rollovers/' . $rollover_name;
            	if(!unlink($file))
			Flash::setNow('error', __('Cannot delete thunbnail file'));
	    }
	    $delete_sql = "delete from image_order where image_name = '$image_name' and g_id = '$gallery_number'";
            mysql_query($delete_sql) or Flash::setNow('error', __('Cannot delete from database'));
            $order++;
            for($i = $order; $i <= $rows; $i++) {
              $num = $i - 1;
              $update_sql = "update image_order set order_number = '$num' where order_number = '$i' and g_id = '$gallery_number'";
              mysql_query($update_sql) or Flash::setNow('error', __('Cannot update database'));
            }
	    $update_sql = "update galleries set total_images = total_images-1 where gallery_number = '$gallery_number'";
            mysql_query($update_sql) or Flash::setNow('error', __('Cannot update database'));
          }
          mysql_close($db);
        } 
        redirect(get_url('plugin/gallery/index/')); 
    } 

    function delete_gallery() {
	require_once(CMS_ROOT . DS . 'config.php');	

	$gallery = func_get_args();
        $gallery_number = array_pop($gallery);

        $db = mysql_connect('localhost', DB_USER, DB_PASS);
        if($db) {
                mysql_select_db('gallery', $db);
                $select_sql = "select * from image_order where g_id = '$gallery_number'";
                $result = mysql_query($select_sql);
                $num_rows = mysql_num_rows($result);
                for($i = 0; $i < $num_rows; $i++)
                {
                        $image_name = mysql_result($result, $i, 'image_name');
			$thumbnail_name = mysql_result($result, $i, 'thumbnail_name');
			$rollover_name = mysql_result($result, $i, 'rollover_name');
                        $file = FILES_DIR . '/gallery/' . $image_name;
                        if(!unlink($file))
                                Flash::setNow('error', __('Cannot delete image file'));
			$file = FILES_DIR . '/gallery/thumbnails/' . $thumbnail_name;
			if(!unlink($file))
                                Flash::setNow('error', __('Cannot delete image file'));
			if($rollover_name != '')
			{
				$file = FILES_DIR . '/gallery/rollovers/' . $rollover_name;
				if(!unlink($file))
                        	        Flash::setNow('error', __('Cannot delete image file'));
			}
                        $delete_sql = "delete from image_order where image_name = '$image_name' and g_id = '$gallery_number'";
                        mysql_query($delete_sql) or Flash::set('error', __('Cannot delete from database'));
                }
                $delete_sql = "delete from galleries where gallery_number = '$gallery_number'";
                mysql_query($delete_sql) or Flash::set('error', __('Cannot delete from database'));
                $select_sql = "select * from galleries  where 1";
                $result = mysql_query($select_sql);
                $num_galleries = mysql_num_rows($result);
                for($i = $gallery_number; $i <= $num_galleries; $i++)
                {
                        $num = $i + 1;
			$title = 'gallery' . $i;
                        $update_sql = "update galleries set gallery_number = '$i', title = '$title' where gallery_number = '$num'";
                        mysql_query($update_sql) or Flash::set('error', __('Cannot update to database'));
			$total_images = mysql_result($result, $i, 'total_images');
                        for($j = 0; $j < $total_images; $j++)
                        {
                                $update_sql = "update images_order set g_id = '$i' where g_id = '$num'";
                                mysql_query($update_sql) or Flash::set('error', __('Cannot update to database'));
                        }
                }
        }
        mysql_close($db);
	redirect(get_url('plugin/gallery/index/'));
    }

    function edit() {
	require_once(CMS_ROOT . DS . 'config.php');
	
	$gallery_number = $_POST['gallery_number'];
	$image_number = $_POST['image_number'];	
	$description = $_POST['description'];
	$keyword = $_POST['keyword'];
	$title = $_POST['title'];

	if($description != '' || $keyword != '' || $title != '')
	{
		$db = mysql_connect('localhost', DB_USER, DB_PASS);
		if($db)
		{
			mysql_select_db('gallery', $db);
			$update_sql = "update image_order set description = '$description', title = '$title', keyword = '$keyword' " .
				  "where order_number = '$image_number' and g_id = '$gallery_number'";
			mysql_query($update_sql) or Flash::setNow('error', __('Cannot update database')); 	
		}
		else
			Flash::setNow('error', __('Cannot connect to database'));
		mysql_close($db);
	}
	redirect(get_url('plugin/gallery/index/'));
    }

    function settings() {
        /** You can do this...
        $tmp = Plugin::getAllSettings('skeleton');
        $settings = array('my_setting1' => $tmp['setting1'],
                          'setting2' => $tmp['setting2'],
                          'a_setting3' => $tmp['setting3']
                         );
        $this->display('comment/views/settings', $settings);
         *
         * Or even this...
         */

        $this->display('gallery/views/settings', Plugin::getAllSettings('gallery'));
    }
}
