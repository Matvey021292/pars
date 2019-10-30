<?php
/* cloudflare.php
 * by daif alotaibi (http://daif.net)
 * daif55@gmail.com 
*/


$url = 'https://librusec.pro';
$data = OpenURLcloudflare($url);
print $data;



function OpenURLcloudflare($url) {
    //get cloudflare ChallengeForm
    $data = OpenURL($url);
    preg_match('/<form id="ChallengeForm" .+ name="act" value="(.+)".+name="jschl_vc" value="(.+)".+<\/form>.+jschl_answer.+\(([0-9\+\-\*]+)\);/Uis',$data,$out);
    if(count($out)>0) {
        eval("\$jschl_answer=$out[3];");
        $post['act']            = $out[1];
        $post['jschl_vc']        = $out[2];
        $post['jschl_answer']    = $jschl_answer;
        //send jschl_answer to the website
        $data = OpenURL($url, $post);
    }
    return($data);
}

function OpenURL($url, $post=array()) {
    $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:13.0) Gecko/20100101 Firefox/13.0.1';
    $headers[] = 'Accept: application/json, text/javascript, */*; q=0.01';
    $headers[] = 'Accept-Language: ar,en;q=0.5';
    $headers[] = 'Connection: keep-alive';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    if(count($post)>0) {
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/curl.cookie');
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/curl.cookie');
    $data = curl_exec($ch);
    return($data);
}
?>