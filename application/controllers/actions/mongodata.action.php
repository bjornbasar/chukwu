<?php

$db = new Core_Mongo('module');

for ($i = 1; $i <= 100; $i ++)
{
	$data = array();
	$data['name'] = 'Module Name ' . $i;
	$data['display_name'] = 'Display Name ' . $i;
	
	$db->create($data);
}

$db = new Core_Mongo('role');

for ($i = 1; $i <= 100; $i ++)
{
	$data = array();
	$data['name'] = "Role $i";
	$data['display_name'] = "Display Role $i";
	
	$db->create($data);
}

$db = new Core_Mongo('user');

$dbRole = new Core_Mongo('role');

$role = $dbRole->getNewDocument();

for ($i = 1; $i <= 100; $i ++)
{
	$data = $db->getNewDocument();
	
	$data->username = "Username $i";
	$data->password = "Password $i";
	
	$temp = $role::one(array('name' => "Role $i"));
	$data->role = $temp;
	
	$data->basicinfo->firstname = "First Name $i";
	$data->basicinfo->lastname = "Last Name $i";
	$data->basicinfo->middlename = "Middle Name $i";
	$data->basicinfo->prefix = "Prefix $i";
	$data->basicinfo->suffix = "Suffix $i";
	$data->basicinfo->birthdate = date('Y-m-d');
	$data->basicinfo->avatar = "Avatar $i";
	
	$phone = $data->contactinfo->phones->new();
	$phone->id = new MongoId();
	$phone->phone = "Phone $i";
	$phone->phone_type = "mobile";
	$email = $data->contactinfo->emails->new();
	$email->id = new MongoId();
	$email->email = "email$i@email.com";
	$address = $data->contactinfo->addresses->new();
	$address->id = new MongoId();
	$address->country = "Country $i";
	$address->region = "Region $i";
	$address->city = "City $i";
	$address->street = "Street $i";
	$data->contactinfo->phones->addDocument($phone);
	$data->contactinfo->emails->addDocument($email);
	$data->contactinfo->addresses->addDocument($address);
	
	/*
	 * $data->contactinfo->phone_mobile = "Phone Mobile $i";
	 * $data->contactinfo->phone_office = "Phone Office $i";
	 * $data->contactinfo->email = "Email$i@email.com";
	 * $data->contactinfo->alternate_email = "AlternateEmail$i@email.com";
	 */
	$data->save();
}

