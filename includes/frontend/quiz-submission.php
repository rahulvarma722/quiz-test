<?php
/**
 * Frontend quiz submission handling
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Process quiz submission
 */
function quiz_system_process_submission() {
    // Verify nonce
    if (!isset($_POST['quiz_id'])) {
        echo '<p class="quiz-error">' . __('Error: Invalid submission.', 'quiz-system') . '</p>';
        return;
    }
    
    // Get quiz ID
    $quiz_id = absint($_POST['quiz_id']);
    
    // Get user details
    $user_name = isset($_POST['quiz_user_name']) ? sanitize_text_field($_POST['quiz_user_name']) : '';
    $user_email = isset($_POST['quiz_user_email']) ? sanitize_email($_POST['quiz_user_email']) : '';
    
    // Validate user details
    if (empty($user_name) || empty($user_email)) {
        echo '<p class="quiz-error">' . __('Error: Name and email are required.', 'quiz-system') . '</p>';
        return;
    }
    
    // Get quiz questions
    $question_ids = get_post_meta($quiz_id, '_quiz_questions', true);
    if (!is_array($question_ids) || empty($question_ids)) {
        echo '<p class="quiz-error">' . __('Error: This quiz has no questions.', 'quiz-system') . '</p>';
        return;
    }
    
    // Get user answers
    $user_answers = isset($_POST['quiz_answers']) ? $_POST['quiz_answers'] : array();
    
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
        
        // Get correct answers
        $correct_answers = array();
        foreach ($answers as $index => $answer) {
            if (!empty($answer['correct'])) {
                $correct_answers[] = $index;
            }
        }
        
        // Get user's answer for this question
        $user_answer = isset($user_answers[$question_id]) ? $user_answers[$question_id] : array();
        if (!is_array($user_answer)) {
            $user_answer = array($user_answer);
        }
        
        // Check if answer is correct
        $is_correct = false;
        if ($question_type === 'single') {
            $is_correct = !empty($user_answer) && in_array($user_answer[0], $correct_answers);
        } else {
            // For multiple choice, all correct answers must be selected and no incorrect ones
            $is_correct = !empty($user_answer) && 
                         count(array_diff($correct_answers, $user_answer)) === 0 && 
                         count(array_diff($user_answer, $correct_answers)) === 0;
        }
        
        if ($is_correct) {
            $score++;
        }
    }
    
    // Save quiz result to database
    $result_id = quiz_system_save_result($quiz_id, $user_name, $user_email, $user_answers, $score, $total_questions);
    
    // Display results
    quiz_system_display_results($quiz_id, $user_name, $user_email, $user_answers, $score, $total_questions);
}

/**
 * Save quiz result to database
 */
function quiz_system_save_result($quiz_id, $user_name, $user_email, $user_answers, $score, $total_questions) {
    global $wpdb;
    
    // Create results table if it doesn't exist
    quiz_system_create_results_table();
    
    // Insert result
    $wpdb->insert(
        $wpdb->prefix . 'quiz_results',
        array(
            'quiz_id' => $quiz_id,
            'user_name' => $user_name,
            'user_email' => $user_email,
            'user_answers' => maybe_serialize($user_answers),
            'score' => $score,
            'total_questions' => $total_questions,
            'date_completed' => current_time('mysql'),
        ),
        array('%d', '%s', '%s', '%s', '%d', '%d', '%s')
    );
    
    return $wpdb->insert_id;
}

/**
 * Create results table
 */
function quiz_system_create_results_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'quiz_results';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            quiz_id bigint(20) NOT NULL,
            user_name varchar(100) NOT NULL,
            user_email varchar(100) NOT NULL,
            user_answers longtext NOT NULL,
            score int(11) NOT NULL,
            total_questions int(11) NOT NULL,
            date_completed datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}