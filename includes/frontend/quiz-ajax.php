<?php
/**
 * AJAX handling for quiz submissions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register AJAX actions
 */
function quiz_system_register_ajax_actions() {
    add_action('wp_ajax_quiz_submit', 'quiz_system_ajax_submit');
    add_action('wp_ajax_nopriv_quiz_submit', 'quiz_system_ajax_submit');
}
add_action('init', 'quiz_system_register_ajax_actions');

/**
 * Enqueue AJAX scripts and styles
 */
function quiz_system_enqueue_ajax_scripts() {
    // Register and enqueue styles
    wp_register_style('quiz-system-styles', QUIZ_SYSTEM_PLUGIN_URL . 'assets/css/quiz-style.css', array(), QUIZ_SYSTEM_VERSION);
    wp_enqueue_style('quiz-system-styles');
    
    // Register and enqueue scripts
    wp_register_script('quiz-system-ajax', QUIZ_SYSTEM_PLUGIN_URL . 'assets/js/quiz-ajax.js', array('jquery'), QUIZ_SYSTEM_VERSION, true);
    
    // Localize script with necessary data
    wp_localize_script('quiz-system-ajax', 'quiz_ajax_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('quiz_ajax_nonce'),
        'error_required_fields' => __('Please fill in all required fields.', 'quiz-system'),
        'error_submission' => __('There was an error submitting your quiz. Please try again.', 'quiz-system')
    ));
    
    wp_enqueue_script('quiz-system-ajax');
}
add_action('wp_enqueue_scripts', 'quiz_system_enqueue_ajax_scripts');

/**
 * Handle AJAX quiz submission
 */
function quiz_system_ajax_submit() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'quiz_ajax_nonce')) {
        wp_send_json_error(__('Security check failed.', 'quiz-system'));
        die();
    }
    
    // Parse form data
    $form_data = array();
    parse_str($_POST['form_data'], $form_data);
    
    // Check if quiz ID is set
    if (!isset($form_data['quiz_id'])) {
        wp_send_json_error(__('Invalid quiz submission.', 'quiz-system'));
        die();
    }
    
    // Get quiz ID
    $quiz_id = absint($form_data['quiz_id']);
    
    // Get user details
    $user_name = isset($form_data['quiz_user_name']) ? sanitize_text_field($form_data['quiz_user_name']) : '';
    $user_email = isset($form_data['quiz_user_email']) ? sanitize_email($form_data['quiz_user_email']) : '';
    
    // Validate user details
    if (empty($user_name) || empty($user_email)) {
        wp_send_json_error(__('Name and email are required.', 'quiz-system'));
        die();
    }
    
    // Get quiz questions
    $question_ids = get_post_meta($quiz_id, '_quiz_questions', true);
    if (!is_array($question_ids) || empty($question_ids)) {
        wp_send_json_error(__('This quiz has no questions.', 'quiz-system'));
        die();
    }
    
    // Get user answers
    $user_answers = isset($form_data['quiz_answers']) ? $form_data['quiz_answers'] : array();
    
    // Calculate score
    $score = 0;
    $total_questions = count($question_ids);
    
    foreach ($question_ids as $question_id) {
        // Get question type and answers
        $question_type = get_post_meta($question_id, '_question_type', true);
        $answers = get_post_meta($question_id, '_question_answers', true);
        
        if (!is_array($answers)) {
            continue;
        }
        
        // Get correct answers (text values)
        $correct_answer_texts = array();
        foreach ($answers as $answer) {
            if (!empty($answer['correct'])) {
                $correct_answer_texts[] = $answer['text'];
            }
        }
        
        // Get user's answer for this question
        $user_answer_texts = isset($user_answers[$question_id]) ? $user_answers[$question_id] : array();
        if (!is_array($user_answer_texts)) {
            $user_answer_texts = array($user_answer_texts);
        }
        
        // Check if answer is correct
        $is_correct = false;
        if ($question_type === 'single') {
            $is_correct = !empty($user_answer_texts) && in_array($user_answer_texts[0], $correct_answer_texts);
        } else {
            // For multiple choice, all correct answers must be selected and no incorrect ones
            $is_correct = !empty($user_answer_texts) && 
                         count(array_diff($correct_answer_texts, $user_answer_texts)) === 0 && 
                         count(array_diff($user_answer_texts, $correct_answer_texts)) === 0;
        }
        
        if ($is_correct) {
            $score++;
        }
    }
    
    // Save quiz result to database
    $result_id = quiz_system_save_result($quiz_id, $user_name, $user_email, $user_answers, $score, $total_questions);
    
    // Start output buffering to capture the results HTML
    ob_start();
    
    // Display results
    quiz_system_display_results($quiz_id, $user_name, $user_email, $user_answers, $score, $total_questions);
    
    // Get the buffered content
    $results_html = ob_get_clean();
    
    // Send the results HTML back to the client
    wp_send_json_success($results_html);
    die();
}
