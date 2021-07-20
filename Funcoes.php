<?
header("Content-Type: text/html; charset=ISO-8859-1",true);

function utf_iso(&$a){
if (is_array($a)){
foreach ($a as $k => $v) {
if (!is_array($v)){
$a[$k] = utf8_decode($a[$k]);
} else {
utf_iso($a[$k]);
}
}
} else {
$a = utf8_decode($a);
}
return $a;
}	

class CtrlPix
{
	
	#-----------------
	# Configurações
	
    public $DadReq                  = array();
	public $IsDebug					= false;
    
	public $Urls1					= array("Producao"	=> "https://oauth.bb.com.br/",
											"Teste"		=> "https://oauth.hm.bb.com.br/");
	public $Urls2					= array("Producao"	=> "https://api.bb.com.br/",
											"Teste"		=> "https://api.hm.bb.com.br/");
	public $UrlsCur1			    = "";
	public $UrlsCur2			    = "";

    public $AccessToken             = "";
    public $AccessTokenRet          = array();

	#-----------------
	# Init
	
	public function __construct($DadReq, $IsDebug = false)
	{

		#-----------------
		# Init
		
		$this->DadReq			    = $DadReq;
        $this->IsDebug			    = $IsDebug;

		#-----------------
		# Endpoint
		
		if( $this->DadReq["Ambiente"] == "T" )
		{
		
			$this->UrlsCur1					= $this->Urls1["Teste"];
			$this->UrlsCur2					= $this->Urls2["Teste"];
		
		}else{
		
			$this->UrlsCur1					= $this->Urls1["Producao"];
			$this->UrlsCur2					= $this->Urls2["Producao"];
		
		}
		
		#-----------------
		
	}
	
	# ###########################
	# Utilitarios
	# ###########################
	
	public function CallRet($err, $httpcode, $response)
	{
		
		# --------------	
		
		if( $err != "" )
		{
			
			$RetFinal		= array("IsOK"		=> false,
									"Mensagem"	=> "cURL Error #:" . $err);
			
		}else{
		
			$DadEle			= utf_iso(json_decode($response, true));
			
			if( $httpcode == 200 || $httpcode == 201 || $httpcode == 202 )
			{
				
				$RetFinal		= array("IsOK"		=> true,
										"Dados"		=> $DadEle);
				
			}else{
				
				if( isset($DadEle["message"]) ){
					
					$RetFinal		= array("IsOK"		=> false,
                                            "Dados"		=> $DadEle,
                                            "response"	=> $response,
											"Mensagem"	=> $DadEle["message"]);

                }elseif( isset($DadEle["mensagem"]) ){
					
					$RetFinal		= array("IsOK"		=> false,
                                            "Dados"		=> $DadEle,
                                            "response"	=> $response,
											"Mensagem"	=> $DadEle["mensagem"]);

                }elseif( isset($DadEle["error_description"]) ){
					
					$RetFinal		= array("IsOK"		=> false,
                                            "Dados"		=> $DadEle,
                                            "response"	=> $response,
											"Mensagem"	=> $DadEle["error_description"]);
                                
                }elseif( isset($DadEle["error_messages"][0]["description"]) ){

                    $RetFinal		= array("IsOK"		=> false,
                                            "Dados"		=> $DadEle,
                                            "response"	=> $response,
                                            "Mensagem"	=> $DadEle["error_messages"][0]["description"]);

                }elseif( isset($DadEle["erros"][0]["mensagem"]) ){

                    $RetFinal		= array("IsOK"		=> false,
                                            "Dados"		=> $DadEle,
                                            "response"	=> $response,
                                            "Mensagem"	=> $DadEle["erros"][0]["mensagem"]);
                                            
				}else{
					
					$RetFinal		= array("IsOK"		=> false,	
                                            "Dados"		=> $DadEle,
                                            "response"	=> $response,
											"Mensagem"	=> $response);
										
				}
				
			}
		
		}
		
		# --------------	
		
		return $RetFinal;
		
		# --------------	
		
	}
	
    # ###########################

