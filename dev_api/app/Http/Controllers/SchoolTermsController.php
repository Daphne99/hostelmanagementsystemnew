<?php
namespace App\Http\Controllers;

use App\Models2\SchoolTerm;
use App\Models2\User;

class SchoolTermsController extends Controller {

	var $data = array();
	var $panelInit ;
	var $layout = 'dashboard';

	public function __construct(){
		if(app('request')->header('Authorization') != "" || \Input::has('token')){
			$this->middleware('jwt.auth');
		}else{
			$this->middleware('authApplication');
		}

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)){
			return \Redirect::to('/');
		}
	}

	public function listAll() {
		if(!$this->panelInit->can( array("schoolTerms.list","schoolTerms.add","schoolTerms.edit","schoolTerms.del") )){
			exit;
		}

		$toReturn = array();
		$terms = SchoolTerm::get();

		$toReturn['terms'] = $terms;

		return $toReturn;
	}

	public function delete($id){
		if(!$this->panelInit->can( "schoolTerms.del" )){
			exit;
		}

		if ( $postDelete = SchoolTerm::where('id', $id)->first() ) {
  		user_log('School terms', 'delete', $postDelete->className);
      $postDelete->delete();
      return $this->panelInit->apiOutput(true,'Delete','Success delete school term.');
    }else{
      return $this->panelInit->apiOutput(false,'Delete','School term not exists.');
    }
	}

	public function create(){
		if(!$this->panelInit->can( "schoolTerms.add" )){
			exit;
		}

		$term = new SchoolTerm;
		$term->title = \Input::get('title');
		$term->save();

		user_log('School terms', 'create', $term->title);

		return $this->panelInit->apiOutput(true,$this->panelInit->language['add'],'Success store school term.', $term->toArray());
	}

	public function fetch($id){
		if(!$this->panelInit->can( "schoolTerms.edit" )){
			exit;
		}
		$termDetail = SchoolTerm::where('id',$id)->first()->toArray();
		return $termDetail;
	}

	public function fetchAll()
	{
		$terms = SchoolTerm::get()->toArray();

		return json_encode([
			"jsData" => $terms,
			"jsStatus" => "1"
		]);
	}

	public function edit($id){
		if(!$this->panelInit->can( "schoolTerms.edit" )){
			exit;
		}

		$term = SchoolTerm::find($id);
		$term->title = \Input::get('title');
		$term->save();

		user_log('School terms', 'edit', $term->title);

		return $this->panelInit->apiOutput(true, 'Update', 'Success update school term.', $term->toArray());
	}
}
