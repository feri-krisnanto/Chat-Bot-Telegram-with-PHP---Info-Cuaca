<?php

define('HS', true);

/*
 *  Programmer  : Hasanudin HS
 *  Email       : banghasan@gmail.com
 *  Telegram    : @hasanudinhs
 *
 *  Name        : Template bot telegram - php
 *  Fungsi      : Sample bot API
 *  Pembuatan   : Mei 2016
 *
 *  File        : hook.php
 *  Tujuan      : bot hook untuk telegram
 *	Syarat		: hosting harus HTTPS bersertifikat
 *  ____________________________________________________________
*/

require_once 'bot-api-config.php';
require_once 'bot-api-fungsi.php';

require_once 'bot-api-proses.php';
// require_once 'botFunction.php';



$entityBody = file_get_contents('php://input');
$message = json_decode($entityBody, true);
prosesApiMessage($message);

echo "Succsess";
// Telegram by: banghasan @hasanudinhs @myqers;
