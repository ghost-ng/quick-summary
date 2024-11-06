<?php
/*
Plugin Name: Quick Post Summary
Description: Generates a summary of the current post content using OpenAI's API and displays it in a user-friendly manner.
Version: 1.00
Author: ghost-ng
Author URI: https://github.com/ghost-ng/quick-summary/
*/

if (!defined('ABSPATH')) {
    exit;
}

class QuickPostSummary {
    private $option_name = 'quickpost_summary_settings';

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_summary_meta_box'));
        add_action('save_post', array($this, 'save_summary_meta_box_data'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_gutenberg_block'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('wp_footer', array($this, 'add_modal_html'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    public function add_summary_meta_box() {
        add_meta_box(
            'quickpost_summary_meta_box',
            __('QuickPost Summary', 'quickpost-summary'),
            array($this, 'render_summary_meta_box'),
            'post',
            'side'
        );
    }

    public function render_summary_meta_box($post) {
        wp_nonce_field('quickpost_summary_meta_box', 'quickpost_summary_meta_box_nonce');
        $summary = get_post_meta($post->ID, '_quickpost_summary', true);
        $word_count = get_post_meta($post->ID, '_quickpost_summary_word_count', true);
        
        echo '<label for="quickpost_summary_word_count">' . __('Max Word Count:', 'quickpost-summary') . '</label>';
        echo '<input type="number" id="quickpost_summary_word_count" name="quickpost_summary_word_count" value="' . esc_attr($word_count) . '" class="quickpost-input" />';
        echo '<textarea id="quickpost_summary" name="quickpost_summary" class="quickpost-textarea">' . esc_textarea($summary) . '</textarea>';
        
        // Generate Summary Button
        echo '<button type="button" id="generate_summary_button" class="quickpost-btn quickpost-generate-btn">' . __('Generate Summary', 'quickpost-summary') . '</button>';
        
        // Save Summary Button
        echo '<button type="button" id="save_summary_button" class="quickpost-btn quickpost-save-btn">' . __('Save Summary', 'quickpost-summary') . '</button>';
    
        // JavaScript for AJAX handling
        echo '<script>
            document.getElementById("generate_summary_button").addEventListener("click", function() {
                var postId = ' . $post->ID . ';
                var wordCount = document.getElementById("quickpost_summary_word_count").value;
                var summaryField = document.getElementById("quickpost_summary");
                
                summaryField.value = "Generating...";
                
                fetch("' . rest_url('quickpost-summary/v1/generate') . '", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-WP-Nonce": "' . wp_create_nonce('wp_rest') . '"
                    },
                    body: JSON.stringify({ post_id: postId, word_count: wordCount })
                })
                .then(response => response.json())
                .then(data => {
                    summaryField.value = data.summary;
                })
                .catch(error => {
                    summaryField.value = "Error generating summary.";
                    console.error("Error:", error);
                });
            });
    
