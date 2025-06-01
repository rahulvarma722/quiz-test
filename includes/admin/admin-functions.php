<?php
/**
 * Admin functions for the Quiz System plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Quiz settings meta box callback
 */
function quiz_system_quiz_settings_callback($post) {
    // Add nonce for security
    wp_nonce_field('quiz_system_save_quiz_settings', 'quiz_system_quiz_settings_nonce');

    // Get saved values
    $quiz_time_limit = get_post_meta($post->ID, '_quiz_time_limit', true);
    $quiz_pass_mark = get_post_meta($post->ID, '_quiz_pass_mark', true);
    
    // Output fields
    ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="quiz_time_limit"><?php _e('Time Limit (minutes)', 'quiz-system'); ?></label>
            </th>
            <td>
                <input type="number" id="quiz_time_limit" name="quiz_time_limit" value="<?php echo esc_attr($quiz_time_limit); ?>" min="0">
                <p class="description"><?php _e('Leave empty for no time limit', 'quiz-system'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="quiz_pass_mark"><?php _e('Pass Mark (%)', 'quiz-system'); ?></label>
            </th>
            <td>
                <input type="number" id="quiz_pass_mark" name="quiz_pass_mark" value="<?php echo esc_attr($quiz_pass_mark); ?>" min="0" max="100">
            </td>
        </tr>
    </table>
    
    <div class="quiz-questions">
        <h3><?php _e('Quiz Questions', 'quiz-system'); ?></h3>
        <p><?php _e('Assign questions to this quiz:', 'quiz-system'); ?></p>
        
        <?php
        // Get currently assigned questions
        $assigned_questions = get_post_meta($post->ID, '_quiz_questions', true);
        if (!is_array($assigned_questions)) {
            $assigned_questions = array();
        }
        
        // Get all questions
        $questions = get_posts(array(
            'post_type' => 'quiz_question',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
        
        if ($questions) {
            echo '<ul class="quiz-question-list">';
            foreach ($questions as $question) {
                $checked = in_array($question->ID, $assigned_questions) ? 'checked="checked"' : '';
                echo '<li>';
                echo '<label>';
                echo '<input type="checkbox" name="quiz_questions[]" value="' . esc_attr($question->ID) . '" ' . $checked . '>';
                echo esc_html($question->post_title);
                echo '</label>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>' . __('No questions found. Create some questions first.', 'quiz-system') . '</p>';
        }
        ?>
    </div>
    <?php
}

/**
 * Question settings meta box callback
 */
function quiz_system_question_settings_callback($post) {
    // Add nonce for security
    wp_nonce_field('quiz_system_save_question_settings', 'quiz_system_question_settings_nonce');

    // Get saved values
    $question_type = get_post_meta($post->ID, '_question_type', true);
    if (empty($question_type)) {
        $question_type = 'single'; // Default to single choice
    }
    
    $answers = get_post_meta($post->ID, '_question_answers', true);
    if (!is_array($answers)) {
        $answers = array(
            array('text' => '', 'correct' => 0),
            array('text' => '', 'correct' => 0),
            array('text' => '', 'correct' => 0),
            array('text' => '', 'correct' => 0),
        );
    }
    
    // Output fields
    ?>
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Question Type', 'quiz-system'); ?></th>
            <td>
                <label>
                    <input type="radio" name="question_type" value="single" <?php checked($question_type, 'single'); ?>>
                    <?php _e('Single Choice', 'quiz-system'); ?>
                </label>
                <br>
                <label>
                    <input type="radio" name="question_type" value="multiple" <?php checked($question_type, 'multiple'); ?>>
                    <?php _e('Multiple Choice', 'quiz-system'); ?>
                </label>
            </td>
        </tr>
    </table>
    
    <div class="question-answers">
        <h3><?php _e('Answers', 'quiz-system'); ?></h3>
        <table class="widefat" id="question-answers-table">
            <thead>
                <tr>
                    <th><?php _e('Answer Text', 'quiz-system'); ?></th>
                    <th><?php _e('Correct?', 'quiz-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($answers as $index => $answer) : ?>
                <tr>
                    <td>
                        <input type="text" name="question_answers[<?php echo $index; ?>][text]" value="<?php echo esc_attr($answer['text']); ?>" class="widefat">
                    </td>
                    <td>
                        <input type="checkbox" name="question_answers[<?php echo $index; ?>][correct]" value="1" <?php checked(!empty($answer['correct'])); ?>>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            <button type="button" class="button add-answer"><?php _e('Add Answer', 'quiz-system'); ?></button>
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Toggle between single and multiple choice
        $('input[name="question_type"]').on('change', function() {
            var type = $(this).val();
            if (type === 'single') {
                // Ensure only one answer can be marked as correct
                $('#question-answers-table').on('change', 'input[type="checkbox"]', function() {
                    if ($(this).is(':checked')) {
                        $('#question-answers-table input[type="checkbox"]').not(this).prop('checked', false);
                    }
                });
            }
        });
        
        // Trigger change event to initialize
        $('input[name="question_type"]:checked').trigger('change');
        
        // Add new answer row
        $('.add-answer').on('click', function() {
            var index = $('#question-answers-table tbody tr').length;
            var newRow = '<tr>' +
                '<td><input type="text" name="question_answers[' + index + '][text]" value="" class="widefat"></td>' +
                '<td><input type="checkbox" name="question_answers[' + index + '][correct]" value="1"></td>' +
                '</tr>';
            $('#question-answers-table tbody').append(newRow);
        });
    });
    </script>
    <?php
}

/**
 * Save quiz settings
 */
function quiz_system_save_quiz_settings($post_id) {
    // Check if nonce is set
    if (!isset($_POST['quiz_system_quiz_settings_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['quiz_system_quiz_settings_nonce'], 'quiz_system_save_quiz_settings')) {
        return;
    }

    // Check if autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if ('quiz' !== $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save time limit
    if (isset($_POST['quiz_time_limit'])) {
        update_post_meta($post_id, '_quiz_time_limit', sanitize_text_field($_POST['quiz_time_limit']));
    }

    // Save pass mark
    if (isset($_POST['quiz_pass_mark'])) {
        update_post_meta($post_id, '_quiz_pass_mark', sanitize_text_field($_POST['quiz_pass_mark']));
    }

    // Save assigned questions
    $questions = isset($_POST['quiz_questions']) ? array_map('absint', $_POST['quiz_questions']) : array();
    update_post_meta($post_id, '_quiz_questions', $questions);
}
add_action('save_post_quiz', 'quiz_system_save_quiz_settings');

/**
 * Save question settings
 */
function quiz_system_save_question_settings($post_id) {
    // Check if nonce is set
    if (!isset($_POST['quiz_system_question_settings_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['quiz_system_question_settings_nonce'], 'quiz_system_save_question_settings')) {
        return;
    }

    // Check if autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if ('quiz_question' !== $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save question type
    if (isset($_POST['question_type'])) {
        update_post_meta($post_id, '_question_type', sanitize_text_field($_POST['question_type']));
    }

    // Save answers
    if (isset($_POST['question_answers']) && is_array($_POST['question_answers'])) {
        $answers = array();
        foreach ($_POST['question_answers'] as $answer) {
            if (!empty($answer['text'])) {
                $answers[] = array(
                    'text' => sanitize_text_field($answer['text']),
                    'correct' => isset($answer['correct']) ? 1 : 0,
                );
            }
        }
        update_post_meta($post_id, '_question_answers', $answers);
    }
}
add_action('save_post_quiz_question', 'quiz_system_save_question_settings');