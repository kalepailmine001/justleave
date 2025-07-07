#!/usr/bin/env php
<?php
// =======================================================
//           LTCViews Bot - Standalone Single File
// =======================================================

// ------------- Error Reporting -------------
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ------------- CLI Argument Handling -------------
function print_help() {
    echo "Usage:\n";
    echo "  php ltcviews_standalone.php -c <cookie> [-u <useragent>]\n";
    echo "  php ltcviews_standalone.php --cookies <cookie> [--user-agent <useragent>]\n";
    echo "  php ltcviews_standalone.php -h | --help\n";
    echo "\n";
    echo "Options:\n";
    echo "  -c, --cookies <cookie>         Use the provided cookie string for authentication\n";
    echo "  -u, --user-agent <useragent>   Use the provided user agent string (default: Mozilla/5.0 ...)\n";
    echo "  -h, --help                     Show this help message\n";
    exit(0);
}
function get_cli_args($argv) {
    $cookie = null;
    $useragent = null;
    $defaultUA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36';
    for ($i = 0; $i < count($argv); $i++) {
        $arg = $argv[$i];
        if (($arg === '-c' || $arg === '--cookies') && isset($argv[$i+1])) {
            $cookie = $argv[$i+1];
        }
        if (($arg === '-u' || $arg === '--user-agent') && isset($argv[$i+1])) {
            $useragent = $argv[$i+1];
        }
        if ($arg === '-h' || $arg === '--help') {
            print_help();
        }
    }
    if (!$cookie) print_help();
    if (!$useragent) $useragent = $defaultUA;
    return [$cookie, $useragent];
}

// ------------- Constants -------------
const BOT_VERSION = "1.0.0";
const BOT_TITLE = "ltcviews";
const HOST = "https://www.ltcviews.com/";
const REFLINK = "https://www.ltcviews.com/?ref=8851";
const YOUTUBE = "https://youtube.com/@iewil";

// ------------- Terminal Colors -------------
const n = "\n";
const d = "\033[0m";
const m = "\033[1;31m";
const h = "\033[1;32m";
const k = "\033[1;33m";
const b = "\033[1;34m";
const u = "\033[1;35m";
const c = "\033[1;36m";
const p = "\033[1;37m";

// ------------- Utility: Display Class -------------
class Display {
    static function rata($var, $value) {
        $list_var = [
            "success" => h."✓",
            "warning" => m."!",
            "debug"   => k."?",
            "info"    => b."i"
        ];
        $len = (in_array($var, array_keys($list_var)))? 8:9;
        $lenstr = ($len == 8)? $len-strlen($var)+1:$len-strlen($var);
        $open = ($len == 8)? $list_var[$var]." " :"› ";
        return $open.$var.str_repeat(" ", max(0, $lenstr)).p.":: ".$value;
    }
    static function Cetak($var, $value) {
        print self::rata($var, $value) . "\n";
    }
    static function Title($string) {
        print str_pad(strtoupper($string),45, " ", STR_PAD_BOTH)."\n";
    }
    static function Line($len = 45) {
        print d.str_repeat('─', max(0, $len))."\n";
    }
    static function Ban($title = null, $versi = null) {
        self::Line();
        if ($title !== null) {
            self::Title($title." [".$versi."]");
        }
        self::Line();
        print PHP_EOL;
    }
    static function Error($message) {
        print self::rata("warning", $message)."\n";
    }
    static function Sukses($message) {
        print self::rata("success", $message)."\n";
    }
    static function Isi($msg) {
        return m."╭[".p."Input $msg".m."]\n╰> ".h;
    }
}

