<?
# ################################
# ################################

require("Funcoes.php");
require("Config.php");
require("phpqrcode/qrlib.php");

# ################################
# ################################

$ObjCtrlPix                                 = new CtrlPix($DadReq);

# *********

$DadEnv                                     = array();
$DadEnv["calendario"]["expiracao"]          = 3600;
$DadEnv["devedor"]["cpf"]                   = utf8_encode($ObjCtrlPix->Util_LimparString("999.999.999-99"));
$DadEnv["devedor"]["nome"]                  = utf8_encode("Teste1 Teste1");

$DadEnv["valor"]["original"]                = utf8_encode(1.23);
$DadEnv["chave"]                            = utf8_encode($DadReq["Chave"]);

# --------------------------------

$RetEnv                                     = $ObjCtrlPix->CallPut("pix/v1/cobqrcode/", $DadEnv);

if( $RetEnv["IsOK"] == false )
{

    # ---------------

    echo "ERRO > " . $RetEnv["Mensagem"];
    exit();

    # ---------------

}

# ################################
# ################################

$RetFPG["QRCode_Texto"]                 = $RetEnv["Dados"]["textoImagemQRcode"];
$RetFPG["QRCode_Imagem"]		        = $ObjCtrlPix->GerarQRCode($RetEnv["Dados"]["textoImagemQRcode"]);

# ################################
# ################################
?>

<b>TXID</b>
<br>
<br>
<?=$RetEnv["Dados"]["txid"]?>

<br>
<br>
<hr />
<br>
<br>

<b>QRCODE</b>
<br>
<br>
<img src="<?=$RetFPG["QRCode_Imagem"]?>" />

<br>
<br>
<hr />
<br>
<br>

<b>ENV</b>
<br>
<br>
<?=$RetFPG["QRCode_Texto"]?>