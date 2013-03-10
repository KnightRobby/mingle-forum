<?php
if(!class_exists("MFAdmin"))
{
  class MFAdmin
  {
    public static function load_hooks()
    {
      add_action('admin_init', 'MFAdmin::maybe_save_options');
      add_action('admin_init', 'MFAdmin::maybe_save_ads_options');
      // add_action('admin_menu', 'MFAdmin::admin_menus');
      add_action('admin_enqueue_scripts', 'MFAdmin::enqueue_admin_scripts');
    }

    public static function enqueue_admin_scripts($hook)
    {
      $plug_url = plugin_dir_url(__FILE__) . '../';

      //Let's only load our shiz on mingle-forum admin pages
      if (strstr($hook, 'mingle-forum') !== false)
      {
        $wp_scripts = new WP_Scripts();
        $ui = $wp_scripts->query('jquery-ui-core');
        $url = "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/start/jquery-ui.css";

        wp_enqueue_style('mingle-forum-ui-css', $url);
        wp_enqueue_style('mingle-forum-admin-css', $plug_url . "css/mf_admin.css");
        wp_enqueue_script('mingle-forum-admin-js', $plug_url . "js/mf_admin.js", array('jquery-ui-accordion', 'jquery-ui-sortable'));
      }
    }

    public static function options_page()
    {
      global $mingleforum;

      $saved = (isset($_GET['saved']) && $_GET['saved'] == 'true');

      require('views/options_page.php');
    }

    public static function ads_options_page()
    {
      global $mingleforum;

      $saved = (isset($_GET['saved']) && $_GET['saved'] == 'true');

      require('views/ads_options_page.php');
    }

    public static function structure_page()
    {
      global $mingleforum;

      $action = (isset($_GET['action']) && !empty($_GET['action']))?$_GET['action']:false;
      $categories = $mingleforum->get_groups();

      switch($action)
      {
        case 'forums':
          require('views/structure_page_forums.php');
          break;
        default:
          require('views/structure_page_categories.php');
          break;
      }
    }

    public static function maybe_save_options()
    {
      global $wpdb, $mingleforum;

      $saved_ops = array();

      if(!isset($_POST['mf_options_submit']) || empty($_POST['mf_options_submit']))
        return;

      foreach($mingleforum->default_ops as $k => $v)
      {
        if(isset($_POST[$k]) && !empty($_POST[$k]))
        {
          if(is_array($v))
            $saved_ops[$k] = explode(',', $_POST[$k]);
          elseif(is_numeric($v))
            $saved_ops[$k] = (int)$_POST[$k];
          elseif(is_bool($v))
            $saved_ops[$k] = true;
          else
            $saved_ops[$k] = $wpdb->escape(stripslashes($_POST[$k]));
        }
        else
        {
          if(is_array($v))
            $saved_ops[$k] = array();
          elseif(is_numeric($v))
            $saved_ops[$k] = $v;
          elseif(is_bool($v))
            $saved_ops[$k] = false;
          else
            $saved_ops[$k] = '';
        }
      }

      //Set some stuff that isn't on the options page
      $saved_ops['forum_skin'] = $mingleforum->options['forum_skin'];
      $saved_ops['forum_db_version'] = $mingleforum->options['forum_db_version'];

      update_option('mingleforum_options', $saved_ops);
      wp_redirect(admin_url('admin.php?page=mingle-forum&saved=true'));
    }

    public static function maybe_save_ads_options()
    {
      global $wpdb, $mingleforum;

      if(!isset($_POST['mf_ads_options_save']) || empty($_POST['mf_ads_options_save']))
        return;

      $mingleforum->ads_options = array('mf_ad_above_forum_on' => isset($_POST['mf_ad_above_forum_on']),
                                        'mf_ad_above_forum' => $wpdb->escape(stripslashes($_POST['mf_ad_above_forum_text'])),
                                        'mf_ad_below_forum_on' => isset($_POST['mf_ad_below_forum_on']),
                                        'mf_ad_below_forum' => $wpdb->escape(stripslashes($_POST['mf_ad_below_forum_text'])),
                                        'mf_ad_above_branding_on' => isset($_POST['mf_ad_above_branding_on']),
                                        'mf_ad_above_branding' => $wpdb->escape(stripslashes($_POST['mf_ad_above_branding_text'])),
                                        'mf_ad_above_info_center_on' => isset($_POST['mf_ad_above_info_center_on']),
                                        'mf_ad_above_info_center' => $wpdb->escape(stripslashes($_POST['mf_ad_above_info_center_text'])),
                                        'mf_ad_above_quick_reply_on' => isset($_POST['mf_ad_above_quick_reply_on']),
                                        'mf_ad_above_quick_reply' => $wpdb->escape(stripslashes($_POST['mf_ad_above_quick_reply_text'])),
                                        'mf_ad_below_menu_on' => isset($_POST['mf_ad_below_menu_on']),
                                        'mf_ad_below_menu' => $wpdb->escape(stripslashes($_POST['mf_ad_below_menu_text'])),
                                        'mf_ad_below_first_post_on' => isset($_POST['mf_ad_below_first_post_on']),
                                        'mf_ad_below_first_post' => $wpdb->escape(stripslashes($_POST['mf_ad_below_first_post_text'])),
                                        'mf_ad_custom_css' => strip_tags($_POST['mf_ad_custom_css']));

      update_option('mingleforum_ads_options', $mingleforum->ads_options);

      wp_redirect(admin_url('admin.php?page=mingle-forum-ads&saved=true'));
    }
  } //End class
} //End if
?>
