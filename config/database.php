<?php
require 'vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

// Ganti path/to/your-service-account.json dengan path file json yang Anda unduh dari Firebase
$firebase = (new Factory)
    ->withServiceAccount('phpnative-871be-firebase-adminsdk-vni5i-b6e78557b9.json')
    ->withDatabaseUri('https://phpnative-871be-default-rtdb.asia-southeast1.firebasedatabase.app/');

$database = $firebase->createDatabase();

// FUNCTION CRUD FIREBASE
function createData($path, $data)
{
    global $database;
        $newPostKey = $database->getReference($path)->push();
        return $newPostKey;
}


function readData($path)
{
    global $database;
    $snapshot = $database->getReference($path);
    return $snapshot->getValue();
}

function updateData($path, $data)
{
    global $database;
    $database->getReference($path)->update($data);
}
function deleteData($path)
{
    global $database;
    $database->getReference($path)->remove();
}

