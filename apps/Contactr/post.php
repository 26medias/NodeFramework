<?php

$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
$surname = filter_var($_POST['surname'], FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
$message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

if($name && $surname && $email && $subject && $message)
{
	if(!filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		echo json_encode(array(
								"result" => false, 
								"message" => "Veuillez fournir une adresse email valide."
		));

		return false;
	}

	$content = "Nom : ".$name."<br /><br />";
	$content .= "Prenom : ".$surname."<br /><br />";
	$content .= "Email : ".$email."<br /><br />";
	$content .= "Sujet : ".$subject."<br /><br /><br />";
	$content .= "Message : ".$message."<br /><br /><br />";

	if(mail($_CONF["vars"]["Contactr"]["email"], $subject, $message))
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