    public function GerarQRCode($HashQRCode)
	{
		
		# --------------	
		
        $DadImg             = substr(md5($HashQRCode), 0, 30) . "_" . mktime() . ".png";
        $DadPath            = "QRCode/" . $DadImg;

        # *********

        QRcode::png($HashQRCode, $DadPath, "L", 4, 2);   
        
        # *********

        $RetFinal           = "data:image/png;base64," . base64_encode(file_get_contents($DadPath));
        @unlink($DadPath);
        
        # *********
        
        return $RetFinal;
		
		# --------------	
		
	}
	
	# ###########################
	
	public function Util_LimparString($TmpNome)
	{
		
		# -------------------------------
		
		$TmpNome			= str_replace(".", "", $TmpNome);
		$TmpNome			= str_replace("-", "", $TmpNome);
		$TmpNome			= str_replace("/", "", $TmpNome);
		$TmpNome			= str_replace(".", "", $TmpNome);
		$TmpNome			= str_replace("-", "", $TmpNome);
		$TmpNome			= str_replace("/", "", $TmpNome);
		$TmpNome			= str_replace("(", "", $TmpNome);
		$TmpNome			= str_replace(")", "", $TmpNome);
		$TmpNome			= str_replace(" ", "", $TmpNome);
		$TmpNome			= str_replace("_", "", $TmpNome);
		
		return $TmpNome;
		
		# -------------------------------
		
	}
	
	# ###########################
	# Chamadas
	# ###########################
	
	public function GerAutorizacao1()
	{
		
		# --------------
		
		return base64_encode($this->DadReq["client_id"] . ":" . $this->DadReq["client_secret"]);
		
		# --------------	
		
	}
	
	# ###########################
    
