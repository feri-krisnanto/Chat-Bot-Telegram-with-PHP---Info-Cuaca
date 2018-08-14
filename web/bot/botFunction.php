<?php

if (!defined('HS')) {
    die('Tidak boleh diakses langsung.');
}

function prosesApiMessage($sumber)
{
    $updateid = $sumber['update_id'];
   // if ($GLOBALS['debug']) mypre($sumber);
    if (isset($sumber['message'])) {
        $message = $sumber['message'];
        if (isset($message['text'])) {
            prosesPesanTeks($message);
        } elseif (isset($message['sticker'])) {
            prosesPesanSticker($message);
        } else {
            // gak di proses silakan dikembangkan sendiri
        }
    }
    if (isset($sumber['callback_query'])) {
        prosesCallBackQuery($sumber['callback_query']);
    }
    return $updateid;
}
function prosesPesanSticker($message)
{
    // if ($GLOBALS['debug']) mypre($message);
}
function prosesCallBackQuery($message)
{
    // if ($GLOBALS['debug']) mypre($message);
    $message_id = $message['message']['message_id'];
    $chatid = $message['message']['chat']['id'];
    $data = $message['data'];
    $inkeyboard = [
                [
                    ['text' => 'Update 1', 'callback_data' => 'data update 1'],
                    ['text' => 'Update 2', 'callback_data' => 'data update 2'],
                ],
                [
                    ['text' => 'keyboard on', 'callback_data' => '!keyboard'],
                    ['text' => 'keyboard inline', 'callback_data' => '!inline'],
                ],
                [
                    ['text' => 'keyboard off', 'callback_data' => '!hide'],
                ],
            ];
    $text = '*'.date('H:i:s').'* data baru : '.$data;
    editMessageText($chatid, $message_id, $text, $inkeyboard, true);
    $messageupdate = $message['message'];
    $messageupdate['text'] = $data;
    prosesPesanTeks($messageupdate);
}
function prosesPesanTeks($message)
{
    // if ($GLOBALS['debug']) mypre($message);
    $pesan = $message['text'];
    $chatid = $message['chat']['id'];
    $fromid = $message['from']['id'];
    $pesanid= $message['message_id'];
    switch (true) {
        case $pesan == '/id':
            sendApiAction($chatid);
            $text = 'ID Kamu adalah: '.$fromid;
            sendApiMsg($chatid, $text);
            break;

        case $pesan == 'test':

          // Create connection
          $conn = koneksi();

          if (!$conn) {
              $text = ("Connection failed: " . mysqli_connect_error());
          }else {
              $text = "Connected successfully";
          }
          sendApiMsg($chatid, $text);
          break;

        case $pesan == 'koneksi':
            $sql = "SELECT id_user FROM user";
            $conn = koneksi();
            $result = $conn->query($sql);
            $result2 = json_encode($result);
            sendApiMsg($chatid, $result2);
          break;

        case $pesan == '!keyboard':
            sendApiAction($chatid);
            $keyboard = [
                ['tombol 1', 'tombol 2'],
                ['!keyboard', '!inline'],
                ['!hide'],
            ];
            sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
            break;
        case $pesan == '!inline':
            sendApiAction($chatid);
            $inkeyboard = [
                [
                    ['text' => 'Update 1', 'callback_data' => 'data update 1'],
                    ['text' => 'Update 2', 'callback_data' => 'data update 2'],
                ],
                [
                    ['text' => 'keyboard on', 'callback_data' => '!keyboard'],
                    ['text' => 'keyboard inline', 'callback_data' => '!inline'],
                ],
                [
                    ['text' => 'keyboard off', 'callback_data' => '!hide'],
                ],
            ];
            sendApiKeyboard($chatid, 'Tampilan Inline', $inkeyboard, true);
            break;
        case $pesan == '!hide':
            sendApiAction($chatid);
            sendApiHideKeyboard($chatid, 'keyboard off');
            break;

        case preg_match("/\/echo (.*)/", $pesan):
            sendApiAction($chatid);
            preg_match("/\/echo (.*)/", $pesan, $hasil);
            $text = '*Echo:* '.$hasil[0];
            sendApiMsg($chatid, $text, $pesanid, 'Markdown');
            break;
        default:
            $text = "Gedebug Bot

Untuk penggunaan, silakan klik tombol bantuan atau ketik /help

Salam, DEVELOPER BOT PHP INDONESIA

@botphp
https://telegram.me/botphp

Informasi bot: @botkoleksi";
            sendApiMsg($chatid, $text, false, 'Markdown');
            break;
    }
}

?>
