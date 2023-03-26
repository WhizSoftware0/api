<?php

namespace App\Ws;
use App\Ws\General;
use PDO;
class Dashboard
{
    public static function List(){
        $bearer = General::BearerDecode();
        if($bearer !== null){
            $pdo = General::DB();
            $Query = $pdo->prepare("SELECT * FROM users WHERE id like :id");
            $Query->bindParam(":id", $bearer->user_id, PDO::PARAM_INT);
            $Query->execute();
            if($Query->rowCount() > 0){
                $User = $Query->fetch(PDO::FETCH_ASSOC);
                $Query = $pdo->prepare("SELECT * FROM orders WHERE author like :author");
                $Query->bindParam(":author", $bearer->user_id, PDO::PARAM_INT);
                $Query->execute();
                $TotalOrders = $Query->rowCount();
                $Query = $pdo->prepare("SELECT * FROM users");
                $Query->execute();
                $TotalUsers = $Query->rowCount();
                $Query = $pdo->prepare("SELECT * FROM orders WHERE author like :author");
                $Query->bindParam(":author", $bearer->user_id, PDO::PARAM_INT);
                $Query->execute();
                $Orders = $Query->fetchAll(PDO::FETCH_ASSOC);
                $Query = $pdo->prepare("SELECT * FROM store");
                $Query->execute();
                $Store = $Query->fetchAll(PDO::FETCH_ASSOC);
                $Query = $pdo->prepare("SELECT * FROM blogs ORDER BY id DESC");
                $Query->execute();
                $Blogs = $Query->fetchAll(PDO::FETCH_ASSOC);
                $Query = $pdo->prepare("SELECT * FROM admins WHERE user like :user");
                $Query->bindParam(":user", $bearer->user_id, PDO::PARAM_INT);
                $Query->execute();
                $Admin = $Query->rowCount();
                General::Success("",array(
                    "User" => array("Email" => $User["email"], "Wallet" => $User["wallet"], "Date" => $User["date"], "Active" => $User["active"], "TotalUsers" => $TotalUsers, "TotalOrders" => $TotalOrders, "Admin" => $Admin > 0 ? "True" : "False"),
                    "Order" => $Orders,
                    "Store" => $Store,
                    "Blog" => $Blogs
                    ));
                    $pdo = null;
            }else{
                General::Error("System Error","","1");
                $pdo = null;
            }
        }else{
            General::Error("Session Expired","","");
        }
    }
    public static function BlogDetails($id){
        $bearer = General::BearerDecode();
        if($bearer !== null){
            $pdo = General::DB();
            $QUERY = $pdo->prepare("SELECT * FROM admins WHERE user like :user");
            $QUERY->bindParam(":user", $bearer->user_id, PDO::PARAM_INT);
            $QUERY->execute();
            if($QUERY->rowCount() > 0){
                $Query = $pdo->prepare("SELECT * FROM blogs WHERE id like :id");
                $Query->bindParam(":id", $id, PDO::PARAM_INT);
                $Query->execute();
                $BLOG = $Query->fetch(PDO::FETCH_ASSOC);
                General::Success("", array("id" => $BLOG["id"], "name" => $BLOG["name"], "details" => $BLOG["details"], "image" => $BLOG["image"], "status" => $BLOG["status"]));
                $pdo = null;
            }else{
                General::Error("Permission Denied","","");
                $pdo = null;
            }
        }else{
            General::Error("Session Expired","","");
        }
    }
    public static function Buy($duration, $key){
        $bearer = General::BearerDecode();
        if($bearer !== null){
            $WS = General::Security($key);
            $order_id = $order_id = "WS_".uniqid();
            $license_key = "WhizSoftware_".uniqid(md5(uniqid()));
            $date = date("d.m.Y");
            $weekly = "7";
            $monthly = "30";
            $pdo = General::DB();
            $Query = $pdo->prepare("SELECT * FROM users WHERE id like :id");
            $Query->bindParam(":id", $bearer->user_id, PDO::PARAM_INT);
            $Query->execute();
            $USER = $Query->fetch(PDO::FETCH_ASSOC);
            $Query = $pdo->prepare("SELECT * FROM store WHERE ws_key like :ws_key");
            $Query->bindParam(":ws_key", $WS, PDO::PARAM_STR);
            $Query->execute();
            $STORE = $Query->fetch(PDO::FETCH_ASSOC);
            if($USER["wallet"] >= $STORE["weekly"] || $USER["wallet"] >= $STORE["monthly"]){
                $Query = $pdo->prepare("SELECT * FROM orders WHERE product like :product AND author like :author");
                $Query->bindParam(":product", $WS, PDO::PARAM_STR);
                $Query->bindParam(":author", $bearer->user_id, PDO::PARAM_INT);
                $Query->execute();
                $ORDER = $Query->fetch(PDO::FETCH_ASSOC);
                if($Query->rowCount() < 1){
                    if($duration === "Weekly"){
                        $balance = $USER["wallet"] - $STORE["weekly"];
                        $Update = $pdo->prepare("UPDATE users SET wallet =:wallet WHERE id =:id");
                        $Update->bindParam(":wallet", $balance, PDO::PARAM_STR);
                        $Update->bindParam(":id", $bearer->user_id, PDO::PARAM_INT);
                        $Update->execute();
                        $Create = $pdo->prepare("INSERT INTO orders (name,order_id,license_key,duration,date,author,product,download) VALUES (:name, :order_id, :license_key, :duration, :date, :author, :product, :download)");
                        $Create->bindParam(":name",$STORE["name"], PDO::PARAM_STR);
                        $Create->bindParam(":order_id", $order_id, PDO::PARAM_STR);
                        $Create->bindParam(":license_key", $license_key, PDO::PARAM_STR);
                        $Create->bindParam(":duration", $weekly, PDO::PARAM_INT);
                        $Create->bindParam(":date", $date, PDO::PARAM_STR);
                        $Create->bindParam(":author", $bearer->user_id, PDO::PARAM_INT);
                        $Create->bindParam(":product", $STORE["ws_key"], PDO::PARAM_STR);
                        $Create->bindParam(":download", $STORE["download"], PDO::PARAM_STR);
                        $Create->execute();
                        $pdo = null;
                        if($Create){
                            General::Success("Purchased", "");
                        }else{
                            General::Success("Purchased", $Create);
                        }
                    }
                    if($duration === "Monthly"){
                        $balance = $USER["wallet"] - $STORE["monthly"];
                        $Update = $pdo->prepare("UPDATE users SET wallet =:wallet WHERE id =:id");
                        $Update->bindParam(":wallet", $balance, PDO::PARAM_STR);
                        $Update->bindParam(":id", $bearer->user_id, PDO::PARAM_INT);
                        $Update->execute();
                        $Create = $pdo->prepare("INSERT INTO orders (name,order_id,license_key,duration,date,author,product,download) VALUES (:name, :order_id, :license_key, :duration, :date, :author, :product, :download)");
                        $Create->bindParam(":name",$STORE["name"], PDO::PARAM_STR);
                        $Create->bindParam(":order_id", $order_id, PDO::PARAM_STR);
                        $Create->bindParam(":license_key", $license_key, PDO::PARAM_STR);
                        $Create->bindParam(":duration", $monthly, PDO::PARAM_INT);
                        $Create->bindParam(":date", $date, PDO::PARAM_STR);
                        $Create->bindParam(":author", $bearer->user_id, PDO::PARAM_INT);
                        $Create->bindParam(":product", $STORE["ws_key"], PDO::PARAM_STR);
                        $Create->bindParam(":download", $STORE["download"], PDO::PARAM_STR);
                        $Create->execute();
                        $pdo = null;
                        General::Success("Purchased", "");
                    }
                }else{
                    if($duration === "Weekly"){
                        $balance = $USER["wallet"] - $STORE["weekly"];
                        $duration2 = $ORDER["duration"] + $weekly;
                        $Update = $pdo->prepare("UPDATE users SET wallet =:wallet WHERE id =:id");
                        $Update->bindParam(":wallet", $balance, PDO::PARAM_STR);
                        $Update->bindParam(":id", $bearer->user_id, PDO::PARAM_INT);
                        $Update->execute();
                        $Update = $pdo->prepare("UPDATE orders SET duration =:duration WHERE author =:author AND product =:product");
                        $Update->bindParam(":duration", $duration2,PDO::PARAM_INT);
                        $Update->bindParam(":author", $bearer->user_id, PDO::PARAM_INT);
                        $Update->bindParam(":product", $WS, PDO::PARAM_STR);
                        $Update->execute();
                        General::Success("Purchased", "");
                    }
                    if($duration === "Monthly"){
                        $balance = $USER["wallet"] - $STORE["monthly"];
                        $duration2 = $ORDER["duration"] + $monthly;
                        $Update = $pdo->prepare("UPDATE users SET wallet =:wallet WHERE id =:id");
                        $Update->bindParam(":wallet", $balance, PDO::PARAM_STR);
                        $Update->bindParam(":id", $bearer->user_id, PDO::PARAM_INT);
                        $Update->execute();
                        $Update = $pdo->prepare("UPDATE orders SET duration =:duration WHERE author =:author AND product =:product");
                        $Update->bindParam(":duration", $duration2,PDO::PARAM_INT);
                        $Update->bindParam(":author", $bearer->user_id, PDO::PARAM_INT);
                        $Update->bindParam(":product", $WS, PDO::PARAM_STR);
                        $Update->execute();
                        General::Success("Purchased", "");
                    }
                }
            }else{
                General::Warning("Insufficient Balance","","");
            }
        }else{
            General::Error("Session Expired","","");
        }
    }
    public static function Private(){
        $bearer = General::BearerDecode();
        if($bearer !== null){
            $pdo = General::DB();
            $QUERY = $pdo->prepare("SELECT * FROM admins WHERE user like :user");
            $QUERY->bindParam(":user", $bearer->user_id, PDO::PARAM_INT);
            $QUERY->execute();
            if($QUERY->rowCount() > 0){
                General::Success("","");
                $pdo = null;
            }else{
                General::Error("","","");
                $pdo = null;
            }
        }else{
            General::Error("Session Expired","","");
        }
    }
    public static function BlogSave($id, $title, $details, $image){
        $bearer = General::BearerDecode();
        if($bearer !== null){
            $pdo = General::DB();
            $QUERY = $pdo->prepare("SELECT * FROM admins WHERE user like :user");
            $QUERY->bindParam(":user", $bearer->user_id, PDO::PARAM_INT);
            $QUERY->execute();
            if($QUERY->rowCount() > 0){
                $Update = $pdo->prepare("UPDATE blogs SET name =:name, details =:details, image =:image WHERE id =:id");
                $Update->bindParam(":name", $title, PDO::PARAM_STR);
                $Update->bindParam(":details", $details, PDO::PARAM_STR);
                $Update->bindParam(":image", $image, PDO::PARAM_STR);
                $Update->bindParam(":id", $id, PDO::PARAM_INT);
                $Update->execute();
                General::Success("Blog Updated", "");
                $pdp = null;
            }else{
                General::Error("Permission Denied","","");
                $pdo = null;
            }
        }else{
            General::Error("Session Expired","","");
        }
    }
    public static function BlogNew($title, $details, $image){
        $bearer = General::BearerDecode();
        if($bearer !== null){
            $pdo = General::DB();
            $QUERY = $pdo->prepare("SELECT * FROM admins WHERE user like :user");
            $QUERY->bindParam(":user", $bearer->user_id, PDO::PARAM_INT);
            $QUERY->execute();
            if($QUERY->rowCount() > 0){
                $st = 1;
                $Create = $pdo->prepare("INSERT INTO blogs (name,details,image,status) VALUES (:name,:details,:image,:status)");
                $Create->bindParam(":name", $title, PDO::PARAM_STR);
                $Create->bindParam(":details", $details, PDO::PARAM_STR);
                $Create->bindParam(":image", $image, PDO::PARAM_STR);
                $Create->bindParam(":status", $st, PDO::PARAM_INT);
                $Create->execute();
                General::Success("Blog Created", "");
                $pdo = null;
            }else{
                General::Error("Permission Denied","","");
                $pdo = null;
            }
        }else{
            General::Error("Session Expired","","");
        }
    }
    public static function BlogDelete($id){
        $bearer = General::BearerDecode();
        if($bearer !== null){
            $pdo = General::DB();
            $QUERY = $pdo->prepare("SELECT * FROM admins WHERE user like :user");
            $QUERY->bindParam(":user", $bearer->user_id, PDO::PARAM_INT);
            $QUERY->execute();
            if($QUERY->rowCount() > 0){
                $Delete = $pdo->prepare("DELETE FROM blogs WHERE id like :id");
                $Delete->bindParam(":id", $id, PDO::PARAM_INT);
                $Delete->execute();
                $Reset = $pdo->prepare("ALTER TABLE blogs AUTO_INCREMENT = 1");
                $Reset->execute();
                General::Success("Blog Deleted", "");
                $pdo = null;
            }else{
                General::Error("Permission Denied","","");
                $pdo = null;
            }
        }else{
            General::Error("Session Expired","","");
        }
    }
    public static function BlogStatus($id, $status){
        $bearer = General::BearerDecode();
        if($bearer !== null){
            $pdo = General::DB();
            $QUERY = $pdo->prepare("SELECT * FROM admins WHERE user like :user");
            $QUERY->bindParam(":user", $bearer->user_id, PDO::PARAM_INT);
            $QUERY->execute();
            if($QUERY->rowCount() > 0){
                if($status == "ENABLE"){
                    $st = 1;
                    $Update = $pdo->prepare("UPDATE blogs SET status =:status WHERE id like :id");
                    $Update->bindParam(":status", $st, PDO::PARAM_INT);
                    $Update->bindParam(":id", $id, PDO::PARAM_INT);
                    $Update->execute();
                    General::Success("Blog Activated", $id);
                    $pdo = null;
                }else{
                    $st = 0;
                    $Update = $pdo->prepare("UPDATE blogs SET status =:status WHERE id =:id");
                    $Update->bindParam(":status", $st2, PDO::PARAM_INT);
                    $Update->bindParam(":id", $id, PDO::PARAM_INT);
                    $Update->execute();
                    General::Success("Blog Deactivated", $status);
                    $pdo = null;
                }
            }else{
                General::Error("Permission Denied","","");
                $pdo = null;
            }
        }else{
            General::Error("Session Expired","","");
        }
    }
    public static function UsersList(){
        $bearer = General::BearerDecode();
        if($bearer !== null){
            $pdo = General::DB();
            $QUERY = $pdo->prepare("SELECT * FROM admins WHERE user like :user");
            $QUERY->bindParam(":user", $bearer->user_id, PDO::PARAM_INT);
            $QUERY->execute();
            if($QUERY->rowCount() > 0){
                $Query = $pdo->prepare("SELECT * FROM users");
                $Query->execute();
                General::Success("", $Query->fetchAll(PDO::FETCH_ASSOC));
                $pdo = null;
            }else{
                General::Error("Permission Denied","","");
                $pdo = null;
            }
        }else{
            General::Error("Session Expired","","");
        }
    }
}