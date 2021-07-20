<?
# ################################
# ################################

require("Funcoes.php");
require("Config.php");

# ################################
# ################################

$ObjCtrlPix                                 = new CtrlPix($DadReq);

# *********

$DadEnv                                     = array();
$DadEnv["TXID"]                             = "XXXXXXXXXXXXXXXXX";

# --------------------------------

$RetEnv                                     = $ObjCtrlPix->CallGet("pix/v1/cob/" . $DadEnv["TXID"]);

if( $RetEnv["IsOK"] == false )
{

    # ---------------

    echo "ERRO > " . $RetEnv["Mensagem"];
    exit();

    # ---------------

}

# ################################
# ################################

var_dump($RetEnv["Dados"]);

# ################################
# ################################
?>