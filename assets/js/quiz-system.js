/**
 * Quiz System JavaScript
 */
(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        // User form submission
        $('#quiz-user-details-form').on('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            var userName = $('#quiz_user_name').val().trim();
            var userEmail = $('#quiz_user_email').val().trim();
            var isValid = true;
            
            if (userName === '') {
                isValid = false;
                $('#quiz_user_name').addClass('error');
                alert(quizSystemData.i18n.required);
            } else {
                $('#quiz_user_name').removeClass('error');
            }
            
            if (userEmail === '' || !isValidEmail(userEmail)) {
                isValid = false;
                $('#quiz_user_email').addClass('error');
                alert(quizSystemData.i18n.validEmail);
            } else {
                $('#quiz_user_email').removeClass('error');
            }
            
            if (isValid) {
                // Transfer values to hidden fields
                $('#hidden_quiz_user_name').val(userName);
                $('#hidden_quiz_user_email').val(userEmail);
                
                // Hide user form and show quiz
                $('.quiz-user-form').hide();
                $('.quiz-questions-container').show();
                
                // Start timer if needed
                startQuizTimer();
            }
        });
        
        // Quiz form submission
        $('.quiz-form').on('submit', function(e) {
            if (!confirm(quizSystemData.i18n.confirmSubmit)) {
                e.preventDefault();
            }
        });
    });
    
    // Start quiz timer
    function startQuizTimer() {
        var timerElement = $('.timer-value');
        if (timerElement.length === 0) {
            return;
        }
        
        var minutes = parseInt(timerElement.data('minutes'), 10);
        if (isNaN(minutes) || minutes <= 0) {
            return;
        }
        
        var totalSeconds = minutes * 60;
        var timerInterval = setInterval(function() {
            totalSeconds--;
            
            if (totalSeconds <= 0) {
                clearInterval(timerInterval);
                alert(quizSystemData.i18n.timeUp);
                $('.quiz-form').submit();
                return;
            }
            
            var minutesLeft = Math.floor(totalSeconds / 60);
            var secondsLeft = totalSeconds % 60;
            
            timerElement.text(
                minutesLeft + ':' + (secondsLeft < 10 ? '0' : '') + secondsLeft
            );
        }, 1000);
    }
    
    // Validate email
    function isValidEmail(email) {
        var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(email);
    }
    
})(jQuery);