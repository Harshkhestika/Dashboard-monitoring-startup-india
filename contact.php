<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = htmlspecialchars($_POST["name"]);
    $email   = htmlspecialchars($_POST["email"]);
    $subject = htmlspecialchars($_POST["subject"]);
    $message = nl2br(htmlspecialchars($_POST["message"]));

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';          // 🔁 Replace with your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'hkhestika@gmail.com';    // 🔁 Replace with your SMTP username
        $mail->Password   = 'erijsrkrkdeyzqbr';             // 🔁 Replace with your SMTP password
        $mail->SMTPSecure = 'ssl';                       // or 'ssl'
        $mail->Port       = 465;                         // 465 for ssl

        // Recipients
        // $mail->setFrom($email, $name);
        $mail->setFrom('hkhestika@gmail.com', 'Harsh'); // 🔁 Replace with your own email
        $mail->addAddress($email, $name); // 🔁 Replace with your own email

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Contact Form: $subject";
        $mail->Body = "
            <h2 style='color:#2e6da4;'>🎉 Congratulations, $name!</h2>
            <p>Welcome to our website. We're excited to have you on board.</p>
            <p>Your account has been successfully created with the email: <strong>$email</strong>.</p>
            <p>If you have any questions or need help, feel free to reach out to us anytime.</p>
            <br>
            <p>Cheers,<br>The Team</p>
        ";

        $mail->send();
        $success = "Thanks for your message! We'll get back to you soon.";
    } catch (Exception $e) {
        $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    .hero-pattern {
      background-image: radial-gradient(#3b82f6 1px, transparent 1px);
      background-size: 20px 20px;
    }
    .map-container {
      height: 300px;
    }
    @media (min-width: 768px) {
      .map-container {
        height: 400px;
      }
    }
  </style>
</head>
<body class="font-sans bg-gray-50">

<section class="py-16 bg-white">
  <div class="container mx-auto px-6">
    <div class="flex flex-col md:flex-row">
      <div class="md:w-1/2 mb-10 md:mb-0 md:pr-10">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Send Us a Message</h2>

        <?php if ($success): ?>
          <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg">
            <?php echo $success; ?>
          </div>
        <?php elseif ($error): ?>
          <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">
            <?php echo $error; ?>
          </div>
        <?php endif; ?>

        <form class="space-y-6" method="POST" action="">
          <div>
            <label for="name" class="block text-gray-700 mb-2">Your Name</label>
            <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label for="email" class="block text-gray-700 mb-2">Email Address</label>
            <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label for="subject" class="block text-gray-700 mb-2">Subject</label>
            <select id="subject" name="subject" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="feedback">Feedback</option>
              <option value="support">Technical Support</option>
              <option value="suggestion">Feature Suggestion</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div>
            <label for="message" class="block text-gray-700 mb-2">Your Message</label>
            <textarea id="message" name="message" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          </div>
          <button type="submit" class="bg-blue-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-300 w-full md:w-auto">
            Send Message
          </button>
        </form>
      </div>

      <div class="md:w-1/2 md:pl-10">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Other Ways to Connect</h2>

        <div class="space-y-6 mb-10">
          <div class="flex items-start">
            <div class="bg-blue-100 p-3 rounded-full mr-4">
              <i class="fas fa-envelope text-blue-600"></i>
            </div>
            <div>
              <h3 class="text-xl font-semibold mb-1">Email Us</h3>
              <p class="text-gray-600">contact@habithero.app</p>
              <p class="text-gray-500 text-sm">(We check daily)</p>
            </div>
          </div>
          <div class="flex items-start">
            <div class="bg-green-100 p-3 rounded-full mr-4">
              <i class="fab fa-twitter text-green-600"></i>
            </div>
            <div>
              <h3 class="text-xl font-semibold mb-1">Tweet Us</h3>
              <p class="text-gray-600">@HabitBuddyApp</p>
              <p class="text-gray-500 text-sm">(Fastest way to get a response)</p>
            </div>
          </div>
          <div class="flex items-start">
            <div class="bg-purple-100 p-3 rounded-full mr-4">
              <i class="fab fa-github text-purple-600"></i>
            </div>
            <div>
              <h3 class="text-xl font-semibold mb-1">Contribute</h3>
              <p class="text-gray-600">github.com/habitbuddy</p>
              <p class="text-gray-500 text-sm">(Open to contributors)</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

</body>
</html>
