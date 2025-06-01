<?php
/**
 * Shortcodes for the Quiz System plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register shortcodes
 */
function quiz_system_register_shortcodes() {
    add_shortcode('my_quiz', 'quiz_system_quiz_shortcode');
}
add_action('init', 'quiz_system_register_shortcodes');

/**
 * Quiz shortcode callback
 */
function quiz_system_quiz_shortcode($atts) {
    // Extract attributes
    $atts = shortcode_atts(array(
        'id' => 0,
    ), $atts, 'my_quiz');
    
    // Check if quiz ID is provided
    if (empty($atts['id'])) {
        return '<p class="quiz-error">' . __('Error: Quiz ID is required.', 'quiz-system') . '</p>';
    }
    
    // Get quiz
    $quiz = get_post(absint($atts['id']));
    if (!$quiz || 'quiz' !== $quiz->post_type) {
        return '<p class="quiz-error">' . __('Error: Quiz not found.', 'quiz-system') . '</p>';
    }
    
    // Start output buffering
    ob_start();
    
    // Check if quiz is being submitted
    if (isset($_POST['quiz_submit']) && $_POST['quiz_id'] == $quiz->ID) {
        // Process quiz submission
        quiz_system_process_submission();
    } else {
        // Display quiz
        quiz_system_display_quiz($quiz);
    }
    
    // Return buffered content
    return ob_get_clean();
}

/**
 * Enqueue frontend scripts and styles
 */
function quiz_system_enqueue_scripts() {
    wp_enqueue_style('quiz-system', QUIZ_SYSTEM_PLUGIN_URL . 'assets/css/quiz-system.css', array(), QUIZ_SYSTEM_VERSION);
    wp_enqueue_script('quiz-system', QUIZ_SYSTEM_PLUGIN_URL . 'assets/js/quiz-system.js', array('jquery'), QUIZ_SYSTEM_VERSION, true);
    
    // Pass data to script
    wp_localize_script('quiz-system', 'quizSystemData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('quiz_system_nonce'),
        'i18n' => array(
            'timeUp' => __('Time\'s up! Submitting your answers...', 'quiz-system'),
            'confirmSubmit' => __('Are you sure you want to submit your answers?', 'quiz-system'),
            'required' => __('This field is required.', 'quiz-system'),
            'validEmail' => __('Please enter a valid email address.', 'quiz-system'),
        ),
    ));
}
add_action('wp_enqueue_scripts', 'quiz_system_enqueue_scripts');