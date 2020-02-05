<?php

	// Libraries, functions
		// Import PHPMailer classes into the global namespace
		// These must be at the top of your script, not inside a function
		use PHPMailer\PHPMailer\PHPMailer;
		use PHPMailer\PHPMailer\Exception;
		/* Exception class. */
		require 'php-mailer/Exception.php';
		/* The main PHPMailer class. */
		require 'php-mailer/PHPMailer.php';
		/* SMTP class, needed if you want to use SMTP. */
		require 'php-mailer/SMTP.php';

		function left($str, $length) {
			return substr($str, 0, $length);
		}
		function right($str, $length) {
			return substr($str, -$length);
		}

	// OINP Web page loads the updates from this source:
		$targetAddress = "https://api.ontario.ca/api/drupal/page%2F2020-ontario-immigrant-nominee-program-updates?fields=nid,field_body_beta,body";


	$mailFirstPart = '<p>Hello, </p><p>A change has been detected on the OINP website' .  ' on ' . date('F j, Y') . ' at ' . date('g:i A') . '. You can see the updated list of announcements below.</p><p>Your Change Tracker</p><br><p><a href="https://www.ontarioimmigration.gov.on.ca/oinp_index/resources/app/guest/index.html#!/">Application Page</a> | <a href="https://www.ontario.ca/page/2020-ontario-immigrant-nominee-program-updates">Announcements Page</a> | <a href="https://apps.cansin.net/web-page-tracker/oinp/?read=true">Scraped Content on Cansin.net</a></p><hr>';

	$knownContentFile = "knownContent.txt";
	$knownContent = file_get_contents($knownContentFile);


	// Only here for reading the text file?
	$read = isset($_GET['read']) ? $_GET['read'] : '';
	if ( $read == 'true' ) {
		echo '<p><a href="https://apps.cansin.net/web-page-tracker/oinp/">Check for changes.</a></p>';
		echo $knownContent;
		exit();
	}

	$contentFromAPI = file_get_contents($targetAddress);
	$contentFromAPI = left($contentFromAPI, strlen($contentFromAPI)-5);
	$contentFromAPI = right($contentFromAPI, strlen($contentFromAPI)-118);
	$contentFromAPI = str_replace('\n', "", $contentFromAPI);
	$contentFromAPI = str_replace('\t', "", $contentFromAPI);
	$contentFromAPI = str_replace(urldecode("%5C"), "", $contentFromAPI);
	$contentFromAPI = str_replace('<a href="/', '<a href="https://www.ontario.ca/', $contentFromAPI);
	$contentFromAPI = left($contentFromAPI, strlen($contentFromAPI)/2 + 25);



		echo '<p><a href="https://apps.cansin.net/web-page-tracker/oinp/?read=true">Read the scraped content.</a></p>';

	if ($contentFromAPI == $knownContent) {
		echo "There are no changes.\n";
	} else {
		// Instantiation and passing `true` enables exceptions
		$mail = new PHPMailer(true);

		try {
		    $mail->SMTPDebug = 0;						//  0 = off (for production use)  1 = client messages  2 = client and server messages
		    $mail->isSMTP();							// Set mailer to use SMTP
		    $mail->Host       = 'smtp.X.com';			// Specify main and backup SMTP servers
		    $mail->SMTPAuth   = true;					// Enable SMTP authentication
		    $mail->Username   = 'X@X.net';	// SMTP username
		    $mail->Password   = '';						// SMTP password
		    $mail->SMTPOptions = array(
			    'ssl' => array(
			        'verify_peer' => false,
			        'verify_peer_name' => false,
			        'allow_self_signed' => true
			    )
			);

		    //Recipients
		    $mail->setFrom('webmaster@X.net', 'Change Tracker');
		    $mail->addReplyTo('webmaster@X.net', 'Change Tracker');
			$mail->addAddress('X@X.com', 'Recipient Name');     // Add a recipient
			$mail->addBCC('X@X.com', 'Recipient Name');			// Add a bcc

		    // Content
		    $mail->isHTML(true);                                  // Set email format to HTML
		    $mail->Subject = 'OINP Update Detected';
		    $mail->Body    = $mailFirstPart . $contentFromAPI;
		    //$mail->Body    = $mailFirstPart . str_replace( $knownContent, "", $contentFromAPI); // Only works when the change us only on the right hand side of the string, month name on the top causes all list to be returned.
		    $mail->AltBody = 'alt body';

		    $mail->send();
		    echo "Message has been sent. \n";
		} catch (Exception $e) {
		    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
		}


		// Put what was in new to old, and put what was read to new
		file_put_contents($knownContentFile, $contentFromAPI);
		echo "Changed content written and emailed successfully!\n";
	}



