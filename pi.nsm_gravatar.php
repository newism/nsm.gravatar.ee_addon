<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require PATH_THIRD . 'nsm_gravatar/config.php';

/**
 * NSM Gravatar Plugin
 *
 * Generally a module is better to use than a plugin if if it has not CP backend
 *
 * @package         NSMGravatar
 * @version         0.0.1
 * @author          Leevi Graham <http://leevigraham.com>
 * @copyright       Copyright (c) 2007-2010 Newism <http://newism.com.au>
 * @license         Commercial - please see LICENSE file included with this distribution
 * @link            http://ee-garage.com/nsm-gravatar
 * @see             http://expressionengine.com/docs/development/plugins.html
 */

/**
 * Plugin Info
 *
 * @var array
 */
$plugin_info = array(
    'pi_name' => NSM_GRAVATAR_NAME,
    'pi_version' => NSM_GRAVATAR_VERSION,
    'pi_author' => 'Leevi Graham',
    'pi_author_url' => 'http://leevigraham.com/',
    'pi_description' => 'Gravatar Plugin for ExpressionEngine 2',
    'pi_usage' => "Refer to the included README"
);

class Nsm_gravatar
{
    /**
     * The return string
     *
     * @var string
     */
    public $return_data = "";

    private $options = array(
        "size" => "s",
        "default" => "d",
        "force_default" => "f",
        "rating" => "r"
    );

    function Nsm_gravatar()
    {
        $EE =& get_instance();

        // Process Email
        $email = $EE->TMPL->fetch_param('email');
        $username = $EE->TMPL->fetch_param('username');
        $member_id = $EE->TMPL->fetch_param('member_id');

        $secure = false;

        if (isset($_SERVER['HTTPS']) && "on" == $_SERVER['HTTPS']) {
            $secure = "yes";
        }

        $secure = $EE->TMPL->fetch_param('secure', $secure);

        if (false == $email && false == $username && false == $member_id) {
            $EE->output->show_message(array(
                'title' => 'NSM Gravatar Error',
                'heading' => 'NSM Gravatar Error',
                'content' => '{exp:nsm_gravatar} requires one of the following params: <code>email</code>, <code>username</code>, or <code>id</code>'
            ));
        }

        if ($email == "logged_in_member") {
            $email = $EE->session->userdata["email"];
        } elseif ($email == "") {
            $EE->db->select('email');
            $EE->db->from('exp_members');
            $EE->db->limit(1);

            if (false !== $username) {
                $EE->db->where('username', $username);
            } else if (false !== $member_id) {
                $EE->db->where('member_id', $member_id);
            }

            $email = $EE->db->row(0)->email;
        }

        $query_params = array();
        foreach ($this->options as $key => $query_param) {
            $val = $EE->TMPL->fetch_param($key, $EE->config->item($key, 'nsm_gravatar'));
            if (false !== $val) {
                $query_params[$query_param] = ("d" == $key) ? urlencode($val) : $val;
            }
        }

        $gravatar_url = ("yes" == $secure) ? "https://secure.gravatar.com/" : "http://www.gravatar.com/";
        $gravatar_url .= 'avatar/';
        $gravatar_url .= md5(strtolower($email)) . "?";
        $gravatar_url .= http_build_query($query_params, '', '&amp;');

        $this->return_data = $gravatar_url;
    }

}
