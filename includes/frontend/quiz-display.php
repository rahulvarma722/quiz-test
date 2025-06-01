<?php
/**
 * Frontend quiz display functions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display quiz
 */
function quiz_system_display_quiz($quiz) {
    // Check if user has already completed this quiz
    $completed = false;
    // if (isset($_COOKIE['quiz_completed_' . $quiz->ID])) {
    //     $completed = true;
    // }
    
    // Get quiz questions
    $question_ids = get_post_meta($quiz->ID, '_quiz_questions', true);
    if (!is_array($question_ids) || empty($question_ids)) {
        echo '<p class="quiz-error">' . __('This quiz has no questions.', 'quiz-system') . '</p>';
        return;
    }
    
    // Get quiz settings
    $time_limit = get_post_meta($quiz->ID, '_quiz_time_limit', true);
    
    // Display quiz
    ?>
    <div class="quiz-container" id="quiz-<?php echo esc_attr($quiz->ID); ?>">
        <h2 class="quiz-title"><?php echo esc_html($quiz->post_title); ?></h2>
        
        <?php if (!empty($quiz->post_content)) : ?>
        <div class="quiz-description">
            <?php echo wp_kses_post($quiz->post_content); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!$completed) : ?>
            <div class="quiz-user-form">
                <h3><?php _e('Please enter your details to start the quiz', 'quiz-system'); ?></h3>
                <form id="quiz-user-details-form">
                    <div class="form-row">
                        <label for="quiz_user_name"><?php _e('Full Name', 'quiz-system'); ?> <span class="required">*</span></label>
                        <input type="text" id="quiz_user_name" name="quiz_user_name" required>
                    </div>
                    <div class="form-row">
                        <label for="quiz_user_email"><?php _e('Email Address', 'quiz-system'); ?> <span class="required">*</span></label>
                        <input type="email" id="quiz_user_email" name="quiz_user_email" required>
                    </div>
                    <div class="form-row">
                        <button type="submit" class="button start-quiz-button"><?php _e('Start Quiz', 'quiz-system'); ?></button>
                    </div>
                </form>
            </div>
            
            <div class="quiz-questions-container" style="display: none;">
                <?php if (!empty($time_limit)) : ?>
                <div class="quiz-timer">
                    <span class="timer-label"><?php _e('Time Remaining:', 'quiz-system'); ?></span>
                    <span class="timer-value" data-minutes="<?php echo esc_attr($time_limit); ?>">
                        <?php echo esc_html($time_limit . ':00'); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <form method="post" class="quiz-form">
                    <input type="hidden" name="quiz_id" value="<?php echo esc_attr($quiz->ID); ?>">
                    <input type="hidden" name="quiz_user_name" id="hidden_quiz_user_name" value="">
                    <input type="hidden" name="quiz_user_email" id="hidden_quiz_user_email" value="">
                    
                    <?php
                    // Display questions
                    $question_number = 1;
                    foreach ($question_ids as $question_id) {
                        $question = get_post($question_id);
                        if (!$question) {
                            continue;
                        }
                        
                        // Get question type and answers
                        $question_type = get_post_meta($question_id, '_question_type', true);
                        $answers = get_post_meta($question_id, '_question_answers', true);
                        
                        if (!is_array($answers) || empty($answers)) {
                            continue;
                        }
                        
                        // Shuffle answers
                        shuffle($answers);
                        
                        // Input type based on question type
                        $input_type = ($question_type === 'multiple') ? 'checkbox' : 'radio';
                        ?>
                        <div class="quiz-question" id="question-<?php echo esc_attr($question_id); ?>">
                            <h3 class="question-title">
                                <span class="question-number"><?php echo esc_html($question_number); ?>.</span>
                                <?php echo esc_html($question->post_title); ?>
                            </h3>
                            
                            <?php if (!empty($question->post_content)) : ?>
                            <div class="question-description">
                                <?php echo wp_kses_post($question->post_content); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="question-answers">
                                <?php foreach ($answers as $index => $answer) : ?>
                                <div class="answer-option">
                                    <label>
                                        <input type="<?php echo esc_attr($input_type); ?>" 
                                               name="quiz_answers[<?php echo esc_attr($question_id); ?>]<?php echo $input_type === 'checkbox' ? '[]' : ''; ?>" 
                                               value="<?php echo esc_attr($index); ?>">
                                        <?php echo esc_html($answer['text']); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php
                        $question_number++;
                    }
                    ?>
                    
                    <div class="quiz-submit">
                        <button type="submit" name="quiz_submit" class="button submit-quiz-button"><?php _e('Submit Answers', 'quiz-system'); ?></button>
                    </div>
                </form>
            </div>
        <?php else : ?>
            <p class="quiz-completed-message">
                <?php _e('You have already completed this quiz.', 'quiz-system'); ?>
            </p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Display quiz results
 */
