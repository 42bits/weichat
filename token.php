
<?php
//客服文本回复
    function nokefutext($openid,$content=''){
            $token = $this->GetToken();
    $data = '{
        "touser":"'.$openid.'",
        "msgtype":"text",
        "text":
        {
             "content":"'.$content.'"
        }
    }';

    $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$token;

    $result = $this->https_post($url,$data);
    $final = json_decode($result);
    }

//客服图文回复
    function nofenxiao($openid,$title='',$description='',$url='',$picurl=''){
            $token = $this->GetToken();

$data = '{
    "touser":"'.$openid.'",
    "msgtype":"news",
    "news":{
        "articles": [
         {
             "title":"'.$title.'",
             "description":"'.$description.'",
             "url":"'.$url.'",
             "picurl":"'.$picurl.'"
         }
         ]
    }
}';

    $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$token;

    $result = $this->https_post($url,$data);
    $final = json_decode($result);

    }



    function https_post($url,$data){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
           return 'Errno'.curl_error($curl);
        }
        curl_close($curl);
        return $result;
    }

    public  function  GetToken(){

            $sql = "SELECT * FROM `wxf_configure` order by id asc limit 1";
            $query = $this->db->query($sql);
            $info = $query->row();

          if($info->AppId && $info->AppSecret){

                 if(time()-($info->access_token_time) >7200 ||  !($info->access_token)){

                     $getToken = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$info->AppId.'&secret='.$info->AppSecret;

                     $token = json_decode(@file_get_contents($getToken),true);

                     $add['access_token']= isset($token['access_token']) ?  $token['access_token'] : '' ;

                     $add['access_token_time']=time();

                     $this->db->update("wxf_configure",$add,array("AppId"=>$info->AppId));

                     return  isset($token['access_token']) ?  $token['access_token'] : '';

                 }
                 else{
                     return  $info->access_token;
                 }
          }
          else{
               $access_token=' ';
               return  $access_token;
          }
    }
