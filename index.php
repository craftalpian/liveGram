<?php
// Mengatur lokasi sekarang
date_default_timezone_set('Asia/Jakarta');
error_reporting(0);
$datetime = date("d-m-Y h:i:s");

// Fungsi yang digunakan

// Mengambil CSRF token langsung dari Instagram
function getCSRF(){
  $fgc    =   file_get_contents("https://www.instagram.com");
  $explode    =   explode('"csrf_token":"',$fgc);
  $explode    =   explode('"',$explode[1]);
  return $explode[0];
}

// Membuat UUID
function generateUUID($keepDashes = true){
  $uuid = sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff)
  );
  return $keepDashes ? $uuid : str_replace('-', '', $uuid);
}

// Membuat signed_body untuk UA : Instagram 24.0.0.12.201 Android
function hookGenerate($hook){
  return 'ig_sig_key_version=4&signed_body=' . hash_hmac('sha256', $hook, '5bd86df31dc496a3a9fddb751515cc7602bdad357d085ac3c5531e18384068b4') . '.' . urlencode($hook);
}

// Fungsi request untuk mengirim data
function request($url,$hookdata,$cookie,$method='GET'){
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, "https://i.instagram.com/api".$url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  if($method == 'POST'){
    curl_setopt($ch, CURLOPT_POSTFIELDS, $hookdata);
    curl_setopt($ch, CURLOPT_POST, 1);
  }else{
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
  }

  $headers = array();
  $headers[] = "Accept: */*";
  $headers[] = "Content-Type: application/x-www-form-urlencoded";
  $headers[] = 'Cookie2: _ENV["Version=1"]';
  $headers[] = "Accept-Language: en-US";
  $headers[] = "User-Agent: Instagram 24.0.0.12.201 Android (28/9; 320dpi; 720x1280; samsung; SM-J530Y; j5y17ltedx; samsungexynos7870; in_ID;)";
  $headers[] = "Host: i.instagram.com";
  if($cookie !== "0"){
    $headers[] = "Cookie: ".$cookie;
  }
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
	$httpcode  = curl_getinfo($ch);
	$header    = substr($result, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
	$body      = substr($result, curl_getinfo($ch, CURLINFO_HEADER_SIZE));

  if(curl_errno($ch)){
    echo 'Error:' . curl_error($ch);
  }
  curl_close ($ch);
  return array($header, $body, $httpcode,$result,$url,$hookdata); // body itu response body
}

echo "Selamat datang di
 _     _____     _______ ____ ____      _    __  __
| |   |_ _\ \   / / ____/ ___|  _ \    / \  |  \/  |
| |    | | \ \ / /|  _|| |  _| |_) |  / _ \ | |\/| |
| |___ | |  \ V / | |__| |_| |  _ <  / ___ \| |  | |
|_____|___|  \_/  |_____\____|_| \_\/_/   \_\_|  |_|

© Pianjammalam 2019

-----------------------------------------------------------

Sebelum melanjutkan, dengan anda menggunakan aplikasi ini, berarti anda menerima segala konsekuensi dan mengikuti aturan yang berlaku.

Username : @";
$username = trim(fgets(STDIN));
echo "Password : ";
$password = trim(fgets(STDIN));

$genDevId = generateDeviceId();
$tryLogin = request('/v1/accounts/login/',hookGenerate('{"phone_id":"'.generateUUID().'","_csrftoken":"'.getCSRF().'","username":"'.$username.'","adid":"'.generateUUID().'","guid":"'.generateUUID().'","device_id":"'.$genDevId.'","password":"'.$password.'","login_attempt_count":"0"}'),0,"POST");

if(json_decode($tryLogin[1],true)['logged_in_user']['username'] == strtolower($username)){
  if (strpos($tryLogin[0], 'set-cookie') !== false) {
    preg_match_all('%set-cookie: (.*?);%',$tryLogin[0],$d);$cookie = '';
    for($o=0;$o<count($d[0]);$o++){$cookie.=$d[1][$o].";";}
  }else{
    preg_match_all('%Set-Cookie: (.*?);%',$tryLogin[0],$d);$cookie = '';
    for($o=0;$o<count($d[0]);$o++){$cookie.=$d[1][$o].";";}
  }

  $pk = json_decode($tryLogin[1],true)['logged_in_user']['pk'];

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, "https://i.instagram.com/api/v1/live/create/");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, '_csrftoken='.getCSRF().'&preview_height=1920&_uuid='.generateUUID().'&broadcast_type=RTMP_SWAP_ENABLED&preview_width=1080&internal_only=0');
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

  $headers = array();
  $headers[] = 'X-Ig-Connection-Type: WIFI';
  $headers[] = 'User-Agent: Instagram 35.0.0.20.96 Android (21/5.0; 480dpi; 1080x1920; asus; ASUS_Z00AD; Z00A_1; mofd_v1; in_ID; 95414347)';
  $headers[] = 'Accept-Language: id-ID, en-US';
  $headers[] = 'Cookie: '.$cookie;
  $headers[] = 'Host: i.instagram.com';
  $headers[] = 'X-Fb-Http-Engine: Liger';
  $headers[] = 'Content-Type: application/x-www-form-urlencoded';
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $resultAwal = curl_exec($ch);
  if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
  }
  curl_close($ch);
  echo "Stream id : ".json_decode($resultAwal,true)['broadcast_id']."
";
  echo "Main url : rtmps://live-upload.instagram.com:443/rtmp/
";
  echo "Last url : ".explode("/rtmp/",json_decode($resultAwal,true)['upload_url'])[1]."
";

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, "https://i.instagram.com/api/v1/live/".json_decode($resultAwal,true)['broadcast_id']."/start/");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, '_csrftoken='.getCSRF().'&_uuid='.generateUUID().'');
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

  $headers = array();
  $headers[] = 'X-Ig-Connection-Type: WIFI';
  $headers[] = 'User-Agent: Instagram 35.0.0.20.96 Android (21/5.0; 480dpi; 1080x1920; asus; ASUS_Z00AD; Z00A_1; mofd_v1; in_ID; 95414347)';
  $headers[] = 'Accept-Language: id-ID, en-US';
  $headers[] = 'Cookie: '.$cookie;
  $headers[] = 'X-Fb-Http-Engine: Liger';
  $headers[] = 'Content-Type: application/x-www-form-urlencoded';
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
  }
  curl_close($ch);

  echo "Ingin mengakhiri ? (ketik 1)";
  $akhiri = trim(fgets(STDIN));

  if($akhiri == '1'){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://i.instagram.com/api/v1/live/".json_decode($resultAwal,true)['broadcast_id']."/end_broadcast/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, hookGenerate('{"_csrftoken":"'.getCSRF().'","_uid":"'.$pk.'","_uuid":"'.generateUUID().'","end_after_copyright_warning":"false"}'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

    $headers = array();
    $headers[] = 'X-Ig-Connection-Type: WIFI';
    $headers[] = 'User-Agent: Instagram 35.0.0.20.96 Android (21/5.0; 480dpi; 1080x1920; asus; ASUS_Z00AD; Z00A_1; mofd_v1; in_ID; 95414347)';
    $headers[] = 'Accept-Language: id-ID, en-US';
    $headers[] = 'Cookie: '.$cookie;
    $headers[] = 'X-Fb-Http-Engine: Liger';
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $resultAkhir = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    echo json_decode($resultAkhir,true)['status'];
  }

}
