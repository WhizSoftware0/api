<?php

namespace App\Ws;
use App\Ws\General;
use PDO;
class Program
{
    public static function Login($program){
        $bearer = General::Bearer();
        if($bearer !== null){
            $license = General::Security($bearer);
            $pdo = General::DB();
            $QUERY = $pdo->prepare("SELECT * FROM orders WHERE license_key like :license_key AND product like :product");
            $QUERY->bindParam(":license_key", $license, PDO::PARAM_STR);
            $QUERY->bindParam(":product", $program, PDO::PARAM_STR);
            $QUERY->execute();
            if($QUERY->rowCount() > 0){
                $ORDER = $QUERY->fetch(PDO::FETCH_ASSOC);
                if($ORDER["duration"] > 0){
                    $QUERY = $pdo->prepare("SELECT * FROM users WHERE id like :id");
                    $QUERY->bindParam(":id", $ORDER["author"], PDO::PARAM_INT);
                    $QUERY->execute();
                    if($QUERY->rowCount() > 0){
                        $USER = $QUERY->fetch(PDO::FETCH_ASSOC);
                        if($USER["active"] !== "0"){
                           $QUERY = $pdo->prepare("SELECT * FROM store WHERE ws_key like :ws_key");
                           $QUERY->bindParam(":ws_key", $program, PDO::PARAM_STR);
                           $QUERY->execute();
                           if($QUERY->rowCount() > 0){
                               $STORE = $QUERY->fetch(PDO::FETCH_ASSOC);
                               if($STORE["status"] === "1"){
                                    General::Success("License Key Verified","");
                                    $pdo = null;
                               }else{
                                   General::Error("The program is currently unavailable","","");
                               }
                           }else{
                               General::Error("Invalid Request","","");
                           }
                        }else{
                            General::Error("You Account Banned","","");
                            $pdo = null;
                        }
                    }else{
                        General::Error("Invalid Request","","");
                        $pdo = null;
                    }
                }else{
                    General::Error("You License Key Duration Expired", "","");
                    $pdo = null;
                }
            }else{
                General::Warning("Invalid License Key","","1");
                $pdo = null;
            }
        }else{
            General::Error("Invalid Request", "","");
        }
    }
}