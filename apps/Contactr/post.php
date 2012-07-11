<?php

$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
$message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

if($email && $subject && $message)
{
	if(!filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		echo json_encode(array(
								"result" => false, 
								"message" => "Veuillez fournir une adresse email valide."
		));

		return false;
	}

	$content = "De : ".$email."<br /><br />";
	$content .= "Sujet : ".$subject."<br /><br />";
	$content .= "Message : ".$message;

	if(mail($_CONF["libsettings"]["server"]["Contactr"]["email"], $subject, $message))
	{
		echo json_encode(array(
								"result" => true, 
								"message" => "Votre message à bien été envoyé, merci."
		));

		return true;
	}
	else
	{
		echo json_encode(array(
								"result" => false, 
								"message" => "Votre message n'a pu être envoyé."
		));

		return false;
	}
}
else
{
	echo json_encode(array(
							"result" => false, 
							"message" => "Veuillez remplir tous les champs."
	));

	return false;
}

?>