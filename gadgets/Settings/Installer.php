<?php
/**
 * Settings Installer
 *
 * @category    GadgetModel
 * @package     Settings
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Settings_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        'admin_script' => '',
        'http_auth' => 'false',
        'realm' => 'Jaws Control Panel',
        'key' => '',
        'theme' => 'jaws',
        'date_format' => 'd MN Y',
        'calendar_type' => 'Gregorian',
        'calendar_language' => 'en',
        'timezone' => 'UTC',
        'gzip_compression' => 'false',
        'use_gravatar' => 'no',
        'gravatar_rating' => 'G',
        'editor' => 'TextArea',
        'editor_tinymce_toolbar' => '',
        'editor_ckeditor_toolbar' => '',
        'browsers_flag' => 'opera,firefox,ie7up,ie,safari,nav,konq,gecko,text',
        'show_viewsite' => 'true',
        'cookie_precedence' => 'false',
        'robots' => '',
        'connection_timeout' => '5',           // per second
        'global_website' => 'true',            // global website?
        'img_driver' => 'GD',                  // image driver
        'site_status' => 'enabled',
        'site_name' => '',
        'site_slogan' => '',
        'site_comment' => '',
        'site_keywords' => '',
        'site_description' => '',
        'site_custom_meta' => '',
        'site_author' => '',
        'site_license' => '',
        'site_favicon' => 'images/jaws.png',
        'site_title_separator' => '-',
        'main_gadget' => '',
        'site_copyright' => '',
        'site_language' => 'en',
        'admin_language' => 'en',
        'site_email' => '',
        'cookie_domain' => '',
        'cookie_path' => '/',
        'cookie_version' => '0.4',
        'cookie_session' => 'false',
        'cookie_secure' => 'false',
        'cookie_httponly' => 'false',
        'ftp_enabled' => 'false',
        'ftp_host' => '127.0.0.1',
        'ftp_port' => '21',
        'ftp_mode' => 'passive',
        'ftp_user' => '',
        'ftp_pass' => '',
        'ftp_root' => '',
        'proxy_enabled' => 'false',
        'proxy_type' => 'http',
        'proxy_host' => '',
        'proxy_port' => '80',
        'proxy_auth' => 'false',
        'proxy_user' => '',
        'proxy_pass' => '',
        'mailer' => 'phpmail',
        'gate_email' => '',
        'gate_title' => '',
        'smtp_vrfy' => 'false',
        'sendmail_path' => '/usr/sbin/sendmail',
        'sendmail_args' => '',
        'smtp_host' => '127.0.0.1',
        'smtp_port' => '25',
        'smtp_auth' => 'false',
        'pipelining' => 'false',
        'smtp_user' => '',
        'smtp_pass' => '',
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'BasicSettings',
        'AdvancedSettings',
        'MetaSettings',
        'MailSettings',
        'FTPSettings',
        'ProxySettings',
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $robots = array(
            'Yahoo! Slurp', 'Baiduspider', 'Googlebot', 'msnbot', 'Gigabot', 'ia_archiver',
            'yacybot', 'http://www.WISEnutbot.com', 'psbot', 'msnbot-media', 'Ask Jeeves',
        );

        $uniqueKey =  sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                              mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
                              mt_rand( 0, 0x0fff ) | 0x4000,
                              mt_rand( 0, 0x3fff ) | 0x8000,
                              mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
        $uniqueKey = md5($uniqueKey);

        // Registry keys
        $this->gadget->registry->update('key', $uniqueKey);
        $this->gadget->registry->update('robots', implode(',', $robots));

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.4.0', '<')) {
            $this->gadget->registry->insert('global_website', 'true');
            $this->gadget->registry->insert('cookie_httponly', 'false');
            $this->gadget->registry->delete('allow_comments');
            $this->gadget->registry->delete('layoutmode');
            $this->gadget->registry->delete('site_url');
        }

        return true;
    }

}