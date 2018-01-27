<?php

  $rawJsonData = $HTTP_RAW_POST_DATA;
  $decodedData = json_decode($rawJsonData, true);
  
  $name = $decodedData['name']; // required
  $email_from = $decodedData['email']; // required
  $website = $decodedData['website']; // not required
  $message = $decodedData['message']; // required

  $response = array();
  $errors = array();

  // validation expected data exists
  function is_provided($value) {
    return isset($value) && count(trim($value));
  }
  function die_if_errors($errors) {
    if ( !empty($errors) ) {
      die(
        json_encode( 
            array(
              'success' => false
              , 'message' => 'Your form is invalid! Please try fixing the erros listed.'
              , 'errors' => $errors
            )
        )
      );
    }
  }

  // REQUIRED validation
  $requiredMsg = 'Required field';
  if ( !is_provided($name) ) {
    $errors['name'] = $requiredMsg;
  }
  if ( !is_provided($email_from) ) {
    $errors['email'] = $requiredMsg;
  }
  if ( !is_provided($message) ) {
    $errors['message'] = $requiredMsg;
  }
  die_if_errors($errors);
  
  // CORRECT FORMAT validation
  $email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
  if(!preg_match($email_exp,$email_from)) {
    $errors['email'] = 'Invalid email';
  }  
  $string_exp = "/^[A-Za-z .'-]+$/";
  if(!preg_match($string_exp,$name)) {
    $errors['name'] = 'Inalid name';
  }
  if(strlen($message) < 5) {
    $errors['message'] = 'Invalid message';
  }
  die_if_errors($errors);
  
  // CONSTRUCTING MESSAGE
  function clean_string($string) {
    $bad = array("content-type","bcc:","to:","cc:","href");
    return str_replace($bad,"",$string);
  }
  $email_message = "Form details below.\n\n";     
  $email_message .= "Name: ".clean_string($name)."\n";
  $email_message .= "Email: ".clean_string($email_from)."\n";
  $email_message .= "website: ".clean_string($website)."\n";
  $email_message .= "message: ".clean_string($message)."\n";
   
   
  // create email headers
  $headers = 'From: '.$email_from."\r\n".
              'Reply-To: '.$email_from."\r\n" .
              'X-Mailer: PHP/' . phpversion();

  // EDIT THE 2 LINES BELOW AS REQUIRED
  $email_to = "uroslates@gmail.com";
  $email_subject = "<uroslates.com> Contact Form submitted by <". clean_string($name) .">";
   
  
  $response = array();
  if( mail($email_to, $email_subject, $email_message, $headers) ) {
    $response['success'] = true;
    $response['message'] = 'Thank you for contacting me. I will be in touch with you very soon.';
  } else {
    $response['success'] = false;
    $response['message'] = 'There was a problem sending your message. Please try again or send me an email!';
    $response['errors'] = $errors;
  }

  die( json_encode($response) );

?>