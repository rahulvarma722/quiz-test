jQuery(document).ready(function($) {
    // Start quiz when user submits their details
    $('#quiz-user-details-form').on('submit', function(e) {
        e.preventDefault();
        
        // Get user details
        var userName = $('#quiz_user_name').val();
        var userEmail = $('#quiz_user_email').val();
        
        // Validate user details
        if (!userName || !userEmail) {
            alert(quiz_ajax_vars.error_required_fields);
            return;
        }
        
        // Store user details in hidden fields
        $('#hidden_quiz_user_name').val(userName);
        $('#hidden_quiz_user_email').val(userEmail);
        
        // Hide user form and show quiz
        $('.quiz-user-form').hide();
        $('.quiz-questions-container').show();
        
        // Start timer if exists
        startQuizTimer();
    });
    
    // Handle quiz submission
    $('.quiz-form').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading indicator
        showLoading();
        
        // Get form data
        var formData = $(this).serialize();
        
        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: quiz_ajax_vars.ajax_url,
            data: {
                action: 'quiz_submit',
                nonce: quiz_ajax_vars.nonce,
                form_data: formData
            },
            success: function(response) {
                // Hide loading indicator
                hideLoading();
                
                if (response.success) {
                    // Replace quiz with results
                    $('.quiz-questions-container').html(response.data);
                    
                    // Add event listener for "Take Quiz Again" button
                    $(document).on('click', '.quiz-actions .button', function(e) {
                        e.preventDefault();
                        
                        // Reload the page to reset the quiz
                        location.reload();
                    });
                } else {
                    // Show error message
                    alert(response.data || quiz_ajax_vars.error_submission);
                }
                
                // Stop timer if exists
                stopQuizTimer();
            },
            error: function() {
                // Hide loading indicator
                hideLoading();
                
                // Show error message
                alert(quiz_ajax_vars.error_submission);
            }
        });
    });
    
    // Timer functionality
    var timerInterval;
    
    function startQuizTimer() {
        var timerElement = $('.timer-value');
        if (timerElement.length === 0) return;
        
        var minutes = parseInt(timerElement.data('minutes'));
        var totalSeconds = minutes * 60;
        
        updateTimerDisplay(totalSeconds);
        
        timerInterval = setInterval(function() {
            totalSeconds--;
            
            if (totalSeconds <= 0) {
                // Time's up - submit the quiz
                clearInterval(timerInterval);
                $('.quiz-form').submit();
                return;
            }
            
            updateTimerDisplay(totalSeconds);
        }, 1000);
    }
    
    function stopQuizTimer() {
        if (timerInterval) {
            clearInterval(timerInterval);
        }
    }
    
    function updateTimerDisplay(totalSeconds) {
        var minutes = Math.floor(totalSeconds / 60);
        var seconds = totalSeconds % 60;
        
        // Format with leading zeros
        var displayMinutes = (minutes < 10 ? '0' : '') + minutes;
        var displaySeconds = (seconds < 10 ? '0' : '') + seconds;
        
        $('.timer-value').text(displayMinutes + ':' + displaySeconds);
        
        // Add warning class when time is running out
        if (totalSeconds < 60) {
            $('.timer-value').addClass('timer-warning');
        }
    }
    
    // Helper functions
    function showLoading() {
        // Create loading overlay if it doesn't exist
        if ($('#quiz-loading-overlay').length === 0) {
            $('body').append('<div id="quiz-loading-overlay"><div class="quiz-loading-spinner"></div></div>');
        }
        
        $('#quiz-loading-overlay').show();
    }
    
    function hideLoading() {
        $('#quiz-loading-overlay').hide();
    }
});