function quiz_system_display_results($quiz_id, $user_name, $user_email, $user_answers, $score, $total_questions) {
    // Get quiz
    $quiz = get_post($quiz_id);
    if (!$quiz) {
        return;
    }
    
    // Get pass mark
    $pass_mark = get_post_meta($quiz_id, '_quiz_pass_mark', true);
    $passed = !empty($pass_mark) && ($score / $total_questions * 100) >= $pass_mark;
    
    // Get questions
    $question_ids = get_post_meta($quiz_id, '_quiz_questions', true);
    ?>
    <div class="quiz-results">
        <h2><?php _e('Quiz Results', 'quiz-system'); ?></h2>
        
        <div class="quiz-user-info">
            <p><strong><?php _e('Name:', 'quiz-system'); ?></strong> <?php echo esc_html($user_name); ?></p>
            <p><strong><?php _e('Email:', 'quiz-system'); ?></strong> <?php echo esc_html($user_email); ?></p>
        </div>
        
        <div class="quiz-score">
            <p class="score-text">
                <?php 
                printf(
                    __('You scored %1$d out of %2$d (%3$d%%)', 'quiz-system'),
                    $score,
                    $total_questions,
                    round(($score / $total_questions) * 100)
                ); 
                ?>
            </p>
            
            <?php if (!empty($pass_mark)) : ?>
            <p class="pass-fail <?php echo $passed ? 'passed' : 'failed'; ?>">
                <?php 
                if ($passed) {
                    _e('Congratulations! You passed the quiz.', 'quiz-system');
                } else {
                    printf(
                        __('Sorry, you did not pass the quiz. The pass mark is %d%%.', 'quiz-system'),
                        $pass_mark
                    );
                }
                ?>
            </p>
            <?php endif; ?>
        </div>
        
        <div class="quiz-answers-review">
            <h3><?php _e('Review Your Answers', 'quiz-system'); ?></h3>
            
            <?php
            $question_number = 1;
            foreach ($question_ids as $question_id) {
                $question = get_post($question_id);
                if (!$question) {
                    continue;
                }
                
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
                $is_correct = true;
                if ($question_type === 'single') {
                    $is_correct = in_array($user_answer[0], $correct_answers);
                } else {
                    // For multiple choice, all correct answers must be selected and no incorrect ones
                    $is_correct = count(array_diff($correct_answers, $user_answer)) === 0 && 
                                 count(array_diff($user_answer, $correct_answers)) === 0;
                }
                ?>
                <div class="question-review <?php echo $is_correct ? 'correct' : 'incorrect'; ?>">
                    <h4>
                        <span class="question-number"><?php echo esc_html($question_number); ?>.</span>
                        <?php echo esc_html($question->post_title); ?>
                        <span class="result-indicator"><?php echo $is_correct ? '✓' : '✗'; ?></span>
                    </h4>
                    
                    <div class="answer-review">
                        <p><strong><?php _e('Your Answer:', 'quiz-system'); ?></strong></p>
                        <ul>
                            <?php 
                            foreach ($user_answer as $index) {
                                if (isset($answers[$index])) {
                                    echo '<li>' . esc_html($answers[$index]['text']) . '</li>';
                                }
                            }
                            
                            if (empty($user_answer)) {
                                echo '<li>' . __('No answer provided', 'quiz-system') . '</li>';
                            }
                            ?>
                        </ul>
                        
                        <?php if (!$is_correct) : ?>
                        <p><strong><?php _e('Correct Answer:', 'quiz-system'); ?></strong></p>
                        <ul>
                            <?php 
                            foreach ($correct_answers as $index) {
                                if (isset($answers[$index])) {
                                    echo '<li>' . esc_html($answers[$index]['text']) . '</li>';
                                }
                            }
                            ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
                $question_number++;
            }
            ?>
        </div>
        
        <div class="quiz-actions">
            <a href="<?php echo esc_url(get_permalink($quiz_id)); ?>" class="button"><?php _e('Back to Quiz', 'quiz-system'); ?></a>
        </div>
    </div>
    <?php
    
    // Set cookie to mark quiz as completed
    setcookie('quiz_completed_' . $quiz_id, '1', time() + (86400 * 30), '/'); // 30 days
}