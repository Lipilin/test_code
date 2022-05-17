<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use DB;
use App\Models\Post;
use App\Models\Page;
use App\Models\Tarif;

class EwaManager extends Controller
{
	private function set_credentials(){
		$this->user = Cache::get("user_name");
		$this->access_token=Cache::get("access_token");
		$this->version=Cache::get("ewa_api_version");
	}
	private function send_request($url)(string $url){
		$curl = curl_init();
		$send_options=array(
			CURLOPT_RETURNTRANSFER=>1,
			CURLOPT_HTTPHEADER=>array(
				"content-type: application/json",
				'x-auth-user: '.$this->user,
				'x-auth-token: '.$this->access_token,
			),
			CURLOPT_URL=>$url,
        );
        curl_setopt_array($curl,$send_options);
        $answer = curl_exec($curl); 
        curl_close($curl);
        return $answer;
	}
      public function get_ewa_api_companies(){
                   $this->set_credentials();
                   $url="https://web.ewa.ua/ewa/api/".$this->version."/tariff?onlyActive=true&salePointId=...";
                   $ewa_answer= $this->send_request($url);
                   $ewa_answer = json_decode($ewa_answer,true);
                   $insurers=array();
                   foreach ($ewa_answer as $tariff){ 
                        if(in_array($tariff["type"],array("epolicy","tourism","vcl","greencard") && isset($tariff["insurer"])){
                        	$new_insurer=[
                        		"api_id"=>$tariff["insurer"]["id"],
                        		"api_name"=>$tariff["insurer"]["name"],
                        		"api_img"=>"default.png",
                        		"description"=>"Стандартное описание крмпании,можно изменить в админке",
                        		"display"=>1,
                        		"type"=>$tariff["type"]
                        	];
                        	$insurers[$tariff["insurer"]["id"]."-".$tariff["type"]]=$new_insurer;
                        }
                    };
                };
                return $insurers;                   
      }
      public function update_ewa_companies(){
           $insurers= $this->get_ewa_api_companies();
           foreach($insurers as $company =>$insurer_info){
           	if(count(DB::select("SELECT * FROM `tarifs` WHERE `api_id`=".$insurer_info["api_id"]." AND `type`='".$insurer_info["type"]."'")) == 0){
           		Tarif::create($insurer_info);
           	}
           }
      }
      public function get_ewa_api_regions(){
            if(Cache::has("regions")){
                 return Cache::get("regions");
            }
            $regions = DB::select("SELECT region FROM `ukranianRegions`");
            $regions=json_encode($regions,JSON_UNESCAPED_UNICODE);
            Cache::put("regions",$regions);
            return $regions;
            
      }
      public function set_ewa_api_regions(Request $request){
      	    $this->set_credentials();
            DB::table('ukranianRegions')->delete();
            $url="https://web.ewa.ua/ewa/api/".$this->version."/place/full?country=UA";
            $ewa_answer= $this->send_request($url);
            $ewa_answer = json_decode($ewa_answer,true);
            foreach($ewa_answer as $region){
                 DB::table('ukranianRegions')->insert(['zone' => $region["zone"], 'region' => $region["nameFull"],"id"=>$region["id"]]);
            }

            
      }
}