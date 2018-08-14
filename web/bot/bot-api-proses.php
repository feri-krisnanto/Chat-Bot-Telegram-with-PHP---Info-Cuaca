<?php

if (!defined('HS')) {
    die('Tidak boleh diakses langsung.');
}


/*

Contoh penggunaan :
~~~~~~~~~~~~~~~~~~~~~

Kirim Aksi
----------
(typing, upload_photo, record_video, upload_video, record_audio, upload_audio, upload_document, find_location) :

    sendApiAction($chatid);
    sendApiAction($chatid, 'upload_photo');


Kirim Pesan :
----------
    sendApiMsg($chatid, 'pesan');
    sendApiMsg($chatid, 'pesan *tebal*', false, 'Markdown');


Kirim Markup Keyboard :
----------
    $keyboard = [
        [ 'tombol 1', 'tombol 2' ],
        [ 'tombol 3', 'tombol 4' ],
        [ 'tombol 5' ]
    ];

    sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);


Kirim Inline Keyboard
----------
    $inkeyboard = [
        [
            ['text'=>'tombol 1', 'callback_data' => 'data 1'],
            ['text'=>'tombol 2', 'callback_data' => 'data 2']
        ],
        [
            ['text'=>'tombol akhir', 'callback_data' => 'data akhir']
        ]
    ];

    sendApiKeyboard($chatid, 'tombol pilihan', $inkeyboard, true);


editMessageText
----------
    editMessageText($chatid, $message_id, $text, $inkeyboard, true);



Menyembunyikan keyboard :
----------
    sendApiHideKeyboard($chatid, 'keyboard off');


kirim sticker
----------

    sendApiSticker($chatid, 'BQADAgADUAADxKtoC8wBeZm11cjsAg')

~~~~~~~~~~~~~~~~~~~~~

*/


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

//Untuk Nilai Balikan Query Data
function prosesCallBackQuery($message)
{
    // if ($GLOBALS['debug']) mypre($message);

    $message_id = $message['message']['message_id'];
    $chatid = $message['message']['chat']['id'];
    $data = $message['data'];
    sendApiAction($chatid);
    $daerah = explode("_", $data);
    //inisialisasi
    $area = $daerah[1];
    $row = (int)$area;

    // $messageupdate = $message['message'];
    // $messageupdate['text'] = $data;
    //
    // prosesPesanTeks($messageupdate);
}

//Fungsi First Get Data
function prosesPesanTeks($message)
{
    // if ($GLOBALS['debug']) mypre($message);

    $pesan = $message['text'];
    $chatid = $message['chat']['id'];
    $fromid = $message['from']['id'];
    $name = $message['from']['first_name'];
    $pesanid= $message['message_id']; // variable penampung id message


    $kata = strtolower($pesan);
    $pecah[] = NULL;
    $pecah = explode(" ", $pesan);

    sendApiAction($chatid, 'typing...');

    //function Utama
    switch (true) {
        case preg_match("/^W/", $pecah[1]): //for Upercase 1st senence W
          //sendApiMsg($chatid, 'ini W Besar |'.$pecah[1]."|");
          showActualCuacaBandara($chatid, $pecah[0]);
          break;

        case preg_match("/Warning Cuaca ‼️/i", $kata):
            KeyboardPeringatanCuaca($chatid);
            break;

        case preg_match("/^\(W*/i", $pecah[1]): //for Upercase 1st senence W
            //sendApiMsg($chatid, 'ini (W) Besar |'.$pecah[1]."|");
            showPrakiraanCuacaBandara($chatid, $pecah[1], $pesanid);
            break;

        case preg_match("/^\(M*/i", $pesan): //for Upercase 1st senence M
            sendApiMsg($chatid, "Request Anda sedang diproses, silakan tunggu..");
            infoMaritim($chatid, $pesan);
            break;

        case preg_match("/kembali/", $kata) :
            if (preg_match("/home/", $kata)) {
                $text = ' ``` Menu Utama 🏠 ``` ';
                keyboardUtama($text, $chatid );
            }elseif (preg_match("/prov/", $kata)){
                keyboardCuaca($chatid);
            }else{
                $text = ' problem ';
                keyboardUtama($text, $chatid);
            }
        break;

        case preg_match("/prakiraan/", $kata) :
            if (preg_match("/curah/", $kata)) {
                sendApiMsg($chatid, 'Menampilkan Informasi Prakiraan Curah Hujan ☔️', $pesanid );
                curahHujan($chatid);
            }elseif(preg_match("/sifat/", $kata)){
                sendApiMsg($chatid, 'Menampilkan Informasi Prakiraan Sifat Hujan ☔️', $pesanid );
                curahHujan($chatid);
            }elseif (preg_match("/gelombang/", $kata)) {
                sendApiMsg($chatid, 'Maaf Data Tingi Gelombang Belum Tersedia ⚠️');
            }
            else{
                goto cuaca;
            }
        break;

        case preg_match("/gempa/", $kata) :
            if (preg_match("/terkini/", $kata)) {
                gempaTerkini($chatid, $pesanid);
                gempaTerkiniDirasakan($chatid, $pesanid);
            }elseif (preg_match("/dirasakan/", $kata)) {
                gempaDirasakan($chatid, $pesanid);
            }elseif (preg_match("/5.0/", $kata)) {
                gempa50($chatid, $pesanid);
            }else{
                keyboardGempa($chatid);
            }
        break;

        case preg_match("/maritim/", $kata):
          if (preg_match("/maritim$/", $kata)) {
              keyboardInfoMaritim($chatid);
          }elseif (preg_match("/^maritim/", $kata)) {
            if (preg_match("/pelabuhan/", $kata)) {
              sendApiMsg($chatid, "in menu pelabuhan");
              keyboardInfoMaritimLokasi($chatid,'(M1)');

            }elseif (preg_match("/penyebrangan/", $kata)) {
              sendApiMsg($chatid, "in menu penyebarang");
              keyboardInfoMaritimLokasi($chatid,'(M2)');

            }elseif (preg_match("/pelayanan/", $kata)) {
              sendApiMsg($chatid, "in menu pelayanan");
              keyboardInfoMaritimLokasi($chatid,'(M3)');

            }elseif (preg_match("/wisata bahari/", $kata)) {
              sendApiMsg($chatid, "in menu wisata bahasia");
              keyboardInfoMaritimLokasi($chatid,'(M4)');

            }
          }

          break;

        case preg_match("/cuaca/", $kata) :
            cuaca:
            if (preg_match("/daerah/", $kata)) {
                sendApiMsg($chatid, 'Maaf Data Belum Tersedia ⚠️');
            }elseif (preg_match("/bandara/", $kata)) {
                if (preg_match("/aktual/", $kata)) {
                    //sendApiMsg($chatid, 'Maaf Menu laporan terkini cuaca bandara Belum Tersedia ⚠️');
                    keyboardAktualCuacaBandara($chatid);
                }else{
                    //sendApiMsg($chatid, 'Maaf Menu prakiraan cuaca bandara Belum Tersedia ⚠️');
                    keyboardPrakiraanCuacaBandara($chatid);
                }
            }elseif (preg_match("/^warning/", $kata)) {

                warningCuaca($chatid, $pesan, $pesanid);

            }else{
                sendApiMsg($chatid, 'Sedang Menyiapkan Data', $pesanid);
                keyboardCuaca($chatid);
            }
        break;

        case preg_match("/iklim/", $kata) :
            sendApiMsg($chatid, 'Menyajikan Informasi Iklim Berupa Prakiraan Curah Hujan', $pesanid );
            keyboardIklim($chatid);
        break;

        case preg_match("/^prov/", $kata) :
            sendApiAction($chatid);
            checkProvinsi($chatid, $kata, $pesanid);
        break;

        case preg_match("/BMKG/", $pesan) :
            keyboardDataBmkg($chatid);
        break;

        case preg_match("/server/", $kata) :
            date_default_timezone_set("Asia/Bangkok");
            $waktu_server = "Waktu Server BMKG Sekarang adalah :\n" .
date("*Y-m-d H:i:s *") .
date_default_timezone_set("Asia/Jakarta") .
"\n\n``` Waktu Indonesia Barat :```".date("_Y-m-d H:i:s _").
date_default_timezone_set("Asia/Ujung_Pandang") .
"\n\n``` Waktu Indonesia Tengah :```".date("_Y-m-d H:i:s _").
date_default_timezone_set("Asia/Jayapura") .
"\n\n``` Waktu Indonesia Timur :```".date("_Y-m-d H:i:s _");

            sendApiMsg($chatid, $waktu_server, false, 'Markdown');
        break;

        case preg_match("/- 0⃣/", $kata):
            sendApiMsg($chatid, 'Sedang Mengirim Informasi', $pesanid);
            sortCuacaKota01($chatid, $kata);
            break;

        case preg_match("/- 1⃣/", $kata):
            sendApiMsg($chatid, 'Sedang Mengirim Informasi', $pesanid);
            sortCuacaKota11($chatid, $kata);
            break;

        case preg_match("/- 2⃣/", $kata):
            sendApiMsg($chatid, 'Sedang Mengirim Informasi', $pesanid);
            sortCuacaKota21($chatid, $kata);
            break;

        case preg_match("/- 3⃣/", $kata):
            sendApiMsg($chatid, 'Sedang Mengirim Informasi', $pesanid);
            sortCuacaKota31($chatid, $kata);
            break;

        case preg_match("/bantuan/", $kata):
          $kalimat = "``` ++Badan Meteorologi Klimatologi dan Geofisika++ ```

Menu Bantuan Sistem Informasi Resmi Telegram *BMKG*.\n

Tersedia Menu :

Tombol 🌤 Info Cuaca
- `Digunakan Untuk Melihat Prakiraan Cuaca, Suhu, dan Kelembaban Berdasarkan Kota`

Tombol 🌎 Info Gempa
- `Digunakan Untuk Melihat Gempa Terkini yang Sedang Terjadi di Indonesia`
- `Terdapat 2 Pilihan yaitu, Gempa > 5.0 SR dan Gempa Dirasakan`

Tombol 💦 Info Iklim
- `Digunakan Untuk Melihat Prakiraan Curah Hujan Di Indonesia`

Tombol 🏣 Data BMKG
- `Digunakan Untuk Melihat`
    `1. Laporan Aktual Cuaca Bandara`
    `2. Prakiraan Cuaca Bandara`
    `3. Tinggi Gelombang`

``` Silahkan pilih menu yang kami sediakan : ``` ";
          sendApiMsg($chatid, $kalimat, $pesanid, 'Markdown');
          break;

        // case $kata = '/test':
        //         //0⃣ 1⃣ 2⃣ 3⃣ 4⃣ 5⃣ 6⃣ 7⃣ 8⃣ 9⃣
        //         //  $inline = "coba $i";
        //         // $coba[$i][0] = $inline;
        //         // $keyboard = $coba;

        //     for ($i=0; $i <= 5 ; $i++) {
        //         if ($i != 5) {
        //             $inline = "coba $i";
        //             $coba[$i][0] = $inline;
        //         }else{
        //             $inline = "ini menu home";
        //             $coba[$i][0] = $inline;
        //         }
        //         $keyboard = $coba;
        //     }
        //     sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);

        // break;

        case $kata = '/start':
            $kalimat = "``` ++Badan Meteorologi Klimatologi dan Geofisika++ ```

Selamat datang *$name* di Sistem Informasi Resmi Telegram *BMKG*.\n
Anda bisa mencari informasi cuaca melalui telegram bot ini.
Informasi yang disampaikan akan selalu diperbaharui setiap harinya.

``` Silahkan pilih menu yang kami sediakan : ``` 😊";
            keyboardUtama($kalimat, $chatid);
        break;

        default:
            $kalimat = '🏠 Kembali ke Home';
            keyboardUtama($kalimat, $chatid);
        break;
    }
}

