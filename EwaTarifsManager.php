<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EwaManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use DB;
use App\Models\Tarif;

class EwaTarifsManager extends Controller
{
  public function dgo_tarifs(Request $request){
    $all_tarifs=Tarif::select('*')->where("type","vcl")->get();
    $this->edit_tarif($request);
    return view("admins/dgo_tarif_price_edit",compact("all_tarifs"));
  }
  public function kasko_tarifs(Request $request){
    $all_tarifs=Tarif::select('*')->where("type","kasko")->get();
    $this->edit_tarif($request);
    return view("admins/each_tarif_price_edit",compact("all_tarifs"));
  }
  public function tourism_tarifs(Request $request){
    $all_tarifs=Tarif::select('*')->where("type","tourism")->get();
    $this->edit_tarif($request);
    return view("admins/edit_tourism_tariff",compact("all_tarifs"));
  }
  public function green_card(Request $request){
    $all_tarifs=Tarif::select('*')->where("type","greencard")->get();
    $this->edit_tarif($request);
    return view("admins/greencard_tarif_price_edit",compact("all_tarifs"));
  }


  private function edit_tarif(Request $request){
    $all_tarifs=Tarif::all();
    if(isset($request["edit_tarif"])){
      $edit_tarif_info=[
        "api_id"=>$request["api_id"],
        "api_name"=>$request["api_name"],
        "api_img"=>$request["api_image"],
        "description"=>$request["card_description"],
        "display"=>$request["display"],
        "tarifs"=>$request["all_tarif_information"],
        "card_recommend"=>?$request["card_recommend"],
        "mtsbu"=>?$request["mtsbu"],
        "card_direct_settlement"=>?$request["card_direct_settlement"]
      ];
      Tarif::select('*')->where("id",$request["edit_tarif"])->update($edit_tarif_info);
      return redirect("admin/general");
    }
  }      
}
