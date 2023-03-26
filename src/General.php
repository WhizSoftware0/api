<?php

namespace App\Ws;


use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
class General
{
    public static function DB(){
        return new PDO("mysql:host=localhost;dbname=g2collections_whizsoftware;charset=utf8", "g2collections_whiz", "cyber123A");
    }
    public static function Success($msg, $json){
        if($json === ""){
            echo json_encode(array(
                "status" => "success",
                "message" => $msg
            ));
        }else{
            echo json_encode(array(
                "status" => "success",
                "message" => $msg,
                "json" => $json
            ));
        }
    }
    public static function Info($msg, $json){
        if($json === ""){
            echo json_encode(array(
                "status" => "info",
                "message" => $msg
            ));
        }else{
            echo json_encode(array(
                "status" => "info",
                "message" => $msg,
                "json" => $json
            ));
        }
    }
    public static function Error($msg, $json, $type){
        if($json === ""){
            echo json_encode(array(
                "status" => "error",
                "message" => $msg,
                "type" => $type
            ));
        }else{
            echo json_encode(array(
                "status" => "error",
                "message" => $msg,
                "json" => $json,
                "type" => $type
            ));
        }
    }
    public static function Warning($msg, $json, $type){
        if($json === ""){
            echo json_encode(array(
                "status" => "warning",
                "message" => $msg,
                "type" => $type
            ));
        }else{
            echo json_encode(array(
                "status" => "warning",
                "message" => $msg,
                "json" => $json,
                "type" => $type
            ));
        }
    }
    public static function Security($text){
        return trim(htmlspecialchars(addslashes(stripslashes(strip_tags($text)))));
    }
    public static function Bearer(){
        $header = getallheaders();
        if($header['Authorization'] !== null){
            return str_replace('Bearer ', '', $header['Authorization']);
        }else{
            return null;
        }
    }
    public static function BearerDecode(){
        try{
            return JWT::decode(self::Bearer(), new Key("WS_USER", "HS256"));
        }catch (\Exception $error){
            return null;
        }
    }
    public static function BearerEncode($id){
        return JWT::encode(array(
            "user_id" => $id,
            "iat" => time(),
            "exp" => time() + 60 * 60 * 24),"WS_USER", "HS256");
    }
}