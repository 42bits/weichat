<?php

class Oauth extends CI_Model{

	function __construct(){
        parent::__construct();
		$this->load->database();
    }

    public function getoauth($func){
    		$sql = "SELECT `AppId`,`AppSecret` FROM `wxf_configure` order by id asc limit 1";
    		$query = $this->db->query($sql);
    		$res = $query->row_array();
    		$appid = $res['AppId'];
    		$appsecret = $res['AppSecret'];
    		$back =$func;
			$returnurl = urlencode($back);
			$urls ="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$returnurl."&response_type=code&scope=snsapi_base &state=123#wechat_redirect";
			header("Location: ".$urls);
			exit;
    }

    public function back(){
    		if( ! isset($_GET['code'])){
				show_404();
			}
    	    $sql = "SELECT * FROM `wxf_configure` order by id asc limit 1";
    		$query = $this->db->query($sql);
    		$res = $query->row_array();
    		$appid = $res['AppId'];
    		$appsecret = $res['AppSecret'];

			$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$_GET['code']."&grant_type=authorization_code";
			$curl = curl_init(); // 启动一个CURL会话
			            curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
			            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
			            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加
			            curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
			            curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
			            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
			            $output = curl_exec($curl); // 执行操作
			            curl_close($curl); // 关闭CURL会话
			$token = @json_decode($output, true);

			return $token['openid'];
    }
	public  function  getyz(){


		//测试

		if(isset($_GET['ref'])){

		     setcookie('openid',$_GET['ref'], time()+3600*24,"/");

		}




		//如果有那么说明是分享的链接进来的
		if(isset($_GET['parent'])){
			//上级浏览数加一
			$this->mycenter->showadd($_GET['parent']);
			//application\models\wxf_fans.php 用来判断是否通过链接来关注
			if(@$_SESSION['parent']){
				unset($_SESSION['parent']);
				$_SESSION['parent'] = $_GET['parent'];
			}else{
				$_SESSION['parent'] = $_GET['parent'];
			}
		}
		//判断openId是否存在
		if(isset($_COOKIE['openid'])){
		      $openid                        =  $_COOKIE['openid'];
		      $this->load->database();
		      $rw=$this->db->query("select * from  `wxf_fans`  where  `wx_acount`='".$openid."'")->row_array();;
		      if(!$rw){
			      $rw['wx_acount']=$token['openid'];
				  return  $rw;
			  }
			  return  $rw;
		}
	    else{
				if(isset($_GET['code'])){
					  $openid=$this->back();
					  $ip=$_SERVER['HTTP_HOST'];
					  setcookie('openid',$openid, time()+3600*24,"/",$ip);
					  //判断会员是不是第一次
					  $this->load->database();
					  $rw=$this->db->query(" select *  from  `wxf_fans`  where  `wx_acount`='".$openid."'")->row_array();
				      if(!$rw){
					     $rw=array();
						 $rw['wx_acount']=$openid;
						 return  $rw;
					  }
					  return  $rw;
				}
				else{
					$tr=$_SERVER['REQUEST_URI'];
					$yurl="http://".$_SERVER['HTTP_HOST'].$tr;
					$this->getoauth($yurl);
				}
		}
	}






}
