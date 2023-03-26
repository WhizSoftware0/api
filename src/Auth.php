<?php

namespace App\Ws;
use App\Ws\General;
use PDO;
class Auth
{
    public static function Login($email, $password){
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            $pdo = General::DB();
            $QUERY = $pdo->prepare("SELECT * FROM users WHERE email like :email AND password like :password");
            $QUERY->bindParam(":email", $email, PDO::PARAM_STR);
            $QUERY->bindParam(":password", $password, PDO::PARAM_STR);
            $QUERY->execute();
            if($QUERY->rowCount() > 0){
                $USER = $QUERY->fetch(PDO::FETCH_ASSOC);
                if($USER["active"] === "1"){
                    General::Success("Login Success",General::BearerEncode($USER["id"]));
                    $pdo = null;
                }else{
                    General::Error("Account Banned","","");
                    $pdo = null;
                }
            }else{
                General::Error("Wrong Email Or Password","","");
                $pdo = null;
            }
        }else{
            General::Warning("Invalid Email","","");
            
        }
        
    }
    public static function Register($email, $password){
        $pdo = General::DB();
        $QUERY = $pdo->prepare("SELECT * FROM users WHERE email like :email");
        $QUERY->bindParam(":email", $email, PDO::PARAM_STR);
        $QUERY->execute();
        if($QUERY->rowCount() < 1){
            if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                $w = 0;
                $a = 1;
                $d = date("d.m.Y");
                $CREATE = $pdo->prepare("INSERT INTO users (email,password,wallet,active,date) VALUES (:email, :password, :wallet, :active, :date)");
                $CREATE->bindParam(":email", $email, PDO::PARAM_STR);
                $CREATE->bindParam(":password", $password, PDO::PARAM_STR);
                $CREATE->bindParam(":wallet", $w, PDO::PARAM_INT);
                $CREATE->bindParam(":active", $a, PDO::PARAM_INT);
                $CREATE->bindParam(":date", $d, PDO::PARAM_STR);
                $CREATE->execute();
                if($CREATE){
                    General::Success("Account Created", "");
                    $pdo = null;
                }else{
                    General::Error("System Error","","");
                    $pdo = null;
                }
            }else{
                General::Warning("Invalid Email Address","","");
                $pdo = null;
            }
        }else{
            General::Warning("Email Address Already in Use","","");
            $pdo = null;
        }
    }
    public static function LoginCheck(){
        $bearer = General::BearerDecode();
        if($bearer){
            General::Success("Verified Session", "");
        }
    }
}