// ------------- HTTP Requests Class -------------
class Requests {
    static function Curl($url, $header=0, $post=0, $data_post=0, $cookie=0, $proxy=0, $skip=0){
        while(true){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_COOKIE,TRUE);
            if($cookie){curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie);curl_setopt($ch, CURLOPT_COOKIEJAR,$cookie);}
            if($post) {curl_setopt($ch, CURLOPT_POST, true);}
            if($data_post) {curl_setopt($ch, CURLOPT_POSTFIELDS, $data_post);}
            if($header) {curl_setopt($ch, CURLOPT_HTTPHEADER, $header);}
            if($proxy){curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);curl_setopt($ch, CURLOPT_PROXY, $proxy);}
            curl_setopt($ch, CURLOPT_HEADER, true);
            $r = curl_exec($ch);
            if($skip){return;}
            $c = curl_getinfo($ch);
            if(!$c) return "Curl Error : ".curl_error($ch); else{
                $head = substr($r, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
                $body = substr($r, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if(!$body){
                    print "Check your Connection!";
                    sleep(2);
                    print "\r                         \r";
                    continue;
                }
                return array($head,$body,"status_code"=>$status_code);
            }
        }
    }
    static function get($url, $head =0){return self::curl($url,$head);}
    static function post($url, $head=0, $data_post=0){return self::curl($url,$head, 1, $data_post);}
}

// ------------- Utility: Functions Class -------------
class Functions {
    static function setConfig($nama_data){
        static $memory = [];
        if (isset($memory[$nama_data])) return $memory[$nama_data];
        print Display::Isi($nama_data);
        $data = readline();
        echo "\n";
        $memory[$nama_data] = $data;
        return $data;
    }
    static function removeConfig($nama_data){
        print Display::Sukses("Removed $nama_data (noop in single-file mode)");
    }
    static function Tmr($tmr){
        date_default_timezone_set("UTC");
        $sym = [' ─ ',' / ',' │ ',' \ ',];
        $timr = time()+$tmr;
        $a = 0;
        while(true){
            $a +=1;
            $res=$timr-time();
            if($res < 1) {break;}
            print $sym[$a % 4].p.date('H',$res).":".p.date('i',$res).":".p.date('s',$res)."\r";
            usleep(100000);
        }
        print "\r           \r";
    }
    static function view(){}
    static function Roll($str){
        for($i = 0;$i < 10; $i ++){
            print h."Number: ".p.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
            usleep(rand(100000,1000000));
            print "\r        \r";
        }
        print h."Number: ".p.$str;
    }
    static function cfDecodeEmail($encodedString){
        $k = hexdec(substr($encodedString,0,2));
        for($i=2,$email='';$i<strlen($encodedString)-1;$i+=2){
            $email.=chr(hexdec(substr($encodedString,$i,2))^$k);
        }
        return $email;
    }
    static function clean($str){return explode('.', $str)[0];}
    static function mid($string, $start, $end = null, $partIndex = 1) {
        $parts = explode($start, $string);
        if (!isset($parts[$partIndex])) return;
        $target = $parts[$partIndex];
        if ($end === null) return $target;
        return explode($end, $target)[0] ?? null;
    }
}

