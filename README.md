# Quiz System

A custom WordPress plugin that adds a quiz system to your website.

## Features

- Create quizzes with multiple questions
- Support for single-choice and multiple-choice questions
- User registration before taking quizzes
- Quiz timer option
- Results display with correct answers
- Score tracking

## Usage

### Creating a Quiz

1. Go to WordPress admin panel
2. Navigate to "Quizzes" > "Add New"
3. Enter a title and description for your quiz
4. Set quiz options (time limit, pass mark)
5. Add questions to the quiz
6. Publish the quiz

### Creating Questions

1. Go to WordPress admin panel
2. Navigate to "Quizzes" > "Questions" > "Add New"
3. Enter the question text
4. Select question type (single or multiple choice)
5. Add answer options and mark the correct answer(s)
6. Publish the question

### Displaying a Quiz on Your Site

Use the shortcode with the quiz ID:

```
[my_quiz id="123"]
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher

## Installation

1. Upload the `quiz-system` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Start creating quizzes and questions

## License

GPL v2 or later