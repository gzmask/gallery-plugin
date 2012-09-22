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

          $filelist = array();
          $db = mysql_connect('localhost', DB_USER, DB_PASS);
          if($db) {
            mysql_select_db('gallery', $db);
            $select_sql = 'select * from image_order where 1';
            $result = mysql_query($select_sql) or Flash::setNow('error', __('Cannot select from database'));
            $num_rows = mysql_num_rows($result);
          
            for($curr = 1; $curr <= $num_rows; $curr++) {
              $select_sql = 'select * from image_order where order_number = ' . $curr;
              $result = mysql_query($select_sql) or Flash::setNow('error', __('Cannot select from database'));
              $image = mysql_result($result, 0, 'image_name');
              $thumbnail = mysql_result($result, 0, 'thumbnail_name');
              $order = mysql_result($result, 0, 'order_number');
              $image_path = URL_PUBLIC . 'public/gallery/' . $image;
              $thumbnail_path = URL_PUBLIC . 'public/gallery/thumbnails/' . $thumbnail;
              $object = new stdClass;
              $object->order = $order;
              $object->image_path = $image_path;
              $object->thumbnail_path = $thumbnail_path;

              $filelist[$object->order] = $object;
            }
          }
          mysql_close($db);
          
          $this->display('gallery/views/index', array(
            'files' => $filelist,
            'rows' => $num_rows
          ));
          
    }
 
    public function upload() {
        if (!AuthUser::hasPermission('file_manager_upload')) {
          Flash::set('error', __('You do not have sufficient permissions to upload a file.'));
          redirect(get_url('plugin/gallery/index'));
        }

        require_once(CMS_ROOT . DS . 'config.php');

        $imageName = $_FILES['image']['name'];
        $thumbnailName = $_FILES['thumbnail']['name']; 
        $path = $_POST['path'];
        $path = str_replace('..', '', $path);

        $imageName = preg_replace('/ /', '_', $imageName);
        $imageName = preg_replace('/[^a-z0-9_\-\.]/i', '', $imageName);
        $thumbnailName = preg_replace('/ /', '_', $thumbnailName);
        $thumbnailName = preg_replace('/[^a-z0-9_\-\.]/i', '', $thumbnailName);

        if(isset($_FILES)) {
          $origin = $imageName;
          $origin = basename($origin);
          $dest = FILES_DIR . '/gallery/';
          $file_ext = (strpos($origin, '.') === false ? '' : '.' . substr(strrchr($origin, '.'), 1));
          $file_name = substr($origin, 0, strlen($origin) - strlen($file_ext)) . '_' . $i . $file_ext;
          $full_dest = $dest . $imageName;
          if(!move_uploaded_file($_FILES['image']['tmp_name'], $full_dest)) {
            Flash::set('error', __('File has not been uploaded!'));
            redirect(get_url('plugin/gallery/index'));
          }
          
          $origin = $thumbnailName;
          $origin = basename($origin);
          $dest = FILES_DIR . '/gallery/thumbnails/';
          $file_ext = (strpos($origin, '.') === false ? '' : '.' . substr(strrchr($origin, '.'), 1));
          $file_name = substr($origin, 0, strlen($origin) - strlen($file_ext)) . '_' . $i . $file_ext;
          $full_dest = $dest . $thumbnailName;
          if(!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $full_dest)) {
            Flash::set('error', __('File has not been uploaded!'));
            redirect(get_url('plugin/gallery/index'));
          }
        }
        
        $db = mysql_connect('localhost', DB_USER, DB_PASS);
        if($db) {
          mysql_select_db('gallery', $db);
          
          if($imageName != '' && $thumbnailName != '') {
            $select_sql = "select * from image_order where 1";
            $result = mysql_query($select_sql);
            $num_rows = mysql_num_rows($result);
            $num_rows++;
            $insert_sql = "insert into image_order (order_number, image_name, thumbnail_name)" .
                          "values ('$num_rows', '$imageName', '$thumbnailName')";
            mysql_query($insert_sql) or Flash::setNow('error', __('Cannot insert to database'));
            redirect(get_url('plugin/gallery/index'));
          }
          else {
            Flash::setNow('error', __('There is no file to upload'));
          }
        }
        mysql_close($db);
    }

    function update() {
      require_once(CMS_ROOT . DS . 'config.php');

      $order = $_GET['orders'];
      $length = $_GET['index'];
      $db = mysql_connect('localhost', DB_USER, DB_PASS);
      if($db) {
        mysql_select_db('gallery', $db);
        $num = explode(',', $order, $length);

        for($i = 0; $i < $length; $i = $i + 2)
        {
          $update_sql = "update image_order set order_number = -order_number where order_number = " . $num[$i];
          mysql_query($update_sql);
        }
        for($i = 0; $i < $length; $i = $i + 2)
        {
          $update_sql = "update image_order set order_number = " . $num[$i+1] . " where order_number = " . -$num[$i];
          mysql_query($update_sql);
        }
      }
      mysql_close($db);
    }

    function delete() {
        require_once(CMS_ROOT . DS . 'config.php');

        $order = func_get_args();
        $image_name = array_pop($order);
        $file = FILES_DIR . '/gallery/' . $image_name;
        
        if(is_file($file)) {
          $db = mysql_connect('localhost', DB_USER, DB_PASS);
          if($db) {
            mysql_select_db('gallery', $db);
            $select_sql = "select * from image_order where 1";
            $result = mysql_query($select_sql) or Flash::setNow('error', __('Cannot select from database'));
            $rows = mysql_num_rows($result);
            $select_sql = "select * from image_order where image_name = '$image_name'";
            $result = mysql_query($select_sql) or Flash::setNow('error', __('Cannot select from database'));
            $thumbnail_name = mysql_result($result, 0, 'thumbnail_name');
            $order = mysql_result($result, 0, 'order_number');

            if(!unlink($file))
                Flash::setNow('error', __('Cannot delete image file'));
            $file = FILES_DIR . '/gallery/thumbnails/' . $thumbnail_name;
            if(!unlink($file))
                Flash::setNow('error', __('Cannot delete thumbnail file'));
            $delete_sql = "delete from image_order where image_name = '$image_name'";
            mysql_query($delete_sql) or Flash::setNow('error', __('Cannot delete from database'));
            $order++;
            for($i = $order; $i <= $rows; $i++) {
              $num = $i - 1;
              $update_sql = "update image_order set order_number = '$num' where order_number = '$i'";
              mysql_query($update_sql) or Flash::setNow('error', __('Cannot update database'));
            }
          }
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