function keyboardUtama($kalimat, $chatid)
{
    sendApiAction($chatid);
    $keyboard = [
        //['🎯 Cuaca Daerah Anda'],
        ['🌤 Info Cuaca', '💦 Info Iklim'],
        ['🌎 Info Gempa', '⛴ Info Maritim'],
        // ['🏣 Data BMKG' , 'Warning Cuaca ‼️','❓ Bantuan'],
        ['Warning Cuaca ‼️'],
    ];
    sendApiKeyboard($chatid, $kalimat , $keyboard);
}

function keyboardIklim($chatid)
{
    sendApiAction($chatid);
    $kalimat = '``` Silahkan pilih menu yang kami sediakan : ```';
    $keyboard = [
        ['🏠 Kembali ke Home'],
        ['☔️ Prakiraan Curah Hujan', '☔️ Prakiraan Sifat Hujan'],
    ];
    sendApiKeyboard($chatid, $kalimat , $keyboard);
}

function keyboardGempa($chatid)
{
    sendApiAction($chatid);
    $kalimat = '``` Silahkan pilih menu yang kami sediakan : ```';
    $keyboard = [
        ['🏠 Kembali ke Home'],
        ['🛰 Info Gempa Bumi Terkini'],
        ['5⃣ Gempabumi Dirasakan', '5⃣ Gempabumi ≥ 5.0'],
    ];
    sendApiKeyboard($chatid, $kalimat , $keyboard);
}

function curahHujan($chatid)
{
    $random = uniqid();
    $bulan = array(
                '01' => 'JANUARI',
                '02' => 'FEBRUARI',
                '03' => 'MARET',
                '04' => 'APRIL',
                '05' => 'MEI',
                '06' => 'JUNI',
                '07' => 'JULI',
                '08' => 'AGUSTUS',
                '09' => 'SEPTEMBER',
                '10' => 'OKTOBER',
                '11' => 'NOVEMBER',
                '12' => 'DESEMBER',
        );
        
    sendApiAction($chatid, 'upload_photo');
    sendApiPhoto($chatid,"http://webdata.bmkg.go.id/datamkg/klimatologi/pch/pch.bulan.1.cond1.png?id=".$random);
    sendApiMsg($chatid, "Prakiraan Curah Hujan bulan ". $bulan[date('m')] ." 2017");
    // sendApiPhoto($chatid,"http://webdata.bmkg.go.id/datamkg/klimatologi/pch/pch.bulan.2.cond1.png?id=".$random);
    // sendApiMsg($chatid, "Prakiraan Curah Hujan Bulan ". $bulan[date('m' + 1)] ." 2017");
    // sendApiPhoto($chatid,"http://webdata.bmkg.go.id/datamkg/klimatologi/pch/pch.bulan.3.cond1.png?id=".$random);
    // sendApiMsg($chatid, "Prakiraan Curah Hujan Bulan ". $bulan[date('m')] ." 2017");
}
function sifatHujan($chatid)
{
    sendApiAction($chatid, 'upload_photo');
    sendApiPhoto($chatid,"http://webdata.bmkg.go.id/datamkg/klimatologi/pch/pch.bulan.1.cond2.png");
    sendApiMsg($chatid, "Prakiraan Sifat Hujan Bulan ". $bulan[date('m')] ." 2017");
    // sendApiPhoto($chatid,"http://webdata.bmkg.go.id/datamkg/klimatologi/pch/pch.bulan.2.cond2.png?id=".$random);
    // sendApiMsg($chatid, "Prakiraan Sifat Hujan Bulan ". $bulan[date('m')] ." 2017");
    // sendApiPhoto($chatid,"http://webdata.bmkg.go.id/datamkg/klimatologi/pch/pch.bulan.3.cond2.png?id=".$random);
    // sendApiMsg($chatid, "Prakiraan Sifat Hujan Bulan ". $bulan[date('m')] ." 2017");
}

function gempaTerkini($chatid, $pesanid)
{

    $new = simplexml_load_file("https://mobiledata.bmkg.go.id/datamkg/TEWS/autogempa.xml");
    if (empty($new)) {
      $new = simplexml_load_file("http://dataweb.bmkg.go.id/inATEWS/autogempa.xml");
    }//handling data


    $tanggal = $new->gempa->Tanggal;
    $jam = $new->gempa->Jam;
    $magnitude = $new->gempa->Magnitude;
    $kedalaman = $new->gempa->Kedalaman;
    $lintang = $new->gempa->Lintang;
    $bujur = $new->gempa->Bujur;
    $wilayah = $new->gempa->Wilayah1;
    $potensi = $new->gempa->Potensi;

    $text = "
``` --Info Gempabumi Terkini >5.0 Magnitude-- ```

Info Gempa Terkini *$tanggal*, _ $jam _
`Lokasi \t\t: $lintang - $bujur
$wilayah,
Magnitude \t: $magnitude
Kedalaman \t: $kedalaman
`

* $potensi *
";
    // $pecah_lintang = explode(' ', $lintang);
    // $pecah_bujur = explode(' ', $bujur);
    // if ($pecah_lintang == 'LS') {
    //     $lang = (float) -$pecah_lintang[0];
    // }else{
    //     $lang = (float) $pecah_lintang[0];
    // }

    // $lang = (float) $lang;
    // $lat = (float) $pecah_bujur[0];
    
    $random = uniqid();
    sendApiPhoto($chatid, "http://dataweb.bmkg.go.id/INATEWS/eqmap.gif?id=".$random);
    sendApiMsg($chatid, $text , $pesanid, 'Markdown');
    
    //sendApiLocation($chatid, $lang, $lat , $pesanid);


}

