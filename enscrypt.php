<?php
/**
 * Plugin Name: Enscrypt
 * Description: Seemless authentication implementation using scrypt as its primary underlying hashing algorithm.
 * Author: Brendan Warkentin
 * Author URI: http://brendanwarkentin.com/
 * Version: 1.0
 * License: MIT
 * License URI: http://opensource.org/licenses/MIT
 */

/*  Copyright (c) 2015 Brendan Warkentin

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
*/

function enscrypt($pass, $salt = null, $N = 16384, $r = 8, $p = 1, $len = 32) {
    if(empty($salt)) {
        $salt = function($hl = 20){
            $r = '';
            $c = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
            for($i = 0; $i < $hl; $i++){
                $r .= $c[rand(0, count($c) - 1)];
            }
            return $r;
        };
        $salt = $salt();
    }
    $pass = scrypt($pass, $salt, $N, $r, $p, $len);
    $pass = '$scrypt$' . $salt . '$' . $pass;
    return $pass;
}

function enscrypt_compare($password, $hash) {
    if(extension_loaded('scrypt') && !empty($password)) {
        $ph_parts = explode('$', $hash);

        if(!empty($ph_parts[1]) && $ph_parts[1] == 'scrypt') {
            $salt = (!empty($ph_parts[2]) ? $ph_parts[2] : null);

            if($salt) {
                $authed = enscrypt($password, $salt) === $hash;

                if($authed) {
                    return true;
                }
            }
            return false;
        }
    }
    return null;
}

if(!function_exists('wp_set_password')) {
    function wp_set_password($password, $user_id) {
        global $wpdb;

        $hash = wp_hash_password($password);

        if(extension_loaded('scrypt')) {
            $e_hash = enscrypt($password);
            $field = 'enscrypt_pass';
            update_user_meta($user_id, $field, $e_hash);
        }

        $wpdb->update($wpdb->users, array('user_pass' => $hash, 'user_activation_key' => ''), array('ID' => $user_id));
        wp_cache_delete($user_id, 'users');
    }
}

if(!function_exists('wp_check_password')){
    function wp_check_password($password, $hash, $user_id = '') {
        $check = enscrypt_compare($password, $hash);
        if($check === null) {
            if(strlen($hash) <= 32) {
                $check = hash_equals($hash, md5($password));
            } else {
                require_once(ABSPATH . WPINC . '/class-phpass.php');
                $fb_hasher = new PasswordHash(8, true);
                $check = $fb_hasher->CheckPassword($password, $hash);
            }

            if(extension_loaded('scrypt') && $check && $user_id) {
                wp_set_password($password, $user_id);
                $user_data = get_user_by('id', $user_id);
                $hash = $user_data->data->user_pass;
            }
        }
        return apply_filters('check_password', $check, $password, $hash, $user_id);
    }
}

add_filter('wp_authenticate_user', 'enscrypt_authenticate_user_filter', 10, 2);
function enscrypt_authenticate_user_filter($user, $password) {
    if($user) {
        $uid = $user->ID;
        $field = 'enscrypt_pass';

        $enscrypt_pass = get_user_meta($uid, $field, true);
        if(!empty($enscrypt_pass)) {
            $user->user_pass = $enscrypt_pass;
        }
    }
    return $user;
}

register_deactivation_hook(__FILE__, 'enscrypt_deactivate');
function enscrypt_deactivate() {
    delete_metadata('user', 0, 'enscrypt_pass', '', true);
}