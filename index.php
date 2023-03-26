<?php
error_reporting(1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token, Accept-Encoding");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("content-type:application/json");
require './vendor/autoload.php';
use App\Ws\Auth;
use App\Ws\General;
use App\Ws\Dashboard;
use App\Ws\Program;




if($_POST["TYPE"] === "LOGIN"){ Auth::Login(General::Security($_POST["EMAIL"]), General::Security($_POST["PASSWORD"])); }
if($_POST["TYPE"] === "REGISTER"){ Auth::Register(General::Security($_POST["EMAIL"]), General::Security($_POST["PASSWORD"])); }
if($_POST["TYPE"] === "LOGIN_CHECK"){ Auth::LoginCheck(); }
if($_POST["TYPE"] === "DETAILS"){ Dashboard::List(); }
if($_POST["TYPE"] === "BUY"){ Dashboard::Buy(General::Security($_POST["DURATION"]), General::Security($_POST["KEY"])); }
if($_POST["TYPE"] === "PRIVATE"){ Dashboard::Private(); }
if($_POST["TYPE"] === "BLOG_DETAILS"){ Dashboard::BlogDetails(General::Security($_POST["BLOG"])); }
if($_POST["TYPE"] === "BLOG_SAVE"){ Dashboard::BlogSave(General::Security($_POST["BLOG"]), General::Security($_POST["TITLE"]), $_POST["DETAILS"], General::Security($_POST["IMAGE"])); }
if($_POST["TYPE"] === "BLOG_NEW"){ Dashboard::BlogNew(General::Security($_POST["TITLE"]), $_POST["DETAILS"], General::Security($_POST["IMAGE"]) ); }
if($_POST["TYPE"] === "BLOG_DELETE"){ Dashboard::BlogDelete(General::Security($_POST["BLOG"]));}
if($_POST["TYPE"] === "BLOG_STATUS"){ Dashboard::BlogStatus(General::Security($_POST["BLOG"]), General::Security($_POST["STATUS"])); }
if($_POST["TYPE"] === "USERS_LIST"){ Dashboard::UsersList(); }
if($_POST["TYPE"] === "LOGIN_JS"){Program::Login(General::Security($_POST["PROGRAM"]));}