function gempaTerkiniDirasakan($chatid, $pesanid)
{
    //gempa dirasakan
    $new = simplexml_load_file("https://mobiledata.bmkg.go.id/datamkg/TEWS/lastgempadirasakan.xml");
    if (empty($new)) {
      $new = simplexml_load_file("http://dataweb.bmkg.go.id/inATEWS/lastgempadirasakan.xml");
    }

    $tanggal = $new->Gempa->Tanggal;
    $jam = $new->Gempa->Jam;
    $magnitude = $new->Gempa->Magnitude;
    $kedalaman = $new->Gempa->Kedalaman;
    $lintang = $new->Gempa->Lintang;
    $bujur = $new->Gempa->Bujur;
    $keterangan = $new->Gempa->Keterangan;
    $dirasakan = $new->Gempa->Dirasakan;

    $text = "
``` --Info Gempabumi Terkini Dirasakan-- ```

Info Gempa Terkini *$tanggal*, _ $jam _
`Lokasi \t\t: $lintang - $bujur
Magnitude \t: $magnitude
Kedalaman \t: $kedalaman
$keterangan
`
*$dirasakan *
";
    

    $random = uniqid();
    sendApiPhoto($chatid, "http://inatews.bmkg.go.id/shakemaprasa/20170711174821/download/intensity.jpg?id=".$random);
    sendApiMsg($chatid, $text , $pesanid, 'Markdown');
}

function gempaDirasakan($chatid, $pesanid)
{
    $new = simplexml_load_file("https://mobiledata.bmkg.go.id/datamkg/TEWS/gempadirasakan.xml");
    if (empty($new)) {
      $new = simplexml_load_file("http://dataweb.bmkg.go.id/inATEWS/gempadirasakan.xml");
    }//handling data

    sendApiMsg($chatid, '5 Gempabumi Dirasakan' , $pesanid, 'Markdown');
    for ($i=4; $i >= 0 ; $i--) {
        $tanggal = $new->Gempa[$i]->Tanggal;
        $magnitude = $new->Gempa[$i]->Magnitude;
        $kedalaman = $new->Gempa[$i]->Kedalaman;
        $lintang = $new->Gempa[$i]->Lintang;
        $posisi = $new->Gempa[$i]->Posisi;
        $wilayah = $new->Gempa[$i]->Dirasakan;
        $ket = $new->Gempa[$i]->Keterangan;
        $ii = $i + 1;

        $text = "$ii. *== Info Gempabumi Dirasakan ==*
_$tanggal _
`
Magnitude \t: $magnitude
Kedalaman \t: $kedalaman
Dirasakan \t: $wilayah
Bujur-Lintang : $posisi
$ket
`";
        sendApiMsg($chatid, $text , false,'Markdown');
    }
}
function gempa50($chatid, $pesanid)
{
    $new = simplexml_load_file("https://mobiledata.bmkg.go.id/datamkg/TEWS/gempaterkini.xml");
    if (empty($new)) {
      $new = simplexml_load_file("http://dataweb.bmkg.go.id/inATEWS/gempaterkini.xml");
    }//handling data

    sendApiMsg($chatid, '5 Gempabumi ≥ 5.0' , $pesanid, 'Markdown');
    for ($i=4; $i >= 0 ; $i--) {
        $tanggal = $new->gempa[$i]->Tanggal;
        $jam = $new->gempa[$i]->Jam;
        $magnitude = $new->gempa[$i]->Magnitude;
        $kedalaman = $new->gempa[$i]->Kedalaman;
        $lintang = $new->gempa[$i]->Lintang;
        $bujur = $new->gempa[$i]->Bujur;
        $wilayah = $new->gempa[$i]->Wilayah;
        $ii = $i + 1;

        $text = "$ii. Info Gempabumi > 5.0
*$tanggal, $jam *
`
Magnitude \t: $magnitude
Kedalaman \t: $kedalaman
lokasi \t: $wilayah
Bujur-Lintang : $lintang - $bujur
`";
        sendApiMsg($chatid,$text, false ,'Markdown');
    }
}

function keyboardDataBmkg($chatid)
{
    $keyboard = [
        ['🏠 Kembali ke Home'],
        [ '🛬 Laporan Aktual Cuaca Bandara ', '🛫 Prakiraan Cuaca Bandara' ],
        [ '🌊 Prakiraan Tinggi Gelombang' ],
    ];
    sendApiKeyboard($chatid, '_Menu Pilihan :_', $keyboard);
}

function keyboardCuaca($chatid)
{
    $keyboard = [
        [ '🏠 Kembali ke Home'],
        [ 'Prov. Aceh 🇮🇩', 'Prov. Sumatera Utara 🇮🇩' ],
        [ 'Prov. Sumatera Barat 🇮🇩', 'Prov. Riau 🇮🇩' ],
        [ 'Prov. Kepulauan Riau 🇮🇩', 'Prov. Jambi 🇮🇩' ],
        [ 'Prov. Sumatera Selatan 🇮🇩', 'Prov. Bangka Belitung 🇮🇩' ],
        [ 'Prov. Bengkulu 🇮🇩', 'Prov. Lampung 🇮🇩' ],
        [ 'Prov. DKI Jakarta 🇮🇩', 'Prov. Jawa Barat 🇮🇩' ],
        [ 'Prov. Banten 🇮🇩', 'Prov. Jawa Tengah 🇮🇩' ],
        [ 'Prov. Yogyakarta 🇮🇩', 'Prov. Jawa Timur 🇮🇩' ],
        [ 'Prov. Bali 🇮🇩', 'Prov. NTT 🇮🇩' ],
        [ 'Prov. NTB 🇮🇩', 'Prov. Kalimantan Barat 🇮🇩' ],
        [ 'Prov. Kalimantan Tengah 🇮🇩', 'Prov. Kalimantan Selatan 🇮🇩' ],
        [ 'Prov. Kalimantan Timur 🇮🇩', 'Prov. Kalimantan Utara 🇮🇩' ],
        [ 'Prov. Sulawesi Utara 🇮🇩', 'Prov. Sulawesi Barat 🇮🇩' ],
        [ 'Prov. Sulawesi Tengah 🇮🇩', 'Prov. Sulawesi Tenggara 🇮🇩' ],
        [ 'Prov. Sulawesi Selatan 🇮🇩', 'Prov. Gorontalo 🇮🇩' ],
        [ 'Prov. Maluku 🇮🇩', 'Prov. Maluku Utara 🇮🇩' ],
        [ 'Prov. Papua Barat 🇮🇩', 'Prov. Papua 🇮🇩' ],
    ];
    sendApiKeyboard($chatid, '_Daftar Provinsi Di Indonesia_', $keyboard);
}