            document.getElementById("save_summary_button").addEventListener("click", function() {
                var postId = ' . $post->ID . ';
                var summary = document.getElementById("quickpost_summary").value;
                var wordCount = document.getElementById("quickpost_summary_word_count").value;
    
                fetch("' . rest_url('quickpost-summary/v1/save-summary') . '", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-WP-Nonce": "' . wp_create_nonce('wp_rest') . '"
                    },
                    body: JSON.stringify({ post_id: postId, summary: summary, word_count: wordCount })
                })
                .then(response => response.json())
                .then(data => {
                    alert("Summary saved successfully!");
                })
                .catch(error => {
                    alert("Error saving summary.");
                    console.error("Error:", error);
                });
            });
        </script>';
    }
    
    public function save_summary_meta_box_data($post_id) {
        if (!isset($_POST['quickpost_summary_meta_box_nonce']) || !wp_verify_nonce($_POST['quickpost_summary_meta_box_nonce'], 'quickpost_summary_meta_box')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (isset($_POST['quickpost_summary_word_count'])) {
            update_post_meta($post_id, '_quickpost_summary_word_count', sanitize_text_field($_POST['quickpost_summary_word_count']));
        }
        if (isset($_POST['quickpost_summary'])) {
            update_post_meta($post_id, '_quickpost_summary', sanitize_textarea_field($_POST['quickpost_summary']));
        }
    }

    public function enqueue_gutenberg_block() {
        $block_path = plugin_dir_path(__FILE__) . 'quickpost-summary-block.js';
        $block_url = plugins_url('quickpost-summary-block.js', __FILE__);
        $version = file_exists($block_path) ? filemtime($block_path) : '1.0';
    
        wp_enqueue_script(
            'quickpost-summary-block',
            $block_url,
            array('wp-blocks', 'wp-element', 'wp-editor'),
            $version
        );
    
        wp_add_inline_script('quickpost-summary-block', '
            wp.blocks.registerBlockType("quickpost/summary", {
                title: "QuickPost Summary",
                icon: "editor-quote",
                category: "widgets",
                edit: function() {
                    return wp.element.createElement("button", {
                        className: "quickpost-summary-show-button",
                        id: "show-summary-button",
                        onClick: function() {
                            document.getElementById("quickpost-summary-modal").style.display = "block";
                        },
                        style: { backgroundColor: "#0073aa", color: "white", padding: "8px 12px", borderRadius: "4px", border: "none", cursor: "pointer" }
                    }, "Show Summary");
                },
                save: function() {
                    return wp.element.createElement("button", {
                        className: "quickpost-summary-show-button",
                        id: "show-summary-button",
                        onClick: function() {
                            document.getElementById("quickpost-summary-modal").style.display = "block";
                        },
                        style: { backgroundColor: "#0073aa", color: "white", padding: "8px 12px", borderRadius: "4px", border: "none", cursor: "pointer" }
                    }, "Show Summary");
                }
            });
        ');
    }

    public function enqueue_frontend_scripts() {
        if (is_single()) {
            // Enqueue any necessary styles
            wp_enqueue_style('quickpost-summary-style', plugins_url('style.css', __FILE__), array(), '1.0');
    
            // Register a dummy JavaScript file as a handle for the inline script
            wp_register_script('quickpost-summary-inline-js', '', [], '1.0', true);
    
            // Add inline JavaScript to handle modal functionality
            wp_add_inline_script('quickpost-summary-inline-js', '
                document.addEventListener("DOMContentLoaded", function () {
                    const showSummaryButton = document.getElementById("show-summary-button");
                    const modal = document.getElementById("quickpost-summary-modal");
                    const closeModalButton = document.getElementById("close-summary-modal");
    
                    if (showSummaryButton && modal) {
                        showSummaryButton.addEventListener("click", function () {
                            modal.style.display = "block";
                        });
                    }
    
                    if (closeModalButton) {
                        closeModalButton.addEventListener("click", function () {
                            modal.style.display = "none";
                        });
                    }
    
                    window.addEventListener("click", function (event) {
                        if (event.target === modal) {
                            modal.style.display = "none";
                        }
                    });
                });
            ');
    
            // Enqueue the dummy JavaScript to ensure inline script runs
            wp_enqueue_script('quickpost-summary-inline-js');
        }
    }
    

    public function add_modal_html() {
        if (is_single()) {
            $summary = esc_html(get_post_meta(get_the_ID(), '_quickpost_summary', true));
            ?>
            <div id="quickpost-summary-modal" class="quickpost-modal-overlay">
                <div class="quickpost-modal-content">
                    <h2>Post Summary</h2>
                    <p id="quickpost-summary-content"><?php echo $summary; ?></p>
                    <button id="close-summary-modal" class="quickpost-modal-close-btn">Close</button>
                </div>
            </div>
            <?php
        }
    }

    public function register_rest_routes() {
        register_rest_route('quickpost-summary/v1', '/generate', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_summary'),
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            }
        ));
    
        register_rest_route('quickpost-summary/v1', '/save-summary', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_summary_via_rest'),
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            }
        ));
    }
    
    public function save_summary_via_rest(WP_REST_Request $request) {
        $post_id = $request->get_param('post_id');
        $summary = $request->get_param('summary');
        $word_count = $request->get_param('word_count');

        update_post_meta($post_id, '_quickpost_summary', sanitize_textarea_field($summary));
        update_post_meta($post_id, '_quickpost_summary_word_count', sanitize_text_field($word_count));

        return rest_ensure_response(array('message' => 'Summary saved successfully'));
    }
    
    public function generate_summary(WP_REST_Request $request) {
        $post_id = $request->get_param('post_id');
        $word_count = $request->get_param('word_count');
        $post_content = get_post_field('post_content', $post_id);

        $options = get_option($this->option_name, array('api_key' => '', 'model' => ''));
        $api_key = $options['api_key'];
        $model = $options['model'];

        if (empty($api_key) || empty($model)) {
            return rest_ensure_response(array('summary' => 'API key or model is missing.'));
        }

        $prompt = "Summarize the following content in a maximum of $word_count words. Do not return the text in markdown, text ONLY.\n\n" . $post_content;
        $approx_tokens = $word_count * 1.5;

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => array(
                    array('role' => 'user', 'content' => $prompt)
                ),
                'max_tokens' => (int) $approx_tokens,
            )),
        ));

        if (is_wp_error($response)) {
            return rest_ensure_response(array('summary' => 'Error generating summary.'));
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $summary = isset($body['choices'][0]['message']['content']) ? trim($body['choices'][0]['message']['content']) : 'Error generating summary.';

        update_post_meta($post_id, '_quickpost_summary', $summary);
        return rest_ensure_response(array('summary' => $summary));
    }

    public function add_settings_page() {
        add_options_page(
            'QuickPost Summary Settings',
            'QuickPost Summary',
            'manage_options',
            'quickpost-summary',
            array($this, 'render_settings_page')
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('QuickPost Summary Settings', 'quickpost-summary'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->option_name);
                do_settings_sections('quickpost-summary');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting($this->option_name, $this->option_name, array($this, 'sanitize_settings'));

        add_settings_section(
            'quickpost_summary_section',
            __('API Settings', 'quickpost-summary'),
            null,
            'quickpost-summary'
        );

        add_settings_field(
            'api_key',
            __('OpenAI API Key', 'quickpost-summary'),
            array($this, 'render_api_key_field'),
            'quickpost-summary',
            'quickpost_summary_section'
        );

        add_settings_field(
            'model',
            __('Model', 'quickpost-summary'),
            array($this, 'render_model_field'),
            'quickpost-summary',
            'quickpost_summary_section'
        );
    }

    public function sanitize_settings($input) {
        $output = array();
        $output['api_key'] = sanitize_text_field($input['api_key']);
        $output['model'] = sanitize_text_field($input['model']);
        return $output;
    }

    public function render_api_key_field() {
        $options = get_option($this->option_name, array('api_key' => '', 'model' => ''));
        ?>
        <input type="text" name="<?php echo $this->option_name; ?>[api_key]" value="<?php echo esc_attr($options['api_key']); ?>" class="regular-text" />
        <?php
    }

    public function render_model_field() {
        $options = get_option($this->option_name, array('api_key' => '', 'model' => ''));
        $models = array('gpt-4', 'gpt-3.5-turbo');
        ?>
        <select name="<?php echo $this->option_name; ?>[model]">
            <?php foreach ($models as $model): ?>
                <option value="<?php echo esc_attr($model); ?>" <?php selected($options['model'], $model); ?>><?php echo esc_html($model); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function enqueue_admin_styles() {
        wp_enqueue_style('quickpost-summary-admin', plugins_url('style.css', __FILE__), array(), '1.0');
    }
}

new QuickPostSummary();
?>