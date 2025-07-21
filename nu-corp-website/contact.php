<?php
// Contact form handler for Nu Corp website
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$to_email = "info@nucorp.co.za";
$from_email = "noreply@nucorp.co.za"; // Change this to your domain email

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// Get and sanitize form data
$data = json_decode(file_get_contents('php://input'), true);

$name = isset($data['name']) ? htmlspecialchars(strip_tags(trim($data['name']))) : '';
$email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
$phone = isset($data['phone']) ? htmlspecialchars(strip_tags(trim($data['phone']))) : '';
$website = isset($data['website']) ? htmlspecialchars(strip_tags(trim($data['website']))) : '';
$location = isset($data['location']) ? htmlspecialchars(strip_tags(trim($data['location']))) : '';
$message = isset($data['message']) ? htmlspecialchars(strip_tags(trim($data['message']))) : '';
$subject = 'Website Contact Form Submission';

// Validate required fields
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(["error" => "Please fill in all required fields"]);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email address"]);
    exit;
}

// Prepare email content
$email_subject = "Nu Corp Contact Form: " . $subject;
$email_body = "You have received a new message from the Nu Corp website contact form.\n\n";
$email_body .= "Contact Details:\n";
$email_body .= "Name: $name\n";
$email_body .= "Email: $email\n";
$email_body .= "Phone: " . ($phone ? $phone : "Not provided") . "\n";
$email_body .= "Website: " . ($website ? $website : "Not provided") . "\n";
$email_body .= "Location: " . ($location ? $location : "Not provided") . "\n\n";
$email_body .= "Message:\n$message\n";

// Email headers
$headers = "From: $from_email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
if (mail($to_email, $email_subject, $email_body, $headers)) {
    // Send copy to leadership if specific keywords are found
    $leadership_keywords = ['partnership', 'investment', 'collaboration', 'project'];
    $send_to_leadership = false;
    
    foreach ($leadership_keywords as $keyword) {
        if (stripos($subject, $keyword) !== false || stripos($message, $keyword) !== false) {
            $send_to_leadership = true;
            break;
        }
    }
    
    if ($send_to_leadership) {
        // Send copies to leadership
        mail("gnijoma@nucorp.co.za", $email_subject, $email_body, $headers);
        mail("ndbricknell@nucorp.co.za", $email_subject, $email_body, $headers);
    }
    
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Thank you for your message. We will get back to you soon!"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to send email. Please try again later."]);
}
?>