//cuaca provinsi
function checkProvinsi($chatid, $namaProv,$pesanid)
{
    sendApiMsg($chatid, '*Mohon Tunggu, Sedang Menyiapkan Data ⚠️*',$pesanid,'Markdown');
    switch (true) {
        case preg_match("/aceh/", $namaProv):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-aceh.xml');
            keyboardNamaKota($chatid, $get_xml, '0⃣1⃣');
        break;

        case preg_match("/sumatera/", $namaProv):
            if (preg_match("/utara/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sumaterautara.xml');
                keyboardNamaKota($chatid, $get_xml, '0⃣2⃣');
            }elseif (preg_match("/barat/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sumaterabarat.xml');
                keyboardNamaKota($chatid, $get_xml, '0⃣3⃣');
            }elseif (preg_match("/selatan/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sumateraselatan.xml');
                keyboardNamaKota($chatid, $get_xml, '0⃣4⃣');
            }
        break;

        case preg_match("/riau/", $namaProv):
            if (preg_match("/kepulauan/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-kepulauanriau.xml');
                keyboardNamaKota($chatid, $get_xml, '0⃣5⃣');
            }else{
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-riau.xml');
                keyboardNamaKota($chatid, $get_xml, '0⃣6⃣');
            }
        break;

        case preg_match("/jambi/", $namaProv):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-jambi.xml');
            keyboardNamaKota($chatid, $get_xml, '0⃣7⃣');
        break;

        case preg_match("/bangka/", $namaProv):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-bangkabelitung.xml');
            keyboardNamaKota($chatid, $get_xml, '0⃣8⃣');
        break;

        case preg_match("/bengkulu/", $namaProv):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-bengkulu.xml');
            keyboardNamaKota($chatid, $get_xml, '0⃣9⃣');
        break;

        case preg_match("/lampung/", $namaProv):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-lampung.xml');
            keyboardNamaKota($chatid, $get_xml, '1⃣0⃣');
        break;

        case preg_match("/jakarta/", $namaProv):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-dkijakarta.xml');
            keyboardNamaKota($chatid, $get_xml, '1⃣1⃣');
        break;

        case preg_match("/jawa/", $namaProv):
            if (preg_match("/barat/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-jawabarat.xml');
                keyboardNamaKota($chatid, $get_xml, '1⃣2⃣');
            }elseif (preg_match("/tengah/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-jawatengah.xml');
                keyboardNamaKota($chatid, $get_xml, '1⃣3⃣');
            }elseif (preg_match("/timur/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-jawatimur.xml');
                keyboardNamaKota($chatid, $get_xml, '1⃣4⃣');
            }
        break;

        case preg_match("/banten/", $namaProv):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-banten.xml');
            keyboardNamaKota($chatid, $get_xml, '1⃣5⃣');
        break;

        case preg_match("/yogyakarta/", $namaProv):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-diyogyakarta.xml');
            keyboardNamaKota($chatid, $get_xml, '1⃣6⃣');
        break;

        case preg_match("/bali/", $namaProv):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-bali.xml');
            keyboardNamaKota($chatid, $get_xml, '1⃣7⃣');
        break;

        case preg_match("/ntt/", $namaProv):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-nusatenggaratimur.xml');
            keyboardNamaKota($chatid, $get_xml, '1⃣8⃣');
        break;

        case preg_match("/ntb/", $namaProv):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-nusatenggarabarat.xml');
            keyboardNamaKota($chatid, $get_xml, '1⃣9⃣');
        break;

        case preg_match("/kalimantan/", $namaProv):
            if (preg_match("/barat/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-kalimantanbarat.xml');
                keyboardNamaKota($chatid, $get_xml, '2⃣0⃣');
            }elseif (preg_match("/tengah/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-kalimantantengah.xml');
                keyboardNamaKota($chatid, $get_xml, '2⃣1⃣');
            }elseif (preg_match("/selatan/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-kalimantanselatan.xml');
                keyboardNamaKota($chatid, $get_xml, '2⃣2⃣');
            }elseif (preg_match("/timur/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-kalimantantimur.xml');
                keyboardNamaKota($chatid, $get_xml, '2⃣3⃣');
            }elseif (preg_match("/utara/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-kalimantanutara.xml');
                keyboardNamaKota($chatid, $get_xml, '2⃣4⃣');
            }
        break;

        case preg_match("/sulawesi/", $namaProv) :
            if (preg_match("/utara/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sulawesiutara.xml');
                keyboardNamaKota($chatid, $get_xml, '2⃣5⃣');
            }elseif (preg_match("/barat/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sulawesibarat.xml');
                keyboardNamaKota($chatid, $get_xml, '2⃣6⃣');
            }elseif (preg_match("/tengah/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sulawesitengah.xml');
                keyboardNamaKota($chatid, $get_xml, '2⃣7⃣');
            }elseif (preg_match("/tenggara/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sulawesitenggara.xml');
                keyboardNamaKota($chatid, $get_xml, '2⃣8⃣');
            }elseif (preg_match("/selatan/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sulawesiselatan.xml');
                keyboardNamaKota($chatid, $get_xml, '2⃣9⃣');
            }
        break;

        case preg_match("/gorontalo/", $namaProv):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-gorontalo.xml');
            keyboardNamaKota($chatid, $get_xml, '3⃣0⃣');
        break;

        case preg_match("/maluku/", $namaProv):
            if (preg_match("/utara/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-malukuutara.xml');
                keyboardNamaKota($chatid, $get_xml, '3⃣1⃣');
            }else{
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-maluku.xml');
                keyboardNamaKota($chatid, $get_xml, '3⃣2⃣');
            }
        break;

        case preg_match("/papua/", $namaProv):
            if (preg_match("/barat/", $namaProv)) {
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-papuabarat.xml');
                keyboardNamaKota($chatid, $get_xml, '3⃣3⃣');
            }else{
                $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-papua.xml');
                keyboardNamaKota($chatid, $get_xml, '3⃣4⃣');
            }
            break;

        default:
            sendApiMsg($chatid, 'Maaf Data Belum Tersedia ⚠️');
        break;
    }
}

function keyboardNamaKota($chatid, $get_xml, $temp)
{
    sendApiAction($chatid);
    $s = simplexml_import_dom($get_xml); //count jumlahnya
    $count = count($s ->forecast->area);

    for ($i=0; $i <= $count ; $i++) {
        $ii = $i + 1;
        if ($i != $count) {
            $namaKota = $get_xml->forecast->area[$i]->name[1];
            $inline = $ii . ". $namaKota - $temp";
            $coba[$i][0] = $inline;
        }else{
            $inline = "Kembali ke Prakiraan Cuaca Provinsi 🇮🇩";
            $coba[$i][0] = $inline;
        }
        $keyboard = $coba;
    }
    sendApiKeyboard($chatid, "_Daftar Nama Kota/Kabupaten_", $keyboard);
}

function sortCuacaKota01($chatid, $kata)
{
    switch (true) {

        case preg_match("/0⃣1⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-aceh.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/0⃣2⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sumaterautara.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/0⃣3⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sumaterabarat.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/0⃣4⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sumateraselatan.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/0⃣5⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-kepulauanriau.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/0⃣6⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-riau.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/0⃣7⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-jambi.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/0⃣8⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-bangkabelitung.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/0⃣9⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-bengkulu.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        default:
            # code...
            break;
    }
}

function sortCuacaKota11($chatid, $kata)
{
    switch (true) {

        case preg_match("/1⃣0⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-lampung.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/1⃣1⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-dkijakarta.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/1⃣2⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-jawabarat.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/1⃣3⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-jawatengah.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/1⃣4⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-jawatimur.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/1⃣5⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-banten.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/1⃣6⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-diyogyakarta.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/1⃣7⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-bali.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/1⃣8⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-nusatenggaratimur.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/1⃣9⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-nusatenggarabarat.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        default:
            # code...
            break;
    }
}

function sortCuacaKota21($chatid, $kata)
{
    switch (true) {

        case preg_match("/2⃣0⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-kalimantanbarat.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/2⃣1⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-kalimantantengah.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/2⃣2⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-kalimantanselatan.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/2⃣3⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-kalimantanselatan.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/2⃣4⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-kalimantanutara.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/2⃣5⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sulawesiutara.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/2⃣6⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sulawesibarat.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/2⃣7⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sulawesitengah.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/2⃣8⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sulawesitenggara.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/2⃣9⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-sulawesiselatan.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        default:
            # code...
            break;
    }
}

function sortCuacaKota31($chatid, $kata)
{
    switch (true) {

        case preg_match("/3⃣0⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-gorontalo.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/3⃣1⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-malukuutara.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/3⃣2⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-maluku.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/3⃣3⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-papuabarat.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;

        case preg_match("/3⃣4⃣/", $kata):
            $get_xml = simplexml_load_file('http://dataweb.bmkg.go.id/mews/digitalforecast/digitalforecast-papua.xml');
            cekCuaca($chatid, $kata, $get_xml);
            break;
    }
}

function cekCuaca($chatid, $kata, $get_xml )
{
    $pisah = explode(".", $kata);
    $baris = $pisah[0] - 1;
    $row = (int)$baris;

    //Humiidity (Kelembaban)
    $hum_0 = $get_xml->forecast->area[$row]->parameter[0]->timerange[0]->value;
    $hum_1 = $get_xml->forecast->area[$row]->parameter[0]->timerange[1]->value;
    $hum_2 = $get_xml->forecast->area[$row]->parameter[0]->timerange[2]->value;

    $hum_3 = $get_xml->forecast->area[$row]->parameter[0]->timerange[3]->value;
    $hum_4 = $get_xml->forecast->area[$row]->parameter[0]->timerange[4]->value;
    $hum_5 = $get_xml->forecast->area[$row]->parameter[0]->timerange[5]->value;
    $hum_6 = $get_xml->forecast->area[$row]->parameter[0]->timerange[6]->value;

    $hum_7 = $get_xml->forecast->area[$row]->parameter[0]->timerange[7]->value;
    $hum_8 = $get_xml->forecast->area[$row]->parameter[0]->timerange[8]->value;
    $hum_9 = $get_xml->forecast->area[$row]->parameter[0]->timerange[9]->value;
    $hum_10 = $get_xml->forecast->area[$row]->parameter[0]->timerange[10]->value;

    //suhu
    $suhu_0 = $get_xml->forecast->area[$row]->parameter[5]->timerange[0]->value[0];
    $suhu_1 = $get_xml->forecast->area[$row]->parameter[5]->timerange[1]->value[0];
    $suhu_2 = $get_xml->forecast->area[$row]->parameter[5]->timerange[2]->value[0];

    $suhu_3 = $get_xml->forecast->area[$row]->parameter[5]->timerange[3]->value[0];
    $suhu_4 = $get_xml->forecast->area[$row]->parameter[5]->timerange[4]->value[0];
    $suhu_5 = $get_xml->forecast->area[$row]->parameter[5]->timerange[5]->value[0];
    $suhu_6 = $get_xml->forecast->area[$row]->parameter[5]->timerange[6]->value[0];

    $suhu_7 = $get_xml->forecast->area[$row]->parameter[5]->timerange[7]->value[0];
    $suhu_8 = $get_xml->forecast->area[$row]->parameter[5]->timerange[8]->value[0];
    $suhu_9 = $get_xml->forecast->area[$row]->parameter[5]->timerange[9]->value[0];
    $suhu_10 = $get_xml->forecast->area[$row]->parameter[5]->timerange[10]->value[0];

    //Cuaca
    $cuaca_0 = $get_xml->forecast->area[$row]->parameter[6]->timerange[0]->value;
    $cuaca_1 = $get_xml->forecast->area[$row]->parameter[6]->timerange[1]->value;
    $cuaca_2 = $get_xml->forecast->area[$row]->parameter[6]->timerange[2]->value;

    $cuaca_3 = $get_xml->forecast->area[$row]->parameter[6]->timerange[3]->value;
    $cuaca_4 = $get_xml->forecast->area[$row]->parameter[6]->timerange[4]->value;
    $cuaca_5 = $get_xml->forecast->area[$row]->parameter[6]->timerange[5]->value;
    $cuaca_6 = $get_xml->forecast->area[$row]->parameter[6]->timerange[6]->value;

    $cuaca_7 = $get_xml->forecast->area[$row]->parameter[6]->timerange[7]->value;
    $cuaca_8 = $get_xml->forecast->area[$row]->parameter[6]->timerange[8]->value;
    $cuaca_9 = $get_xml->forecast->area[$row]->parameter[6]->timerange[9]->value;
    $cuaca_10 = $get_xml->forecast->area[$row]->parameter[6]->timerange[10]->value;

    //Arah Angin
    $arah_0 = $get_xml->forecast->area[$row]->parameter[7]->timerange[0]->value[1];
    $arah_1 = $get_xml->forecast->area[$row]->parameter[7]->timerange[1]->value[1];
    $arah_2 = $get_xml->forecast->area[$row]->parameter[7]->timerange[2]->value[1];

    $arah_3 = $get_xml->forecast->area[$row]->parameter[7]->timerange[3]->value[1];
    $arah_4 = $get_xml->forecast->area[$row]->parameter[7]->timerange[4]->value[1];
    $arah_5 = $get_xml->forecast->area[$row]->parameter[7]->timerange[5]->value[1];
    $arah_6 = $get_xml->forecast->area[$row]->parameter[7]->timerange[6]->value[1];

    $arah_7 = $get_xml->forecast->area[$row]->parameter[7]->timerange[7]->value[1];
    $arah_8 = $get_xml->forecast->area[$row]->parameter[7]->timerange[8]->value[1];
    $arah_9 = $get_xml->forecast->area[$row]->parameter[7]->timerange[9]->value[1];
    $arah_10 = $get_xml->forecast->area[$row]->parameter[7]->timerange[10]->value[1];

    //kecepatan Angin
    $kec_0 = $get_xml->forecast->area[$row]->parameter[8]->timerange[0]->value[2];
    $kec_1 = $get_xml->forecast->area[$row]->parameter[8]->timerange[1]->value[2];
    $kec_2 = $get_xml->forecast->area[$row]->parameter[8]->timerange[2]->value[2];

    $kec_3 = $get_xml->forecast->area[$row]->parameter[8]->timerange[3]->value[2];
    $kec_4 = $get_xml->forecast->area[$row]->parameter[8]->timerange[4]->value[2];
    $kec_5 = $get_xml->forecast->area[$row]->parameter[8]->timerange[5]->value[2];
    $kec_6 = $get_xml->forecast->area[$row]->parameter[8]->timerange[6]->value[2];

    $kec_7 = $get_xml->forecast->area[$row]->parameter[8]->timerange[7]->value[2];
    $kec_8 = $get_xml->forecast->area[$row]->parameter[8]->timerange[8]->value[2];
    $kec_9 = $get_xml->forecast->area[$row]->parameter[8]->timerange[9]->value[2];
    $kec_10 = $get_xml->forecast->area[$row]->parameter[8]->timerange[10]->value[2];


    $nama_kota = $get_xml->forecast->area[$row]->name[1];
    //hari ini
    $today_pagi = keteranganCuaca($cuaca_0);
    $today_siang = keteranganCuaca($cuaca_1);
    $today_malam = keteranganCuaca($cuaca_2);

    //besok
    $tomorow_dini = keteranganCuaca($cuaca_3);
    $tomorow_pagi = keteranganCuaca($cuaca_4);
    $tomorow_siang = keteranganCuaca($cuaca_5);
    $tomorow_malam = keteranganCuaca($cuaca_6);

    //lusa
    $lusa_dini = keteranganCuaca($cuaca_7);
    $lusa_pagi = keteranganCuaca($cuaca_8);
    $lusa_siang = keteranganCuaca($cuaca_9);
    $lusa_malam = keteranganCuaca($cuaca_10);

    date_default_timezone_set("Asia/Bangkok");
    date_default_timezone_get();

    $dt = date("d-m-Y");
    $wkt = date("H");

    if ($wkt <= 17) {
        $text = "Prakiraan Cuaca Untuk Daerah:
*$nama_kota *
```
Pada tanggal ". date("d-m-Y \n");
        if ($wkt <= 5) {
          $text .= "Pagi Hari
Cuaca\t: $today_pagi
Suhu\t: $suhu_0 C
Kelembaban\t: $hum_0 %
Arah Angin\t: $arah_0
Kec. Angin\t: $kec_0 Km/Jam
";
        }
        if ($wkt <= 11) {
          $text .= "\nSiang Hari
Cuaca\t: $today_siang
Suhu:\t: $suhu_1 C
Kelembaban\t: $hum_1 %
Arah Angin\t: $arah_1
Kec. Angin\t: $kec_1 Km/Jam
";
        }
        $text .= "\nMalam Hari
Cuaca\t: $today_malam
Suhu:\t: $suhu_2 C
Kelembaban\t: $hum_2 %
Arah Angin\t: $arah_2
Kec. Angin\t: $kec_2 Km/Jam
```";
        sendApiMsg($chatid, $text, false, 'Markdown');
    }

    $besok = date( "d-m-Y", strtotime( "$dt +1 day" ));
    $text = "Prakiraan Cuaca Untuk Daerah:
*$nama_kota *
```
Pada tanggal _$besok _
Dini Hari
Cuaca \t:$tomorow_dini
Suhu:\t: $suhu_3 C
Kelembaban\t: $hum_3 %
Arah Angin\t: $arah_3
Kec. Angin\t: $kec_3 Km/Jam

Pagi Hari
Cuaca \t:$tomorow_pagi
Suhu:\t: $suhu_4 C
Kelembaban\t: $hum_4 %
Arah Angin\t: $arah_4
Kec. Angin\t: $kec_4 Km/Jam

Siang Hari
Cuaca\t:$tomorow_siang
Suhu:\t: $suhu_5 C
Kelembaban\t: $hum_5 %
Arah Angin\t: $arah_5
Kec. Angin\t: $kec_5 Km/Jam

Malam Hari
Cuaca\t:$tomorow_malam
Suhu:\t: $suhu_6 C
Kelembaban\t: $hum_6 %
Arah Angin\t: $arah_6
Kec. Angin\t: $kec_6 Km/Jam

```";
    sendApiMsg($chatid, $text, false, 'Markdown');

    $lusa = date( "d-m-Y", strtotime( "$dt +2 day" ));
    $text = "Prakiraan Cuaca Untuk Daerah:
*$nama_kota *
```
Pada tanggal _$lusa _
Dini Hari
Cuaca\t:$lusa_dini
Suhu:\t: $suhu_7 C
Kelembaban\t: $hum_7 %
Arah Angin\t: $arah_7
Kec. Angin\t: $kec_7 Km/Jam

Pagi Hari
Cuaca\t:$lusa_pagi
Suhu:\t: $suhu_8 C
Kelembaban\t: $hum_8 %
Arah Angin\t: $arah_8
Kec. Angin\t: $kec_8 Km/Jam

Siang Hari
Cuaca\t:$lusa_siang
Suhu:\t: $suhu_9 C
Kelembaban\t: $hum_9 %
Arah Angin\t: $arah_9
Kec. Angin\t: $kec_9 Km/Jam

Malam Hari
Cuaca\t:$lusa_malam
Suhu:\t: $suhu_10 C
Kelembaban\t: $hum_10 %
Arah Angin\t: $arah_10
Kec. Angin\t: $kec_10 Km/Jam
```";
    sendApiMsg($chatid, $text, false, 'Markdown');
}

function keteranganCuaca ($id_cuaca)
{
    if ($id_cuaca == '100') {
      $id_cuaca = '☀️'.'Cerah';
      return $id_cuaca;
    }elseif ($id_cuaca == '101') {
      $id_cuaca = '⛅️'.'Cerah Berawan';
      return $id_cuaca;
    }elseif ($id_cuaca == '102') {
      $id_cuaca = '⛅️'.'Cerah Berawan';
      return $id_cuaca;
    }elseif ($id_cuaca == '103') {
      $id_cuaca = '🌥'.'Berawan';
      return $id_cuaca;
    }elseif ($id_cuaca == '104') {
      $id_cuaca = '☁️'.'Berawan Tebal';
      return $id_cuaca;
    }elseif ($id_cuaca == '5') {
      $id_cuaca = '🌫'.'Udara Kabur';
      return $id_cuaca;
    }elseif ($id_cuaca == '10') {
      $id_cuaca = '🌫'.'Berasap';
      return $id_cuaca;
    }elseif ($id_cuaca == '45') {
      $id_cuaca = '🌫'.'Berkabut';
      return $id_cuaca;
    }elseif ($id_cuaca == '60') {
      $id_cuaca = '🌧'.'Hujan Ringan';
      return $id_cuaca;
    }elseif ($id_cuaca == '61') {
      $id_cuaca = '🌧'.'Hujan Sedang';
      return $id_cuaca;
    }elseif ($id_cuaca == '63') {
      $id_cuaca = '⛈'.'Hujan Lebat';
      return $id_cuaca;
    }elseif ($id_cuaca == '80') {
      $id_cuaca = '🌦'.'Hujan Lokal';
      return $id_cuaca;
    }elseif ($id_cuaca == '95') {
      $id_cuaca = '⛈⚡️'.'Hujan Petir';
      return $id_cuaca;
    }elseif ($id_cuaca == '97') {
      $id_cuaca = '⛈⚡️'.'Hujan Petir';
      return $id_cuaca;
    }else {
      $id_cuaca = "-";
      return $id_cuaca;
    }
}

function keyboardAktualCuacaBandara($chatid)
{
    $get_xml = simplexml_load_file("http://aviation.bmkg.go.id/latest/observation.x.xml.php?lang=id");
    $s = simplexml_import_dom($get_xml); //count jumlahnya
    $count = count($s ->report);

    for ($i=0; $i <= $count ; $i++) {
        $ii = $i + 1;
        if ($i != $count) {
            $kodeStasiun = $get_xml->report[$i]->icao_id;
            $namaStasiun = $get_xml->report[$i]->station_name;
            $inline = $ii.". $kodeStasiun ( $namaStasiun )";
            $coba[$i][0] = $inline;

        }else{
            $inline = "Ke Data BMKG 🏣 ";
            $coba[$i][0] = $inline;
        }
        $keyboard = $coba;
    }
    sendApiKeyboard($chatid, "_Daftar Nama Bandara_", $keyboard);
}

function showActualCuacaBandara($chatid, $rowAktual)
{
    $rowAktual = (int) $rowAktual;
    $i = $rowAktual - 1;
    $get_xml = simplexml_load_file("http://aviation.bmkg.go.id/latest/observation.x.xml.php?lang=id");

    $kodeStasiunShow = $get_xml->report[$i]->icao_id;
    $namaStasiunShow = $get_xml->report[$i]->station_name;
    $waktuShow = $get_xml->report[$i]->observed_time;
    $arahAnginShow = $get_xml->report[$i]->wind_direction;
    $kecAnginShow = $get_xml->report[$i]->wind_speed;
    $jarakPandangShow = $get_xml->report[$i]->visibility;
    $timeZone = $get_xml->report[$i]->time_zone;
    $cuacaShow = $get_xml->report[$i]->weather;

    $showcuacabandara = "
Informasi Cuaca Aktual Bandara
*$kodeStasiunShow *
_$namaStasiunShow _
```
Waktu         : $waktuShow $time_zone
Arah angin    : $arahAnginShow
Kec. Angin    : $kecAnginShow km/jam
Jarak Pandang : $jarakPandangShow km
Cuaca         : $cuacaShow
```
";
    sendApiMsg($chatid, $showcuacabandara ,$pesanid, 'Markdown');
}

function errorHandling($chatid)
{
  sendApiMsg($chatid, 'Terjadi Kesalahan Saat Pengambilan Data');
}

function keyboardPrakiraanCuacaBandara($chatid)
{
  $get_xml = simplexml_load_file("http://aviation.bmkg.go.id/latest/forecast.x.xml.php?s=1&lang=id");
  $s = simplexml_import_dom($get_xml); //count jumlahnya
  $count = count($s ->report);

  for ($i=0; $i <= $count ; $i++) {
      $ii = $i + 1;
      if ($i != $count) {
          $kodeStasiun = $get_xml->report[$i]->icao_id;
          $namaStasiun = $get_xml->report[$i]->station_name;
          $inline = $ii.". (".$kodeStasiun.") $namaStasiun";
          $coba[$i][0] = $inline;

      }else{
          $inline = "Ke Data BMKG 🏣 ";
          $coba[$i][0] = $inline;
      }
      $keyboard = $coba;
  }
  sendApiKeyboard($chatid, "_List Prakiraan Cuaca Bandara_", $keyboard);
}

function showPrakiraanCuacaBandara($chatid, $getBand, $pesanid)
{
    $get_xml = simplexml_load_file("http://aviation.bmkg.go.id/latest/forecast.x.xml.php?s=1&lang=id");
    $jumm = (int) $get_xml->report_count;
    $rest = substr($getBand, 1, -1);

    for ($i=1; $i <= 12 ; $i++) {
      $get_xml = simplexml_load_file("http://aviation.bmkg.go.id/latest/forecast.x.xml.php?s=$i&lang=id");
      for ($j=0; $j < $jumm ; $j++) {
        $string = $get_xml->report[$j]->icao_id;
        if ($rest == $string) {
            //$kodeStasiunShow [$i] = $get_xml->report[$i]->icao_id;
            //$namaStasiunShow [$i] = $get_xml->report[$i]->station_name;
            $data = $get_xml->report[$i]->forecast_time;
            $exWak = explode(" ", $data);
            $waktuShow [$i] = substr($exWak[1], 0, 5);
            $timeZone [$i] = $get_xml->report[$i]->time_zone;
            $arahAnginShow [$i] = $get_xml->report[$i]->wind_direction;
            $kecAnginShow [$i] = $get_xml->report[$i]->wind_speed;
            $jarakPandangShow [$i] = $get_xml->report[$i]->visibility;
            $cuacaShow [$i] = $get_xml->report[$i]->weather;
            //$bandaraArr [$i] = $string." hasil $i";
            //goto endBand;
        }
      }
    }

    //endBand:
      $txt =
"
```
====================================================
Waktu      Kec    Jarak      Arah     Cuaca
           Angin  Pandang    Angin
====================================================
";


    for ($i=1; $i <= 12 ; $i++) {
      //$txt .= $i." ini ". $bandaraArr[$i]."\n";
      $space1 = "        " ;
      $space1 = substr($space1, strlen($kecAnginShow [$i]));

      $space2 = "       " ;
      $space2 = substr($space2, strlen($jarakPandangShow [$i]));

      $space3 = "             " ;
      $space3 = substr($space3, strlen($arahAnginShow [$i]));

      // $space4 = "          " ;
      // $space4 = substr($space4, strlen($cuacaShow [$i]));


      $txt .= $waktuShow [$i] ." ".$timeZone [$i] ."   ".$kecAnginShow[$i].$space1. $jarakPandangShow [$i].$space2. $arahAnginShow[$i].$space3. $cuacaShow[$i]."\n";
      //$txt .="nama \t\t\t alamat \t\t\t\t nul \n";
    }

    $txt .=
"
Ket:
-Arah angin: Arah dari mana angin bertiup
-Arah angin Variabel: Arah angin selalu berubah-ubah
-Kec. Angin dlm satuan km/jam
-Jarak pandang dlm satuan KM
```";
    sendApiMsg($chatid, $txt, $pesanid, 'Markdown');
}

function KeyboardPeringatanCuaca($chatid)
{
  $keyboard = [
      [ '🏠 Kembali ke Home'],
      [ 'Warning Cuaca Aceh ⛔️'               , 'Warning Cuaca Sumatera_Utara ⛔️' ],
      [ 'Warning Cuaca Sumatera_Barat ⛔️'     , 'Warning Cuaca Riau ⛔️' ],
      [ 'Warning Cuaca Kep _Riau ⛔️'          , 'Warning Cuaca Jambi ⛔️' ],
      [ 'Warning Cuaca Sumatera_Selatan ⛔️'   , 'Warning Cuaca Bangka_Belitung ⛔️' ],
      [ 'Warning Cuaca Bengkulu ⛔️'           , 'Warning Cuaca Lampung ⛔️' ],
      [ 'Warning Cuaca DKI_Jakarta ⛔️'        , 'Warning Cuaca Jawa_Barat ⛔️' ],
      [ 'Warning Cuaca Banten ⛔️'             , 'Warning Cuaca Jawa_Tengah ⛔️' ],
      [ 'Warning Cuaca Yogyakarta ⛔️'         , 'Warning Cuaca Jawa_Timur ⛔️' ],
      [ 'Warning Cuaca Bali ⛔️'               , 'Warning Cuaca Nusa_Tenggara_Timur ⛔️' ],
      [ 'Warning Cuaca Nusa_Tenggara_Barat ⛔️', 'Warning Cuaca Kalimantan_Barat ⛔️' ],
      [ 'Warning Cuaca Kalimantan_Tengah ⛔️'  , 'Warning Cuaca Kalimantan_Selatan ⛔️' ],
      [ 'Warning Cuaca Kalimantan_Timur ⛔️'   , 'Warning Cuaca Kalimantan_Utara ⛔️' ],
      [ 'Warning Cuaca Sulawesi_Utara ⛔️'     , 'Warning Cuaca Sulawesi_Barat ⛔️' ],
      [ 'Warning Cuaca Sulawesi_Tengah ⛔️'    , 'Warning Cuaca Sulawesi_Tenggara ⛔️' ],
      [ 'Warning Cuaca Sulawesi_Selatan ⛔️'   , 'Warning Cuaca Gorontalo ⛔️' ],
      [ 'Warning Cuaca Maluku ⛔️'             , 'Warning Cuaca Maluku_Utara ⛔️' ],
      [ 'Warning Cuaca Papua_Barat ⛔️'        , 'Warning Cuaca Papua ⛔️' ],
  ];
  sendApiKeyboard($chatid, '_Peringatan Cuaca Provinsi Di Indonesia_', $keyboard);
}

function keyboardInfoMaritim($chatid)
{
  $keyboard = [
      [ '🏠 Kembali ke Home'],
      [ 'Maritim - Pelabuhan ⛵️', 'Maritim - Pelayanan 🛳' ],
      [ 'Maritim - Penyebrangan 🛥', 'Maritim - Wisata Bahari 🏝' ],
  ];
  sendApiKeyboard($chatid, '_Informasi Cuaca Maritim_', $keyboard);
}

function keyboardInfoMaritimLokasi($chatid, $nick)
{
  $keyboard = [
      [ '🏠 Kembali ke Home'],
      [ $nick.' Ambon', $nick.' Balikpapan' ],
      [ $nick.' Batam', $nick.' Belawan' ],
      [ $nick.' Biak', $nick.' Bitung' ],
      [ $nick.' Cilacap', $nick.' Denpasar' ],
      [ $nick.' Kendari', $nick.' Kupang' ],
      [ $nick.' Lampung', $nick.' Makasar' ],
      [ $nick.' Merauke', $nick.' Pontianak' ],
      [ $nick.' Semarang', $nick.' Sorong' ],
      [ $nick.' TanjungPerak', $nick.' TanjungPriok' ],
      [ $nick.' TelukBayur', $nick.' Ternate' ],

  ];
  sendApiKeyboard($chatid, '_Informasi Cuaca Maritim_', $keyboard);
}
function infoMaritim($chatid, $pesan)
{
    //sendApiMsg($chatid, "berhasil masuk $pesan");
    $info = explode(" ", $pesan);
    if ($info[0] == "(M1)") {
        //sendApiMsg($chatid, "arr : ".$info[0].", arr2 : ".$info[1]);
        $id = $info[1];
        $get_xml = simplexml_load_file("https://mobiledata.bmkg.go.id/datamkg/Meteorologi/xml/Maritim-Cuaca-Pelabuhan-".$id.".xml");
        $s = simplexml_import_dom($get_xml); //count jumlahnya
        $jumlah = count($s ->forecast->harbor->port);
        if ($jumlah != 0) {
          sendApiMsg($chatid, "Terdapat $jumlah Pelabuhan.." );
          loopInfoMaritim($chatid, $jumlah, $get_xml);
        }else {
          sendApiMsg($chatid, "Tidak Ada Informasi Data Pelabuhan.." );
        }


    }elseif ($info[0] == "(M3)") {
        //sendApiMsg($chatid, "arr : ".$info[0].", arr2 : ".$info[1]);
        $id = $info[1];
        $get_xml = simplexml_load_file("https://mobiledata.bmkg.go.id/datamkg/Meteorologi/xml/Maritim-Cuaca-Pelayanan-".$id.".xml");
        $s = simplexml_import_dom($get_xml); //count jumlahnya
        $jumlah = count($s->forecast->service->timerange[0]->area);

        if ($jumlah != 0) {
          sendApiMsg($chatid, "Terdapat $jumlah Pelayanan.." );
          loopPelayananMaritim($chatid, $jumlah, $get_xml);
        }else {
          sendApiMsg($chatid, "Tidak Ada Informasi Data Pelabuhan.." );
        }

    }elseif ($info[0] == "(M2)") {
      //sendApiMsg($chatid, "arr : ".$info[0].", arr2 : ".$info[1]);
      $id = $info[1];
      $get_xml = simplexml_load_file("https://mobiledata.bmkg.go.id/datamkg/Meteorologi/xml/Maritim-Cuaca-Penyebrangan-".$id.".xml");
      $s = simplexml_import_dom($get_xml); //count jumlahnya
      $jumlah = count($s ->forecast->ferriage->area);

      if ($jumlah != 0) {
        sendApiMsg($chatid, "Terdapat $jumlah Penyebrangan.." );
        loopPenyebranganMaritim($chatid, $jumlah, $get_xml);
      }else {
        sendApiMsg($chatid, "Tidak Ada Informasi Data Pelabuhan.." );
      }

    }elseif ($info[0] == "(M4)") {
      //sendApiMsg($chatid, "arr : ".$info[0].", arr2 : ".$info[1]);
      $id = $info[1];
      $get_xml = simplexml_load_file("https://mobiledata.bmkg.go.id/datamkg/Meteorologi/xml/Maritim-Wisata-Bahari-".$id.".xml");
      $s = simplexml_import_dom($get_xml); //count jumlahnya
      $jumlah = count($s ->forecast->marine_tourism->beach);


      if ($jumlah != 0) {
        sendApiMsg($chatid, "Terdapat $jumlah Wisata Bahari.." );
        loopWisataBahari($chatid, $jumlah, $get_xml);
      }else {
        sendApiMsg($chatid, "Tidak Ada Informasi Data Wisata Bahari.." );
      }
    }
}

function loopInfoMaritim($chatid, $jumlah, $get_xml)
{
    for ($i=0; $i < $jumlah ; $i++) {

        $port_name        = $get_xml->forecast->harbor->port[$i]->port_name;
        $port_description = $get_xml->forecast->harbor->port[$i]->port_description;
        $weather          = $get_xml->forecast->harbor->port[$i]->timerange->parameter[0]->value;
        $wd_from          = $get_xml->forecast->harbor->port[$i]->timerange->parameter[1]->value[0];
        $wd_to            = $get_xml->forecast->harbor->port[$i]->timerange->parameter[2]->value[0];
        $ws_min           = $get_xml->forecast->harbor->port[$i]->timerange->parameter[3]->value[0];
        $ws_max           = $get_xml->forecast->harbor->port[$i]->timerange->parameter[4]->value[0];
        $wave_min         = $get_xml->forecast->harbor->port[$i]->timerange->parameter[5]->value;
        $wave_max         = $get_xml->forecast->harbor->port[$i]->timerange->parameter[6]->value;
        $visibility       = $get_xml->forecast->harbor->port[$i]->timerange->parameter[7]->value;
        $t_min            = $get_xml->forecast->harbor->port[$i]->timerange->parameter[8]->value;
        $t_max            = $get_xml->forecast->harbor->port[$i]->timerange->parameter[9]->value;
        $hum_min          = $get_xml->forecast->harbor->port[$i]->timerange->parameter[10]->value;
        $hum_max          = $get_xml->forecast->harbor->port[$i]->timerange->parameter[11]->value;

        $news = "Prakiraan Cuaca Maritim Pelabuhan
  -------------------------------------------
  *$port_description *, $port_name
  ```
  Cuaca : $weather
  Angin : $wd_from - $wd_to , $ws_min - $ws_max Knot
  Gelombang : $wave_min - $wave_max m
  Visibility : $visibility meter
  ```";
  // Suhu Udara :
  // - Min :  $t_min C
  // - Max  :  $t_max C
  // Kelembaban
  // - Min :  $hum_min %
  // - Max :  $hum_max %

        sendApiMsg($chatid, $news, false, 'Markdown');
    }
}
function loopPelayananMaritim($chatid, $jumlah, $get_xml)
{
    for ($i=0; $i < $jumlah ; $i++) {

        $port_name        = $get_xml->forecast->service->timerange[0]->area[$i]->NameArea;
        $weather          = $get_xml->forecast->service->timerange[0]->area[$i]->parameter[0]->value[0];
        $wd_from          = $get_xml->forecast->service->timerange[0]->area[$i]->parameter[1]->value[0];
        $wd_to            = $get_xml->forecast->service->timerange[0]->area[$i]->parameter[2]->value[0];
        $ws_min           = $get_xml->forecast->service->timerange[0]->area[$i]->parameter[3]->value[0];
        $ws_max           = $get_xml->forecast->service->timerange[0]->area[$i]->parameter[4]->value[0];
        $wave_min         = $get_xml->forecast->service->timerange[0]->area[$i]->parameter[5]->value;
        $wave_max         = $get_xml->forecast->service->timerange[0]->area[$i]->parameter[6]->value;
        $warning          = $get_xml->forecast->service->timerange[0]->area[$i]->parameter[7]->value;

        $news = "Prakiraan Cuaca Pelayanan Maritim
  -------------------------------------------
  *$port_name *
  ```
  Cuaca : $weather
  Angin : $wd_from - $wd_to , $ws_min - $ws_max Knot
  Gelombang : $wave_min - $wave_max m
  Warning : $warning
  ```";
  // Suhu Udara :
  // - Min :  $t_min C
  // - Max  :  $t_max C
  // Kelembaban
  // - Min :  $hum_min %
  // - Max :  $hum_max %

        sendApiMsg($chatid, $news, false, 'Markdown');
    }
}
function loopPenyebranganMaritim($chatid, $jumlah, $get_xml)
{
    for ($i=0; $i < $jumlah ; $i++) {

        $port_name        = $get_xml->forecast->ferriage->area[$i]->NameArea[0];
        $weather          = $get_xml->forecast->ferriage->area[$i]->timerange->parameter[0]->value;
        $wd_from          = $get_xml->forecast->ferriage->area[$i]->timerange->parameter[1]->value[0];
        $wd_to            = $get_xml->forecast->ferriage->area[$i]->timerange->parameter[2]->value[0];
        $ws_min           = $get_xml->forecast->ferriage->area[$i]->timerange->parameter[3]->value[0];
        $ws_max           = $get_xml->forecast->ferriage->area[$i]->timerange->parameter[4]->value[0];
        $wave_min         = $get_xml->forecast->ferriage->area[$i]->timerange->parameter[5]->value;
        $wave_max         = $get_xml->forecast->ferriage->area[$i]->timerange->parameter[6]->value;
        //$warning          = $get_xml->forecast->ferriage->timerange[0]->area[$i]->parameter[7]->value;

        $news = "Prakiraan Cuaca Penyebrangan Maritim
  -------------------------------------------
  *$port_name *
  ```
  Cuaca : $weather
  Angin : $wd_from - $wd_to , $ws_min - $ws_max Knot
  Gelombang : $wave_min - $wave_max m
  ```";

        sendApiMsg($chatid, $news, false, 'Markdown');
    }
}

function loopWisataBahari($chatid, $jumlah, $get_xml)
{
    $news = "Prakiraan Cuaca Wisata Bahari";
    for ($x=0; $x < $jumlah ; $x++) {

        $port_name        = $get_xml->forecast->marine_tourism->beach[$x]->beach_name;
        $weather          = $get_xml->forecast->marine_tourism->beach[$x]->data->timerange[0]->parameter[2]->value;
        $wd_from          = $get_xml->forecast->marine_tourism->beach[$x]->data->timerange[0]->parameter[3]->value[1];
        $ws_max           = $get_xml->forecast->marine_tourism->beach[$x]->data->timerange[0]->parameter[4]->value[0];
        $wave_height         = $get_xml->forecast->marine_tourism->beach[$x]->data->timerange[0]->parameter[1]->value[0];
        //$warning          = $get_xml->forecast->ferriage->timerange[0]->area[$i]->parameter[7]->value;
        $ii = $x+1;
        $news .= "
  -------------------------------------------
  $ii *$port_name *
  ```
  Cuaca : ".keteranganCuaca ($weather)."
  Angin : $wd_from , $ws_max Knot
  Gelombang : $wave_height m
  ```";
      if ($ii % 5 == 0) {
          sendApiMsg($chatid, $news, false, 'Markdown');
          $news = "Prakiraan Cuaca Wisata Bahari";
      }

    }
    if ($ii % 5 != 0) {
        sendApiMsg($chatid, $news, false, 'Markdown');
    }
}

function warningCuaca($chatid, $pesan, $pesanid)
{
    $new = explode(" ", $pesan);
    $temp = $new[2];
    sendApiMsg($chatid, $temp);

    //sendApiMsg($chatid, $pesan );
    $get_xml = simplexml_load_file("http://mobiledata.bmkg.go.id/datamkg/MEWS/DigitalForecast/WarningsXML-".$temp.".xml");
    $tanggal = $get_xml->warnings->data['date'];

    $report_1 = $get_xml->warnings->reports->report[1]->text;
    $report_2 = $get_xml->warnings->reports->report[2]->text;

    $text = "Info Peringatan Cuaca

*Hari ini : *
`$report_1 `

*Esok Hari: *
`$report_2 `
";

    sendApiMsg($chatid, $text , $pesanid, 'Markdown');
}
