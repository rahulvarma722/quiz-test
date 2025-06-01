<?php
/**
 * Register custom post types for quizzes and questions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom post types
 */
function quiz_system_register_post_types() {
    // Register Quiz post type
    register_post_type('quiz', array(
        'labels' => array(
            'name'               => __('Quizzes', 'quiz-system'),
            'singular_name'      => __('Quiz', 'quiz-system'),
            'add_new'            => __('Add New', 'quiz-system'),
            'add_new_item'       => __('Add New Quiz', 'quiz-system'),
            'edit_item'          => __('Edit Quiz', 'quiz-system'),
            'new_item'           => __('New Quiz', 'quiz-system'),
            'view_item'          => __('View Quiz', 'quiz-system'),
            'search_items'       => __('Search Quizzes', 'quiz-system'),
            'not_found'          => __('No quizzes found', 'quiz-system'),
            'not_found_in_trash' => __('No quizzes found in trash', 'quiz-system'),
            'menu_name'          => __('Quizzes', 'quiz-system'),
        ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'quiz'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-welcome-learn-more',
        'supports'           => array('title', 'editor', 'thumbnail'),
    ));

    // Register Question post type
    register_post_type('quiz_question', array(
        'labels' => array(
            'name'               => __('Questions', 'quiz-system'),
            'singular_name'      => __('Question', 'quiz-system'),
            'add_new'            => __('Add New', 'quiz-system'),
            'add_new_item'       => __('Add New Question', 'quiz-system'),
            'edit_item'          => __('Edit Question', 'quiz-system'),
            'new_item'           => __('New Question', 'quiz-system'),
            'view_item'          => __('View Question', 'quiz-system'),
            'search_items'       => __('Search Questions', 'quiz-system'),
            'not_found'          => __('No questions found', 'quiz-system'),
            'not_found_in_trash' => __('No questions found in trash', 'quiz-system'),
            'menu_name'          => __('Questions', 'quiz-system'),
        ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=quiz',
        'query_var'          => true,
        'rewrite'            => array('slug' => 'quiz-question'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'supports'           => array('title', 'editor'),
    ));
}
add_action('init', 'quiz_system_register_post_types');

/**
 * Add custom meta boxes for quizzes and questions
 */
function quiz_system_add_meta_boxes() {
    // Add meta box for quiz settings
    add_meta_box(
        'quiz_settings',
        __('Quiz Settings', 'quiz-system'),
        'quiz_system_quiz_settings_callback',
        'quiz',
        'normal',
        'high'
    );

    // Add meta box for question settings
    add_meta_box(
        'question_settings',
        __('Question Settings', 'quiz-system'),
        'quiz_system_question_settings_callback',
        'quiz_question',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'quiz_system_add_meta_boxes');