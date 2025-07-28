<?php
// Configuration
$recipient_email = "contact@anummian.co.uk";
$email_subject = "New Website Contact Form Submission";

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get and sanitize form data
    $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
    $app_name = isset($_POST['app_name']) ? sanitize_input($_POST['app_name']) : 'Not specified';
    
    // Validate required fields
    $errors = array();
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no errors, proceed with sending email
    if (empty($errors)) {
        // Build email content with HTML formatting
        $email_content = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                h1 { color: #2563eb; }
                .info { margin-bottom: 20px; }
                .label { font-weight: bold; }
                .message-box { background-color: #f9fafb; padding: 15px; border-radius: 5px; border-left: 4px solid #2563eb; }
                .app-badge { background-color: #e0f2fe; color: #0369a1; padding: 5px 10px; border-radius: 15px; display: inline-block; font-size: 14px; margin-bottom: 15px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>New Contact Form Submission</h1>
                " . ($app_name !== 'Not specified' ? "<div class='app-badge'>App: {$app_name}</div>" : "") . "
                <div class='info'>
                    <p><span class='label'>Name:</span> {$name}</p>
                    <p><span class='label'>Email:</span> {$email}</p>
                    <p><span class='label'>Subject:</span> {$subject}</p>
                    <p><span class='label'>App:</span> {$app_name}</p>
                </div>
                <div class='message-box'>
                    <p><span class='label'>Message:</span></p>
                    <p>" . nl2br($message) . "</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Email headers
        $headers = "From: {$name} <{$email}>\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        // Update email subject to include app name if provided
        $email_subject_with_app = $app_name !== 'Not specified' ? 
            "{$email_subject}: {$subject} - {$app_name}" : 
            "{$email_subject}: {$subject}";
        
        // Attempt to send email
        $email_sent = mail($recipient_email, $email_subject_with_app, $email_content, $headers);
        
        // Handle response
        header('Content-Type: application/json');
        
        if ($email_sent) {
            echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Sorry, there was an error sending your message. Please try again later.']);
        }
    } else {
        // Return validation errors
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please fix the following errors:', 'errors' => $errors]);
    }
} else {
    // Not a POST request
    header('HTTP/1.1 403 Forbidden');
    echo "Access forbidden";
}
?> 