    public function GerToken()
	{
		
		# --------------

		$curl 		= curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL 				=> $this->UrlsCur1 . "oauth/token",
		CURLOPT_RETURNTRANSFER 		=> true,
		CURLOPT_MAXREDIRS 			=> 10,
		CURLOPT_TIMEOUT 			=> 30,
        CURLOPT_SSL_VERIFYHOST 		=> false,
		CURLOPT_SSL_VERIFYPEER 		=> false,
        CURLOPT_CUSTOMREQUEST       => "POST",
        CURLOPT_POSTFIELDS          => http_build_query(array("grant_type" => "client_credentials")),
		CURLOPT_HTTPHEADER 			=> array(
        "Authorization: Basic " . $this->GerAutorizacao1(),
        "Content-Type: application/x-www-form-urlencoded"
        ),
		));
		
		$response 	= curl_exec($curl);
        $response2  = json_decode($response, true);
		$httpcode	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err 		= curl_error($curl);
		
		curl_close($curl);
		
		# --------------	

        if( $response2["access_token"] != "" )
        {

            $this->AccessToken      = $response2["access_token"];

        }else{

            $this->AccessTokenRet   = $this->CallRet($err, $httpcode, $response);

        }
		
		# --------------	
		
	}
	
	# ###########################
	# Chamadas
	# ###########################
    
    public function CallGet($Url, $Query = array())
	{
		
		# --------------
        
        $this->GerToken();

        if( $this->AccessToken == "" )
        {

            return $this->AccessTokenRet;

        }

        # --------------
		
        $Query["gw-dev-app-key"]        = $this->DadReq["developer_application_key"];

        # --------------
        
		$curl 		= curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL 				=> $this->UrlsCur2 . $Url . "?" . http_build_query($Query),
		CURLOPT_RETURNTRANSFER 		=> true,
		CURLOPT_MAXREDIRS 			=> 10,
		CURLOPT_TIMEOUT 			=> 30,
        CURLOPT_SSL_VERIFYHOST 		=> false,
		CURLOPT_SSL_VERIFYPEER 		=> false,
		CURLOPT_CUSTOMREQUEST		=> "GET",
		CURLOPT_HTTPHEADER 			=> array(
        "accept: application/json",
        "content-type: application/json",
        "Authorization: Bearer " . $this->AccessToken
        ),
		));
		
		$response 	= curl_exec($curl);
		$httpcode	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err 		= curl_error($curl);
		
		curl_close($curl);
		
		# --------------	
		
		return $this->CallRet($err, $httpcode, $response);
		
		# --------------	
		
	}
	
	# ###########################
	
	public function CallPost($Url, $Dados = array(), $Query = array())
	{
		
		# --------------
        
        $this->GerToken();

        if( $this->AccessToken == "" )
        {

            return $this->AccessTokenRet;

        }

        # --------------
		
        $Query["gw-dev-app-key"]        = $this->DadReq["developer_application_key"];

        # --------------
		
		$curl 		= curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL 				=> $this->UrlsCur2 . $Url . "?" . http_build_query($Query),
		CURLOPT_RETURNTRANSFER 		=> true,
		CURLOPT_MAXREDIRS 			=> 10,
		CURLOPT_TIMEOUT 			=> 30,
        CURLOPT_SSL_VERIFYHOST 		=> false,
		CURLOPT_SSL_VERIFYPEER 		=> false,
		CURLOPT_CUSTOMREQUEST		=> "POST",
		CURLOPT_POSTFIELDS 			=> ( count($Dados) > 0 ) ? json_encode($Dados) : "",
		CURLOPT_HTTPHEADER 			=> array(
        "accept: application/json",
        "content-type: application/json",
        "Authorization: Bearer " . $this->AccessToken
        ),
		));
		
		$response 	= curl_exec($curl);
		$httpcode	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err 		= curl_error($curl);
		
        curl_close($curl);
        
		# --------------	
		
		
		return $this->CallRet($err, $httpcode, $response);
		
		# --------------		
		
	}
	
	# ###########################
	
	public function CallPut($Url, $Dados = array(), $Query = array())
	{
		
		# --------------
        
        $this->GerToken();

        if( $this->AccessToken == "" )
        {

            return $this->AccessTokenRet;

        }

        # --------------
		
        $Query["gw-dev-app-key"]        = $this->DadReq["developer_application_key"];

        # --------------

		$curl 		= curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL 				=> $this->UrlsCur2 . $Url . "?" . http_build_query($Query),
		CURLOPT_RETURNTRANSFER 		=> true,
		CURLOPT_MAXREDIRS 			=> 10,
		CURLOPT_TIMEOUT 			=> 30,
        CURLOPT_SSL_VERIFYHOST 		=> false,
		CURLOPT_SSL_VERIFYPEER 		=> false,
		CURLOPT_CUSTOMREQUEST		=> "PUT",
		CURLOPT_POSTFIELDS 			=> ( count($Dados) > 0 ) ? json_encode($Dados) : "",
		CURLOPT_HTTPHEADER 			=> array(
        "accept: application/json",
        "content-type: application/json",
        "Authorization: Bearer " . $this->AccessToken
        ),
        ));
		
		$response 	= curl_exec($curl);
		$httpcode	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err 		= curl_error($curl);
		
		curl_close($curl);
		
		# --------------	
		
		return $this->CallRet($err, $httpcode, $response);
		
		# --------------	
		
	}
	
	# ###########################
	
	public function CallDelete($Url, $Query = array())
	{
		
		# --------------
        
        $this->GerToken();

        if( $this->AccessToken == "" )
        {

            return $this->AccessTokenRet;

        }

        # --------------
		
        $Query["gw-dev-app-key"]        = $this->DadReq["developer_application_key"];

        # --------------
		
		$curl 		= curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL 				=> $this->UrlsCur2 . $Url . "?" . http_build_query($Query),
		CURLOPT_RETURNTRANSFER 		=> true,
		CURLOPT_MAXREDIRS 			=> 10,
		CURLOPT_TIMEOUT 			=> 30,
        CURLOPT_SSL_VERIFYHOST 		=> false,
		CURLOPT_SSL_VERIFYPEER 		=> false,
		CURLOPT_CUSTOMREQUEST		=> "DELETE",
		CURLOPT_HTTPHEADER 			=> array(
        "accept: application/json",
        "content-type: application/json",
        "Authorization: Bearer " . $this->AccessToken
        ),
        ));
		
		$response 	= curl_exec($curl);
		$httpcode	= curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$err 		= curl_error($curl);
		
		curl_close($curl);
		
		# --------------	
		
		return $this->CallRet($err, $httpcode, $response);
		
		# --------------	
		
	}
	
	# ###########################
	
}
?>