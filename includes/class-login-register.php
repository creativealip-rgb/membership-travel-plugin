<?php
/**
 * Login/Register Shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Login_Register {
    
    public function __construct() {
        add_shortcode('tmp_login', [$this, 'login_form']);
        add_shortcode('tmp_register', [$this, 'register_form']);
    }
    
    /**
     * Login Form Shortcode
     * [tmp_login]
     */
    public function login_form($atts) {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            return '<div class="tmp-login-success" style="padding:20px;background:#d4edda;border:1px solid #c3e6cb;border-radius:8px;text-align:center;">
                <p style="margin:0;">Welcome back, ' . esc_html($user->display_name) . '!</p>
                <a href="' . wp_logout_url(get_permalink()) . '" class="tmpb-btn tmpb-btn-primary" style="margin-top:10px;display:inline-block;">Logout</a>
            </div>';
        }
        
        ob_start();
        ?>
        <div class="tmp-login-form" style="max-width:400px;margin:0 auto;padding:30px;background:#fff;border:1px solid #ddd;border-radius:8px;">
            <h2 style="margin-bottom:20px;text-align:center;">Login</h2>
            
            <form name="loginform" id="loginform" action="<?php echo wp_login_url(); ?>" method="post">
                <div class="tmp-form-group" style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:600;">Username or Email</label>
                    <input type="text" name="log" id="user_login" class="input" value="" size="20" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;" required>
                </div>
                
                <div class="tmp-form-group" style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:600;">Password</label>
                    <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;" required>
                </div>
                
                <div style="margin-bottom:15px;">
                    <label style="display:inline-flex;align-items:center;">
                        <input type="checkbox" name="rememberme" value="forever" style="margin-right:5px;"> Remember Me
                    </label>
                </div>
                
                <button type="submit" name="wp-submit" class="tmpb-btn tmpb-btn-primary tmpb-btn-block" style="background:#0073aa;color:#fff;padding:12px;">Login</button>
                
                <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink()); ?>">
            </form>
            
            <div style="margin-top:20px;text-align:center;">
                <p>Don't have an account? <a href="<?php echo wp_registration_url(); ?>">Register here</a></p>
                <p><a href="<?php echo wp_lostpassword_url(); ?>">Lost your password?</a></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Register Form Shortcode
     * [tmp_register]
     */
    public function register_form($atts) {
        if (is_user_logged_in()) {
            return '<p>You are already logged in.</p>';
        }
        
        $error = '';
        $success = '';
        
        // Handle registration
        if (isset($_POST['tmp_register_submit'])) {
            check_admin_referer('tmp_register_nonce', 'tmp_register_nonce_field');
            
            $username = sanitize_user($_POST['user_login']);
            $email = sanitize_email($_POST['user_email']);
            $password = $_POST['user_pass'];
            $password_confirm = $_POST['user_pass_confirm'];
            
            if (empty($username) || empty($email) || empty($password)) {
                $error = 'All fields are required.';
            } elseif ($password !== $password_confirm) {
                $error = 'Passwords do not match.';
            } elseif (!is_email($email)) {
                $error = 'Invalid email address.';
            } elseif (username_exists($username) || email_exists($email)) {
                $error = 'Username or email already exists.';
            } else {
                $user_id = wp_create_user($username, $password, $email);
                
                if (is_wp_error($user_id)) {
                    $error = $user_id->get_error_message();
                } else {
                    $success = 'Registration successful! You can now login.';
                    wp_signon([
                        'user_login' => $username,
                        'user_password' => $password,
                    ]);
                }
            }
        }
        
        ob_start();
        ?>
        <div class="tmp-register-form" style="max-width:400px;margin:0 auto;padding:30px;background:#fff;border:1px solid #ddd;border-radius:8px;">
            <h2 style="margin-bottom:20px;text-align:center;">Register</h2>
            
            <?php if ($error): ?>
                <div style="padding:10px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:4px;margin-bottom:15px;color:#721c24;"><?php echo esc_html($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="padding:10px;background:#d4edda;border:1px solid #c3e6cb;border-radius:4px;margin-bottom:15px;color:#155724;"><?php echo esc_html($success); ?></div>
            <?php endif; ?>
            
            <form name="registerform" id="registerform" action="" method="post">
                <div class="tmp-form-group" style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:600;">Username</label>
                    <input type="text" name="user_login" id="user_login" class="input" value="" size="20" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;" required>
                </div>
                
                <div class="tmp-form-group" style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:600;">Email</label>
                    <input type="email" name="user_email" id="user_email" class="input" value="" size="20" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;" required>
                </div>
                
                <div class="tmp-form-group" style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:600;">Password</label>
                    <input type="password" name="user_pass" id="user_pass" class="input" value="" size="20" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;" required>
                </div>
                
                <div class="tmp-form-group" style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:600;">Confirm Password</label>
                    <input type="password" name="user_pass_confirm" id="user_pass_confirm" class="input" value="" size="20" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;" required>
                </div>
                
                <button type="submit" name="tmp_register_submit" class="tmpb-btn tmpb-btn-primary tmpb-btn-block" style="background:#0073aa;color:#fff;padding:12px;">Register</button>
                
                <?php wp_nonce_field('tmp_register_nonce', 'tmp_register_nonce_field'); ?>
            </form>
            
            <div style="margin-top:20px;text-align:center;">
                <p>Already have an account? <a href="<?php echo wp_login_url(get_permalink()); ?>">Login here</a></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
