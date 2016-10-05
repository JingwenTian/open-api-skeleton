<?php 

/**
 * 调试打印
 *
 * @return [type] [description]
 */
function p()
{
    $args=func_get_args();  
    if(count($args)<1) {
        return;
    }
    echo '<div style="width:100%;text-align:left; background-color: #fff;"><pre style="white-space:pre">';
    foreach($args as $arg){
        if(is_array($arg)){
            print_r($arg);
            echo '<br>';
        }else if(is_string($arg)){
            echo $arg.'<br>';
        }else{
            var_dump($arg);
            echo '<br>';
        }
    }
    echo '</pre></div>';
}

/**
 * 从数组获取下标Key的vlaue
 *
 * @param Array   $array   [description]
 * @param [type]  $key     [description]
 * @param boolean $default [description]
 *
 * @return [type]  [description]
 */
function array_get( Array $array , $key , $default = false )
{
    return isset($array[$key]) ? $array[$key] : $default ;
}


function curl($url, $getParam = [], $postParam = [], $requestMethod = 'GET', $isHttps = false)
{
    $url .= '?' . http_build_query($getParam);

    $ch = curl_init();

    if ($requestMethod == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postParam);
    }

    if ($isHttps === true) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}