// ------------- Main Bot Logic -------------
class Bot{
    public $cookie, $uagent;
    public function __construct($cookieFromArg = null, $userAgentArg = null){
        Display::Ban(BOT_TITLE, BOT_VERSION);
        cookie:
        Display::Cetak("Register",REFLINK);
        Display::Line();
        if ($cookieFromArg) {
            $this->cookie = $cookieFromArg;
        } else {
            $this->cookie = Functions::setConfig("cookie");
        }
        if ($userAgentArg) {
            $this->uagent = $userAgentArg;
        } else {
            $this->uagent = Functions::setConfig("user_agent");
        }
        Display::Cetak("User Agent", $this->uagent);
        Functions::view();
        Display::Ban(BOT_TITLE, BOT_VERSION);
        $r = $this->Dashboard();
        if(!isset($r["user"]) || !$r["user"]){
            Functions::removeConfig("cookie");
            print Display::Error("Cookie Expired!\n");
            goto cookie;
        }
        Display::Cetak("User ID",$r["user"] ?? '');
        Display::Cetak("Balance",$r["balance"] ?? '');
        Display::Line();
        $this->surf_ads();
        $this->faucet();
    }
    private function headers($xml = 0){
        $h[] = "Host: ".parse_url(HOST)['host'];
        $h[] = "Upgrade-Insecure-Requests: 1";
        $h[] = "Connection: keep-alive";
        $h[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9";
        $h[] = "user-agent: ".$this->uagent;
        $h[] = "Referer: https://www.ltcviews.com/";
        $h[] = "Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7";
        $h[] = "cookie: ".$this->cookie;
        return $h;
    }
    private function Dashboard(){
        $r = Requests::get(HOST."dashboard.php",$this->headers())[1];
        $user = null;
        $bal = null;
        $userParts = explode('Your id: <strong>',$r);
        if (isset($userParts[1]) && $userParts[1] !== null && $userParts[1] !== '') {
            $userEnd = explode('</strong>',$userParts[1]);
            $user = isset($userEnd[0]) ? $userEnd[0] : null;
        }
        $balParts = explode('<h6>Acc Balance <strong>ŁTC</strong>',$r);
        if (isset($balParts[1]) && $balParts[1] !== null && $balParts[1] !== '') {
            $balH3 = explode('<h3 class="text-center">',$balParts[1]);
            if (isset($balH3[1]) && $balH3[1] !== null && $balH3[1] !== '') {
                $balEnd = explode('</h3>', $balH3[1]);
                $bal = isset($balEnd[0]) ? $balEnd[0] : null;
            }
        }
        return ["user"=>$user,"balance"=>$bal];
    }
    private function surf_ads(){
        while(true){
            $r = Requests::get(HOST."surf.php",$this->headers())[1];
            $adIdParts = explode('const adId = ', $r);
            if (isset($adIdParts[1]) && $adIdParts[1] !== null && $adIdParts[1] !== '') {
                $idSplit = explode(';', $adIdParts[1]);
                $id = isset($idSplit[0]) ? $idSplit[0] : null;
            } else {
                $id = null;
            }
            if(!$id){
                print Display::Error("Ads Finished\n");
                Display::Line();
                break;
            }
            $durationParts = explode("const duration = ",$r);
            $tmr = (isset($durationParts[1]) && $durationParts[1] !== null && $durationParts[1] !== '') ? explode(";",$durationParts[1])[0] : null;
            if($tmr){Functions::Tmr($tmr);}
            $resp = Requests::post(HOST."surf.php",$this->headers(),"ad_id=".$id);
            $r = json_decode($resp[1],1);
            if(isset($r["success"]) && $r["success"]){
                Display::Cetak("Surf Ads","");
                Display::Cetak("Success",$r["reward"] ?? '');
                $r = $this->Dashboard();
                Display::Cetak("Balance",$r["balance"] ?? '');
                Display::Line();
            }
        }
    }
    private function faucet(){
        while(true){
            $r = Requests::get(HOST."faucet.php",$this->headers())[1];
            $cooldownParts = explode('let cooldown = ', $r);
            $tmr = (isset($cooldownParts[1]) && $cooldownParts[1] !== null && $cooldownParts[1] !== '') ? explode(';', $cooldownParts[1])[0] : null;
            if($tmr){Functions::Tmr($tmr);continue;}
            $startTimerParts = explode('onclick="startTimer(', $r);
            if(!isset($startTimerParts[1]) || $startTimerParts[1] === null || $startTimerParts[1] === '') break;
            $adTimer = explode(',', $startTimerParts[1]);
            $ad_timer = isset($adTimer[0]) ? $adTimer[0] : null;
            $adIdSplit = isset($adTimer[1]) ? explode(')', $adTimer[1]) : null;
            $ad_id = (isset($adIdSplit[0]) && $adIdSplit[0] !== null && $adIdSplit[0] !== '') ? trim($adIdSplit[0]) : null;
            if($ad_timer){Functions::Tmr($ad_timer);}
            if(!$ad_id) break;
            $data = "ad_id=".$ad_id;
            $resp = Requests::post(HOST."faucet_claim.php",$this->headers(),$data);
            $r = $resp[1];
            if(preg_match("/You've earned/",$r)){
                $successParts = explode('!',$r);
                $successMsg = isset($successParts[1]) ? trim($successParts[1]) : '';
                Display::Cetak("Faucet","");
                Display::Cetak("Success",$successMsg);
                $r = $this->Dashboard();
                Display::Cetak("Balance",$r["balance"] ?? '');
                Display::Line();
            }
        }
    }
}

// ------------- Script Entry -------------
list($cookieFromArg, $userAgentArg) = get_cli_args($argv);
new Bot($cookieFromArg, $userAgentArg); 